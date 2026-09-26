const fs = require('fs');
const path = require('path');

// 1. Update app/Http/Controllers/AuthController.php
const authPath = path.join(__dirname, '..', 'app', 'Http', 'Controllers', 'AuthController.php');
let authContent = fs.readFileSync(authPath, 'utf8');

const targetAuthBlock = `        if ($role === 'driver' || $role === 'พนักงานขับรถ' || !empty($dynamicCar) || isset($defaultDriverCarMap[$userKeyEmail]) || isset($defaultDriverCarMap[$userKeyName]) || isset($defaultDriverCarMap[$userKeyEmpId])) {
            $redirectUrl = url('/tracking?car=' . $assignedCar);
        }`;

const replacementAuthBlock = `        if ($role === 'driver' || $role === 'พนักงานขับรถ' || !empty($dynamicCar) || isset($defaultDriverCarMap[$userKeyEmail]) || isset($defaultDriverCarMap[$userKeyName]) || isset($defaultDriverCarMap[$userKeyEmpId])) {
            // Check vehicle status from Cache / Database / Storage
            $carStatus = null;
            $tramsJson = \\Illuminate\\Support\\Facades\\Cache::get('global_storage_yru_trams_v18');
            if ($tramsJson) {
                $tramsList = json_decode($tramsJson, true);
                if (is_array($tramsList)) {
                    foreach ($tramsList as $t) {
                        if (isset($t['id']) && $t['id'] === $assignedCar) {
                            $carStatus = $t['status'] ?? null;
                            break;
                        }
                    }
                }
            }

            // Default EV-03 is suspended by default
            if (!$carStatus && $assignedCar === 'EV-03') {
                $carStatus = 'ระงับการใช้งาน';
            }

            // Check if vehicle is suspended or broken
            $isCarSuspendedOrBroken = false;
            if ($carStatus) {
                $carStatusLower = strtolower($carStatus);
                if (
                    str_contains($carStatus, 'ระงับ') || 
                    str_contains($carStatus, 'ขัดข้อง') || 
                    str_contains($carStatus, 'ปรับปรุง') ||
                    $carStatusLower === 'suspended' || 
                    $carStatusLower === 'maintenance' || 
                    $carStatusLower === 'broken'
                ) {
                    $isCarSuspendedOrBroken = true;
                }
            }

            if ($isCarSuspendedOrBroken) {
                // If car is suspended/broken and driver has not been reassigned to another available active car
                Auth::logout();
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'vehicle_suspended',
                    'message' => 'รถไฟฟ้าประจำของท่าน (' . $assignedCar . ') อยู่ในสถานะระงับการใช้งาน/รถขัดข้อง ไม่สามารถเข้าสู่ระบบเพื่อปฏิบัติงานได้ กรุณาติดต่อผู้ดูแลระบบเพื่อย้ายไปขับรถคันอื่นแทน'
                ], 403);
            }

            $redirectUrl = url('/tracking?car=' . $assignedCar);
        }`;

if (authContent.includes(targetAuthBlock)) {
    authContent = authContent.replace(targetAuthBlock, replacementAuthBlock);
    fs.writeFileSync(authPath, authContent, 'utf8');
    console.log('AuthController.php updated successfully');
} else {
    console.log('AuthController target block not found');
}

// 2. Update welcome/index.blade.php for vehicle_suspended error popup
const welcomePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'welcome', 'index.blade.php');
let welcome = fs.readFileSync(welcomePath, 'utf8');

if (!welcome.includes('vehicle_suspended')) {
    welcome = welcome.replace(
        'showLoginError(data.error_type || \'general\', data.message || \'เกิดข้อผิดพลาดในการเข้าสู่ระบบ\');',
        `if (data.error_type === 'vehicle_suspended') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'รถถูกระงับการใช้งาน / ขัดข้อง',
                            text: data.message,
                            confirmButtonText: 'รับทราบ',
                            confirmButtonColor: '#EC4899',
                            customClass: { popup: 'rounded-2xl' }
                        });
                    } else {
                        showLoginError(data.error_type || 'general', data.message || 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ');
                    }`
    );
    welcome = welcome.replace(
        'showLoginError(err.error_type || \'general\', msg);',
        `if (err.error_type === 'vehicle_suspended' || (err.message && (err.message.includes('ระงับการใช้งาน') || err.message.includes('รถขัดข้อง')))) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'รถถูกระงับการใช้งาน / ขัดข้อง',
                        text: msg,
                        confirmButtonText: 'รับทราบ',
                        confirmButtonColor: '#EC4899',
                        customClass: { popup: 'rounded-2xl' }
                    });
                } else {
                    showLoginError(err.error_type || 'general', msg);
                }`
    );
    fs.writeFileSync(welcomePath, welcome, 'utf8');
    console.log('welcome/index.blade.php updated successfully');
}

// 3. Update tracking/index.blade.php to block suspended vehicle access directly
const trackingPath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'tracking', 'index.blade.php');
let tracking = fs.readFileSync(trackingPath, 'utf8');

const lockGuardCode = `
    // Guard check: ถ้าแอดมินระงับการใช้งานหรือรถขัดข้อง ล็อคไม่ให้เข้าสู่ระบบการขับรถคันนี้
    function checkCurrentVehicleSuspensionLock() {
        try {
            var curTram = (typeof trams !== 'undefined' && Array.isArray(trams)) ? trams.find(t => t.id === currentCarCode) : null;
            if (!curTram) return;

            var rawStatus = (curTram.status || '').toString().trim();
            var rawLive = localStorage.getItem('yru_car_status_' + currentCarCode);
            if (rawLive) {
                var parsed = JSON.parse(rawLive);
                if (parsed.status) rawStatus = parsed.status.toString().trim();
            }

            var isSuspended = rawStatus.includes('ระงับ') || rawStatus.includes('ขัดข้อง') || rawStatus.includes('ปรับปรุง') || rawStatus === 'MAINTENANCE' || rawStatus === 'SUSPENDED';

            if (isSuspended) {
                Swal.fire({
                    icon: 'error',
                    title: 'รถถูกระงับการใช้งาน / ขัดข้อง',
                    text: 'รถไฟฟ้า ' + currentCarCode + ' อยู่ในสถานะระงับการใช้งานหรือรถขัดข้อง ไม่สามารถปฏิบัติงานได้ กรุณาติดต่อผู้ดูแลระบบเพื่อเปลี่ยนคันขับ',
                    confirmButtonText: 'ออกจากระบบ',
                    confirmButtonColor: '#EC4899',
                    allowOutsideClick: false,
                    customClass: { popup: 'rounded-2xl' }
                }).then(() => {
                    window.location.href = '/';
                });
            }
        } catch(e) {}
    }
`;

if (!tracking.includes('checkCurrentVehicleSuspensionLock')) {
    tracking = tracking.replace('// ===== 📌 Driver Vehicle Shift State Management =====', lockGuardCode + '\n    // ===== 📌 Driver Vehicle Shift State Management =====');
    tracking = tracking.replace('updateWorkButtonsUI();', 'updateWorkButtonsUI();\n        checkCurrentVehicleSuspensionLock();');
    fs.writeFileSync(trackingPath, tracking, 'utf8');
    console.log('tracking/index.blade.php updated successfully');
}
