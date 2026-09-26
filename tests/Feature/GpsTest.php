<?php

namespace Tests\Feature;

use App\Models\GpsDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GpsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gps.device_key' => 'test-key', 'services.gps.stale_seconds' => 120]);
    }

    private function makeUser(string $role): User
    {
        return User::forceCreate([
            'user_id' => 'T' . substr(md5($role), 0, 6),
            'username' => 'test_' . strtolower($role),
            'password' => bcrypt('secret'),
            'user_role' => $role,
        ]);
    }

    private function report(array $overrides = [], string $key = 'test-key')
    {
        return $this->postJson('/api/gps/report', array_merge([
            'device_id' => 'ESP32-A1B2C3',
            'lat' => 6.554312,
            'lng' => 101.307088,
            'speed_kmh' => 12.5,
            'satellites' => 9,
            'hdop' => 0.9,
        ], $overrides), ['X-GPS-Key' => $key]);
    }

    public function test_report_rejects_wrong_key(): void
    {
        $this->report([], 'wrong')->assertStatus(401);
        $this->assertDatabaseCount('gps_devices', 0);
    }

    public function test_report_validates_coordinates(): void
    {
        $this->report(['lat' => 200])->assertStatus(422);
    }

    public function test_report_auto_registers_device_and_updates_position(): void
    {
        $this->report()->assertOk()->assertJson(['status' => 'ok', 'vehicle_id' => null]);
        $this->report(['lat' => 6.55, 'lng' => 101.3])->assertOk();

        $this->assertDatabaseCount('gps_devices', 1);
        $device = GpsDevice::first();
        $this->assertSame('ESP32-A1B2C3', $device->device_id);
        $this->assertEqualsWithDelta(6.55, $device->latitude, 0.0000001);
        $this->assertNotNull($device->last_seen_at);
    }

    public function test_positions_only_include_assigned_devices(): void
    {
        $this->report();
        $this->report(['device_id' => 'ESP32-000002']);
        GpsDevice::where('device_id', 'ESP32-A1B2C3')->update(['vehicle_id' => 'EV-01']);

        $this->getJson('/api/gps/positions')
            ->assertOk()
            ->assertJsonCount(1, 'positions')
            ->assertJsonPath('positions.0.vehicle_id', 'EV-01')
            ->assertJsonPath('positions.0.online', true);
    }

    public function test_stale_position_is_offline(): void
    {
        $this->report();
        GpsDevice::query()->update(['vehicle_id' => 'EV-01', 'last_seen_at' => now()->subMinutes(10)]);

        $this->getJson('/api/gps/positions')->assertJsonPath('positions.0.online', false);
    }

    public function test_admin_endpoints_require_admin(): void
    {
        $this->report();

        $this->getJson('/api/gps/devices')->assertStatus(401);
        $this->actingAs($this->makeUser('Driver'))
            ->postJson('/api/gps/devices/ESP32-A1B2C3/assign', ['vehicle_id' => 'EV-01'])
            ->assertStatus(403);
    }

    public function test_one_gps_per_vehicle(): void
    {
        $this->report();
        $this->report(['device_id' => 'ESP32-000002']);
        $admin = $this->makeUser('Administrator');

        $this->actingAs($admin)
            ->postJson('/api/gps/devices/ESP32-A1B2C3/assign', ['vehicle_id' => 'EV-01'])
            ->assertOk()
            ->assertJsonPath('device.vehicle_id', 'EV-01');

        $this->actingAs($admin)
            ->postJson('/api/gps/devices/ESP32-000002/assign', ['vehicle_id' => 'EV-01'])
            ->assertStatus(409);

        // ยกเลิกการผูก แล้วอีกตัวผูกได้
        $this->actingAs($admin)
            ->postJson('/api/gps/devices/ESP32-A1B2C3/assign', ['vehicle_id' => null])
            ->assertOk();
        $this->actingAs($admin)
            ->postJson('/api/gps/devices/ESP32-000002/assign', ['vehicle_id' => 'EV-01'])
            ->assertOk();
    }

    public function test_pages_render_gps_integration(): void
    {
        $this->actingAs($this->makeUser('Administrator'))
            ->get('/admin-view')
            ->assertOk()
            ->assertSee('gpsDeviceTable', false)
            ->assertSee("navigatePage('gps', this)", false);

        $this->actingAs($this->makeUser('Student'))
            ->get('/home')
            ->assertOk()
            ->assertSee('/api/gps/positions', false);
    }

    public function test_admin_can_add_and_delete_device(): void
    {
        $admin = $this->makeUser('Administrator');

        $this->actingAs($admin)->postJson('/api/gps/devices', ['device_id' => 'GPS-01', 'name' => 'ตัวที่ 1'])->assertCreated();
        $this->actingAs($admin)->postJson('/api/gps/devices', ['device_id' => 'GPS-01'])->assertStatus(422);
        $this->actingAs($admin)->getJson('/api/gps/devices')->assertJsonCount(1, 'devices');
        $this->actingAs($admin)->deleteJson('/api/gps/devices/GPS-01')->assertOk();
        $this->assertDatabaseCount('gps_devices', 0);
    }
}
