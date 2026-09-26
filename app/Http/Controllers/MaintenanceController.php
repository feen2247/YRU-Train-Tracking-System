<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

class MaintenanceController extends Controller
{
    /**
     * Safely store data in Cache without throwing SQL column truncation exceptions
     */
    private function safeCacheForever($key, $data)
    {
        try {
            if (is_array($data)) {
                $cleaned = [];
                foreach ($data as $k => $item) {
                    if (is_array($item)) {
                        foreach ($item as $field => $val) {
                            if (in_array($field, ['signature_image', 'driver_signature', 'attachment_url', 'signed_document_url'])) {
                                continue;
                            }
                            if (is_string($val) && strlen($val) > 25000 && (str_starts_with($val, 'data:') || str_starts_with($val, 'iVBORw'))) {
                                // Keep lightweight placeholder in MySQL cache table if ultra large
                                $item[$field] = substr($val, 0, 100) . '...[TRUNCATED_FOR_CACHE]';
                            }
                        }
                    }
                    $cleaned[$k] = $item;
                }
                Cache::forever($key, $cleaned);
            } else {
                Cache::forever($key, $data);
            }
        } catch (\Throwable $e) {
            \Log::warning("Cache store exception suppressed for {$key}: " . $e->getMessage());
        }
    }

    /**
     * Auto-ensure database table exists
     */
    private function ensureTableExists()
    {
        try {
            if (!Schema::hasTable('maintenance_requests')) {
                Schema::create('maintenance_requests', function (Blueprint $table) {
                    $table->id();
                    $table->string('ticket_no', 50)->unique();
                    
                    // Step 1: Driver Request
                    $table->string('doc_date', 10)->nullable();
                    $table->string('doc_month', 30)->nullable();
                    $table->string('doc_year', 10)->nullable();
                    $table->string('driver_id', 50)->nullable();
                    $table->string('driver_name', 255)->nullable();
                    $table->string('car_id', 50)->nullable();
                    $table->string('license_plate', 50)->nullable();
                    $table->string('brand', 100)->default('YRU EV');
                    $table->string('model', 100)->default('Tram Electric');
                    $table->integer('mileage')->default(0);
                    $table->string('category', 255)->nullable();
                    $table->text('description')->nullable();
                    $table->text('issues')->nullable(); // JSON array
                    $table->string('driver_signature', 255)->nullable();
                    $table->string('urgency', 50)->default('normal');
                    
                    // Step 2: Supervisor Verification
                    $table->string('supervisor_id', 50)->nullable();
                    $table->string('supervisor_name', 255)->nullable();
                    $table->text('supervisor_notes')->nullable();
                    $table->timestamp('supervisor_verified_at')->nullable();
                    $table->string('supervisor_signature', 255)->nullable();
                    $table->longText('supervisor_signature_image')->nullable();
                    $table->text('approved_items')->nullable();
                    $table->text('rejected_items')->nullable();
                    
                    // Step 3: Quotation Submission (Mechanic/Garage)
                    $table->string('garage_to', 255)->nullable();
                    $table->string('garage_project', 500)->nullable();
                    $table->string('quotation_no', 50)->nullable();
                    $table->string('quotation_date', 30)->nullable();
                    $table->string('garage_name', 255)->nullable();
                    $table->string('garage_manager', 255)->nullable();
                    $table->string('mechanic_name', 255)->nullable();
                    $table->text('quotation_items')->nullable(); // JSON array
                    $table->decimal('subtotal', 12, 2)->default(0);
                    $table->decimal('vat', 12, 2)->default(0);
                    $table->decimal('total_cost', 12, 2)->default(0);
                    $table->string('thai_baht_text', 500)->nullable();
                    $table->decimal('parts_cost', 10, 2)->default(0);
                    $table->decimal('labor_cost', 10, 2)->default(0);
                    $table->integer('estimated_days')->default(1);
                    $table->string('quotation_doc_url', 500)->nullable();
                    
                    // Step 4: Director Approval
                    $table->string('director_opinion', 50)->nullable();
                    $table->string('budget_type', 50)->nullable();
                    $table->string('revenue_budget_source', 255)->nullable();
                    $table->string('director_name', 255)->nullable();
                    $table->timestamp('director_signed_at')->nullable();
                    $table->text('director_remarks')->nullable();
                    $table->string('director_signature', 255)->nullable();
                    $table->longText('director_signature_image')->nullable();
                    
                    // Step 5: Completion & Billing
                    $table->string('receiver_name', 255)->nullable();
                    $table->timestamp('completed_at')->nullable();
                    $table->string('archive_no', 50)->nullable();
                    $table->string('archive_date', 20)->nullable();
                    $table->string('receipt_doc_url', 500)->nullable();
                    $table->text('completion_notes')->nullable();
                    $table->text('photos')->nullable();
                    
                    $table->string('status', 50)->default('pending_supervisor');
                    $table->timestamps();
                });
            } else {
                // Add missing columns to existing table
                $newCols = [
                    'category' => ['string', 255],
                    'garage_to' => ['string', 255],
                    'garage_project' => ['string', 500],
                    'quotation_no' => ['string', 50],
                    'quotation_date' => ['string', 30],
                    'thai_baht_text' => ['string', 500],
                    'supervisor_signature' => ['string', 255],
                    'director_signature' => ['string', 255],
                    'garage_name' => ['string', 255],
                    'garage_manager' => ['string', 255],
                    'mechanic_name' => ['string', 255],
                    'quotation_doc_url' => ['string', 500],
                    'receiver_name' => ['string', 255],
                    'archive_no' => ['string', 50],
                    'archive_date' => ['string', 20],
                    'receipt_doc_url' => ['string', 500],
                    'estimated_days' => ['integer', 0],
                ];
                $newDecimals = ['subtotal', 'vat', 'total_cost', 'parts_cost', 'labor_cost'];
                $longCols = [
                    'description', 
                    'signature_image', 
                    'attachment_url', 
                    'signed_document_url', 
                    'supervisor_signature_image', 
                    'director_signature_image',
                    'issues',
                    'approved_items',
                    'rejected_items',
                    'quotation_items',
                    'completion_notes',
                    'photos'
                ];
                $newTimestamps = ['completed_at'];

                Schema::table('maintenance_requests', function (Blueprint $table) use ($newCols, $newDecimals, $longCols, $newTimestamps) {
                    foreach ($newCols as $col => $def) {
                        if (!Schema::hasColumn('maintenance_requests', $col)) {
                            if ($def[0] === 'integer') {
                                $table->integer($col)->default(0);
                            } else {
                                $table->string($col, $def[1])->nullable();
                            }
                        }
                    }
                    foreach ($newDecimals as $col) {
                        if (!Schema::hasColumn('maintenance_requests', $col)) {
                            $table->decimal($col, 12, 2)->default(0);
                        }
                    }
                    foreach ($longCols as $col) {
                        if (!Schema::hasColumn('maintenance_requests', $col)) {
                            $table->longText($col)->nullable();
                        }
                    }
                    foreach ($newTimestamps as $col) {
                        if (!Schema::hasColumn('maintenance_requests', $col)) {
                            $table->timestamp($col)->nullable();
                        }
                    }
                });
            }
        } catch (\Exception $e) {
            \Log::warning("Could not auto-create maintenance_requests table: " . $e->getMessage());
        }
    }

    /**
     * Get Thai Month Name
     */
    private function getThaiMonth($monthIndex = null)
    {
        $months = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        $m = $monthIndex ? (int)$monthIndex : (int)date('n');
        return $months[$m] ?? 'สิงหาคม';
    }

    private function seedDefaultTickets()
    {
        $defaults = [
            [
                'ticket_no' => 'MNT-2569-ED4D',
                'doc_date' => '24',
                'doc_month' => 'สิงหาคม',
                'doc_year' => '2569',
                'driver_id' => 'DRV-EV-01',
                'driver_name' => 'นายอัสมี มูเล็ง',
                'car_id' => 'EV-01',
                'license_plate' => 'EV-01 (มรย. ยะลา)',
                'brand' => 'YRU EV',
                'model' => 'Tram Electric 2026',
                'mileage' => 14250,
                'issues' => ['ระบบผ้าเบรกลื่น มีเสียงดังผิดปกติขณะชะลอรถ', 'แรงดันลมยางล้อหลังขวาลดลงผิดปกติ'],
                'driver_signature' => 'นายอัสมี มูเล็ง',
                'urgency' => 'normal',
                'status' => 'pending_supervisor'
            ],
            [
                'ticket_no' => 'MNT-2569-3010',
                'doc_date' => '24',
                'doc_month' => 'สิงหาคม',
                'doc_year' => '2569',
                'driver_id' => 'DRV-EV-03',
                'driver_name' => 'นายอัรฟาน มะเระ',
                'car_id' => 'EV-03',
                'license_plate' => 'EV-03 (มรย. ยะลา)',
                'brand' => 'YRU EV',
                'model' => 'Tram Electric 2026',
                'mileage' => 18600,
                'issues' => ['มอเตอร์ไฟฟ้าส่งเสียงดังผิดปกติขณะเร่งความเร็ว', 'ระบบไฟเลี้ยวด้านขวาไม่ติด'],
                'driver_signature' => 'นายอัรฟาน มะเระ',
                'urgency' => 'normal',
                'status' => 'pending_supervisor'
            ],
            [
                'ticket_no' => 'MNT-2569-4F63',
                'doc_date' => '24',
                'doc_month' => 'สิงหาคม',
                'doc_year' => '2569',
                'driver_id' => 'DRV-EV-04',
                'driver_name' => 'นายอุสมาน สาและ',
                'car_id' => 'EV-04',
                'license_plate' => 'EV-04 (มรย. ยะลา)',
                'brand' => 'YRU EV',
                'model' => 'Tram Electric 2026',
                'mileage' => 15420,
                'issues' => ['ผ้าเบรกมีปัญหา', 'เปลี่ยนยาง4ล้อ'],
                'driver_signature' => 'นายอุสมาน สาและ',
                'urgency' => 'normal',
                'status' => 'pending_supervisor'
            ]
        ];

        $created = [];
        $cacheMap = [];
        foreach ($defaults as $d) {
            try {
                $item = MaintenanceRequest::updateOrCreate(['ticket_no' => $d['ticket_no']], $d);
                $created[] = $item;
                $cacheMap[$d['ticket_no']] = $item->toArray();
            } catch (\Exception $e) {
                $cacheMap[$d['ticket_no']] = array_merge($d, ['id' => time() + rand(1, 999)]);
            }
        }

        $this->safeCacheForever('yru_maintenance_requests_v5', $cacheMap);
        return !empty($created) ? collect($created) : collect(array_values($cacheMap));
    }

    /**
     * List all maintenance requests
     */
    public function index(Request $request)
    {
        $this->ensureTableExists();

        $status = $request->query('status');
        $carId = $request->query('car_id');

        try {
            $query = MaintenanceRequest::query()->orderBy('id', 'desc');

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            if ($carId && $carId !== 'all') {
                $query->where('car_id', $carId);
            }

            $requests = $query->get();
            if ($requests->count() === 0) {
                $requests = $this->seedDefaultTickets();
            }

            return response()->json([
                'status' => 'success',
                'count' => $requests->count(),
                'data' => $requests
            ]);
        } catch (\Exception $e) {
            // Hybrid fallback to Cache if DB fails
            try {
                $cached = Cache::get('yru_maintenance_requests_v5', []);
            } catch (\Throwable $ex) {
                $cached = [];
            }
            if (empty($cached)) {
                $this->seedDefaultTickets();
                try {
                    $cached = Cache::get('yru_maintenance_requests_v5', []);
                } catch (\Throwable $ex) {
                    $cached = [];
                }
            }
            return response()->json([
                'status' => 'success',
                'source' => 'cache',
                'count' => count($cached),
                'data' => array_values($cached)
            ]);
        }
    }

    /**
     * Get single request detail
     */
    public function show($id)
    {
        $this->ensureTableExists();

        try {
            $req = MaintenanceRequest::where('id', $id)
                ->orWhere('ticket_no', $id)
                ->first();

            if (!$req) {
                // Check cache
                try {
                    $cached = Cache::get('yru_maintenance_requests_v5', []);
                    foreach ($cached as $item) {
                        if ((isset($item['id']) && (string)$item['id'] === (string)$id) ||
                            (isset($item['ticket_no']) && $item['ticket_no'] === $id)) {
                            return response()->json(['status' => 'success', 'data' => $item]);
                        }
                    }
                } catch (\Throwable $ex) {}
                return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูลใบแจ้งซ่อม'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $req]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 1: Driver Request Submission
     */
    public function storeDriverRequest(Request $request)
    {
        $this->ensureTableExists();

        $request->validate([
            'car_id' => 'required',
            'driver_name' => 'required',
        ]);

        $thaiYear = (int)date('Y') + 543;
        $day = $request->doc_date ?: date('j');
        $month = $request->doc_month ?: $this->getThaiMonth();
        $year = $request->doc_year ?: (string)$thaiYear;

        // Auto generate Ticket No: MNT-2569-XXXX
        $ticketNo = 'MNT-' . $thaiYear . '-' . strtoupper(substr(uniqid(), -4));

        // Format issues (up to 5 items) from any payload format (issues, symptoms, repair_items, issue_description, etc.)
        $rawIssues = $request->input('issues', $request->input('symptoms', $request->input('repair_items', $request->input('issues_list', []))));
        $formattedIssues = [];
        if (is_array($rawIssues)) {
            foreach ($rawIssues as $idx => $iss) {
                $val = trim((string)$iss);
                if (!empty($val)) {
                    $formattedIssues[] = $val;
                }
            }
        } elseif (is_string($rawIssues) && trim($rawIssues) !== '') {
            $formattedIssues = array_filter(array_map('trim', explode("\n", $rawIssues)));
        }

        if (empty($formattedIssues)) {
            $singleDesc = $request->input('issue_description', $request->input('issue', $request->input('details', $request->input('description', ''))));
            if (!empty($singleDesc)) {
                $formattedIssues = [trim($singleDesc)];
            }
        }

        if (empty($formattedIssues)) {
            $formattedIssues = ['ตรวจเช็คสภาพทั่วไป'];
        }

        $carId = strtoupper(trim($request->car_id));
        $plate = $request->input('license_plate', $request->input('plate_number', $request->input('plate', $carId . ' (มรย. ยะลา)')));

        $rawMileage = $request->input('mileage', $request->input('current_mileage', $request->input('odometer', 0)));
        $mileageVal = (int)preg_replace('/\D/', '', (string)$rawMileage);

        $driverName = $request->input('driver_name', $request->input('reporter', 'พนักงานขับรถ'));

        $sig = $request->input('signature_image', $request->input('driver_signature', $driverName));
        $attachUrl = $request->input('attachment_url', $request->input('signed_document_url', null));

        // If driver_signature is a base64 image string, store driverName in DB driver_signature column to fit VARCHAR(255)
        $dbSignature = (is_string($sig) && (str_starts_with($sig, 'data:') || strlen($sig) > 200)) ? $driverName : $sig;

        $categoryVal = $request->input('category', $request->input('issue_category', 'หมวดทั่วไป / อื่นๆ'));
        $descriptionVal = $request->input('description', $request->input('issue_description', ''));

        $data = [
            'ticket_no' => $ticketNo,
            'doc_date' => (string)$day,
            'doc_month' => (string)$month,
            'doc_year' => (string)$year,
            'driver_id' => $request->driver_id ?: ('DRV-' . $carId),
            'driver_name' => $driverName,
            'car_id' => $carId,
            'license_plate' => $plate,
            'brand' => $request->brand ?: 'YRU EV',
            'model' => $request->model ?: 'Tram Electric 2026',
            'mileage' => $mileageVal,
            'category' => $categoryVal,
            'description' => $descriptionVal,
            'issues' => array_values($formattedIssues),
            'driver_signature' => $dbSignature,
            'signature_image' => $sig,
            'attachment_url' => $attachUrl,
            'signed_document_url' => $attachUrl,
            'urgency' => $request->urgency ?: 'normal',
            'status' => 'pending_supervisor'
        ];

        try {
            $maintenanceReq = MaintenanceRequest::create($this->filterDbColumns($data));
            $recordId = $maintenanceReq->id;
        } catch (\Exception $e) {
            \Log::error("Maintenance Request DB Error: " . $e->getMessage());
            $recordId = time();
            $data['id'] = $recordId;
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        // Hybrid storage in Cache
        try {
            $all = Cache::get('yru_maintenance_requests_v5', []);
        } catch (\Throwable $ex) {
            $all = [];
        }
        $all[$ticketNo] = array_merge($data, ['id' => $recordId, 'driver_signature' => $sig]);
        $this->safeCacheForever('yru_maintenance_requests_v5', $all);

        // Note: Submitting a repair ticket does NOT automatically change vehicle status to broken/suspended.
        // Vehicle status management (พร้อมใช้งาน / รถขัดข้อง / ระงับการใช้งาน) is strictly managed by Admin.

        return response()->json([
            'status' => 'success',
            'message' => 'บันทึกใบขออนุญาตซ่อมเรียบร้อยแล้ว (รอหัวหน้ายานพาหนะตรวจสอบ)',
            'ticket_no' => $ticketNo,
            'data' => array_merge($data, ['id' => $recordId, 'driver_signature' => $sig])
        ]);
    }

    /**
     * Step 2: Supervisor Verification
     */
    public function verifySupervisor(Request $request, $id)
    {
        $this->ensureTableExists();

        $request->validate([
            'supervisor_notes' => 'required',
            'supervisor_name' => 'required'
        ]);

        $verifiedAt = now();
        $notes = trim($request->supervisor_notes);
        $supName = trim($request->supervisor_name);
        $supId = $request->supervisor_id ?: 'SUP-01';
        $approvedItems = $request->input('approved_items', []);
        $rejectedItems = $request->input('rejected_items', []);

        $targetStatus = $request->input('status');
        if (empty($targetStatus)) {
            $targetStatus = (!empty($approvedItems)) ? 'pending_quotation' : 'rejected';
        }

        $req = MaintenanceRequest::where('id', $id)->orWhere('ticket_no', $id)->first();
        if ($req) {
            $ticketNo = $req->ticket_no;
            $itemsForMechanic = !empty($approvedItems) ? $approvedItems : $req->issues;

            $updateData = [
                'supervisor_id' => $supId,
                'supervisor_name' => $supName,
                'supervisor_notes' => $notes,
                'supervisor_verified_at' => $verifiedAt,
                'supervisor_signature' => $supName,
                'supervisor_signature_image' => $request->supervisor_signature_img ?: (is_string($request->supervisor_signature) && str_starts_with($request->supervisor_signature, 'data:') ? $request->supervisor_signature : null),
                'approved_items' => $approvedItems,
                'rejected_items' => $rejectedItems,
                'issues' => $itemsForMechanic,
                'status' => $targetStatus
            ];
            $req->update($this->filterDbColumns($updateData));
            $fullData = $req->toArray();

            // If partial approval (some approved, some rejected), create a rejected record for driver
            if (!empty($rejectedItems) && !empty($approvedItems)) {
                $rejTicketNo = $ticketNo . '-REJ';
                try {
                    MaintenanceRequest::updateOrCreate(
                        ['ticket_no' => $rejTicketNo],
                        $this->filterDbColumns([
                            'ticket_no' => $rejTicketNo,
                            'doc_date' => $req->doc_date,
                            'doc_month' => $req->doc_month,
                            'doc_year' => $req->doc_year,
                            'driver_id' => $req->driver_id,
                            'driver_name' => $req->driver_name,
                            'car_id' => $req->car_id,
                            'license_plate' => $req->license_plate,
                            'brand' => $req->brand,
                            'model' => $req->model,
                            'mileage' => $req->mileage,
                            'category' => $req->category,
                            'description' => 'รายการที่ไม่ได้รับอนุญาตจากคำขอ ' . $ticketNo,
                            'issues' => $rejectedItems,
                            'rejected_items' => $rejectedItems,
                            'approved_items' => [],
                            'supervisor_id' => $supId,
                            'supervisor_name' => $supName,
                            'supervisor_notes' => $notes,
                            'supervisor_verified_at' => $verifiedAt,
                            'supervisor_signature' => $supName,
                            'supervisor_signature_image' => $updateData['supervisor_signature_image'] ?? null,
                            'driver_signature' => $req->driver_signature,
                            'signature_image' => $req->signature_image,
                            'status' => 'rejected'
                        ])
                    );
                } catch (\Throwable $ex) {
                    \Log::warning("Could not create separate rejected ticket: " . $ex->getMessage());
                }
            }
        } else {
            $ticketNo = $id;
            $fullData = [];
        }

        // Sync Cache
        try {
            $all = Cache::get('yru_maintenance_requests_v5', []);
            if (isset($all[$ticketNo])) {
                $all[$ticketNo]['supervisor_id'] = $supId;
                $all[$ticketNo]['supervisor_name'] = $supName;
                $all[$ticketNo]['supervisor_notes'] = $notes;
                $all[$ticketNo]['supervisor_verified_at'] = (string)$verifiedAt;
                $all[$ticketNo]['supervisor_signature'] = $request->supervisor_signature ?: $supName;
                $all[$ticketNo]['supervisor_signature_image'] = $request->supervisor_signature_img;
                $all[$ticketNo]['supervisor_signature_img'] = $request->supervisor_signature_img;
                $all[$ticketNo]['approved_items'] = $approvedItems;
                $all[$ticketNo]['rejected_items'] = $rejectedItems;
                if (!empty($approvedItems)) {
                    $all[$ticketNo]['issues'] = $approvedItems;
                    $all[$ticketNo]['approved_issues'] = $approvedItems;
                }
                $all[$ticketNo]['status'] = $targetStatus;
                $fullData = $all[$ticketNo];

                if (!empty($rejectedItems) && !empty($approvedItems)) {
                    $rejTicketNo = $ticketNo . '-REJ';
                    $rejItem = $all[$ticketNo];
                    $rejItem['id'] = time() + 999;
                    $rejItem['ticket_no'] = $rejTicketNo;
                    $rejItem['issues'] = $rejectedItems;
                    $rejItem['rejected_items'] = $rejectedItems;
                    $rejItem['approved_items'] = [];
                    $rejItem['status'] = 'rejected';
                    $rejItem['description'] = 'รายการที่ไม่ได้รับอนุญาตจากคำขอ ' . $ticketNo;
                    $all[$rejTicketNo] = $rejItem;
                }
                $this->safeCacheForever('yru_maintenance_requests_v5', $all);
            }
        } catch (\Throwable $ex) {}

        $message = (!empty($approvedItems))
            ? (!empty($rejectedItems) 
                ? "บันทึกการพิจารณา: อนุญาต " . count($approvedItems) . " รายการ (ส่งต่อไปยังช่าง) และ ไม่อนุญาต " . count($rejectedItems) . " รายการ (ส่งกลับไปยังคนขับ)" 
                : "บันทึกการพิจารณาเรียบร้อย: อนุญาตทั้งหมด ส่งต่อไปยังช่างซ่อม")
            : "บันทึกการพิจารณา: ไม่อนุญาตการแจ้งซ่อมทั้งหมด (ส่งข้อมูลตีกลับไปยังคนขับ)";

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $fullData
        ]);
    }

    /**
     * Step 3: Quotation Submission (Mechanic/Garage)
     */
    public function submitQuotation(Request $request, $id)
    {
        $this->ensureTableExists();

        $garageTo = trim($request->garage_to ?: 'อธิการบดี มหาวิทยาลัยราชภัฏยะลา');
        $garageProject = trim($request->garage_project ?: '');
        $quotationNo = trim($request->quotation_no ?: '');
        $quotationDate = trim($request->quotation_date ?: '');
        $garageName = trim($request->garage_name ?: 'บริษัท เช้าท์ พี.เค. อินเตอร์ กรุ๊ป จำกัด');
        $garageManager = trim($request->garage_manager ?: 'ผู้จัดการศูนย์บริการ');
        $mechanicName = trim($request->mechanic_name ?: 'ช่างผู้รับผิดชอบ');
        $items = $request->input('items', []);
        $subtotal = (float)($request->subtotal ?: 0);
        $vat = (float)($request->vat ?: 0);
        $totalCost = (float)($request->total_cost ?: 0);
        $thaiBahtText = trim($request->thai_baht_text ?: '');
        $estDays = (int)($request->estimated_days ?: 3);
        $sigImage = $request->signature_image ?: $request->mechanic_signature;

        $req = MaintenanceRequest::where('id', $id)->orWhere('ticket_no', $id)->first();
        if ($req) {
            $updateData = [
                'garage_to' => $garageTo,
                'garage_project' => $garageProject,
                'quotation_no' => $quotationNo,
                'quotation_date' => $quotationDate,
                'garage_name' => $garageName,
                'garage_manager' => $garageManager,
                'mechanic_name' => $mechanicName,
                'quotation_items' => $items,
                'subtotal' => $subtotal,
                'vat' => $vat,
                'total_cost' => $totalCost,
                'thai_baht_text' => $thaiBahtText,
                'estimated_days' => $estDays,
                'status' => 'pending_director'
            ];
            if ($sigImage) {
                $updateData['signature_image'] = $sigImage;
            }
            $req->update($updateData);
            $ticketNo = $req->ticket_no;
            $fullData = $req->toArray();
        } else {
            $ticketNo = $id;
            $fullData = [];
        }

        // Sync Cache
        try {
            $all = Cache::get('yru_maintenance_requests_v5', []);
            if (isset($all[$ticketNo])) {
                $all[$ticketNo]['garage_to'] = $garageTo;
                $all[$ticketNo]['garage_project'] = $garageProject;
                $all[$ticketNo]['quotation_no'] = $quotationNo;
                $all[$ticketNo]['quotation_date'] = $quotationDate;
                $all[$ticketNo]['garage_name'] = $garageName;
                $all[$ticketNo]['garage_manager'] = $garageManager;
                $all[$ticketNo]['mechanic_name'] = $mechanicName;
                $all[$ticketNo]['quotation_items'] = $items;
                $all[$ticketNo]['subtotal'] = $subtotal;
                $all[$ticketNo]['vat'] = $vat;
                $all[$ticketNo]['total_cost'] = $totalCost;
                $all[$ticketNo]['thai_baht_text'] = $thaiBahtText;
                $all[$ticketNo]['estimated_days'] = $estDays;
                $all[$ticketNo]['status'] = 'pending_director';
                $fullData = $all[$ticketNo];
                $this->safeCacheForever('yru_maintenance_requests_v5', $all);
            }
        } catch (\Throwable $ex) {}

        return response()->json([
            'status' => 'success',
            'message' => 'บันทึกใบเสนอราคาสำเร็จ (รอผู้บริหารอนุมัติงบประมาณ)',
            'data' => $fullData
        ]);
    }

    /**
     * Step 4: Director Approval
     */
    public function directorApprove(Request $request, $id)
    {
        $this->ensureTableExists();

        $opinion = $request->director_opinion ?: 'approved'; // approved / rejected
        $budgetType = $request->budget_type ?: 'government_budget'; // government_budget / revenue_budget
        $revenueSource = $request->revenue_budget_source ?: '';
        $directorName = $request->director_name ?: 'ผู้อำนวยการสำนักงานอธิการบดี';
        $remarks = $request->director_remarks ?: 'อนุมัติให้ดำเนินการซ่อมบำรุงตามเสนอ';
        $signedAt = now();
        $approvedItems = $request->input('approved_items', []);
        $rejectedItems = $request->input('rejected_items', []);
        $approvedTotalCost = $request->input('approved_total_cost', null);

        $newStatus = ($opinion === 'approved') ? 'in_progress' : 'rejected';

        $req = MaintenanceRequest::where('id', $id)->orWhere('ticket_no', $id)->first();
        if ($req) {
            $updateData = [
                'director_opinion' => $opinion,
                'budget_type' => $budgetType,
                'revenue_budget_source' => $revenueSource,
                'director_name' => $directorName,
                'director_signed_at' => $signedAt,
                'director_remarks' => $remarks,
                'director_signature' => $directorName,
                'director_signature_image' => $request->director_signature_img ?: (is_string($request->director_signature) && str_starts_with($request->director_signature, 'data:') ? $request->director_signature : null),
                'approved_items' => $approvedItems,
                'rejected_items' => $rejectedItems,
                'status' => $newStatus
            ];
            if ($approvedTotalCost !== null && (float)$approvedTotalCost > 0) {
                $updateData['total_cost'] = (float)$approvedTotalCost;
            }
            $req->update($this->filterDbColumns($updateData));
            $ticketNo = $req->ticket_no;
            $fullData = $req->toArray();
        } else {
            $ticketNo = $id;
            $fullData = [];
        }

        // Sync Cache
        try {
            $all = Cache::get('yru_maintenance_requests_v5', []);
            if (isset($all[$ticketNo])) {
                $all[$ticketNo]['director_opinion'] = $opinion;
                $all[$ticketNo]['budget_type'] = $budgetType;
                $all[$ticketNo]['revenue_budget_source'] = $revenueSource;
                $all[$ticketNo]['director_name'] = $directorName;
                $all[$ticketNo]['director_signed_at'] = (string)$signedAt;
                $all[$ticketNo]['director_remarks'] = $remarks;
                $all[$ticketNo]['director_signature'] = $request->director_signature ?: $directorName;
                $all[$ticketNo]['director_signature_image'] = $request->director_signature_img;
                $all[$ticketNo]['director_signature_img'] = $request->director_signature_img;
                $all[$ticketNo]['approved_items'] = $approvedItems;
                $all[$ticketNo]['rejected_items'] = $rejectedItems;
                if ($approvedTotalCost !== null && (float)$approvedTotalCost > 0) {
                    $all[$ticketNo]['total_cost'] = (float)$approvedTotalCost;
                }
                $all[$ticketNo]['status'] = $newStatus;
                $fullData = $all[$ticketNo];
                $this->safeCacheForever('yru_maintenance_requests_v5', $all);
            }
        } catch (\Throwable $ex) {}

        return response()->json([
            'status' => 'success',
            'message' => ($opinion === 'approved') 
                ? 'ผู้อำนวยการอนุมัติงบประมาณซ่อมบำรุงเรียบร้อยแล้ว (สถานะ: กำลังดำเนินการซ่อม)'
                : 'บันทึกความเห็นไม่อนุมัติเรียบร้อยแล้ว',
            'data' => $fullData
        ]);
    }

    /**
     * Step 5: Completion & Billing
     */
    public function completeRepair(Request $request, $id)
    {
        $this->ensureTableExists();

        $receiverName = $request->receiver_name ?: 'ผู้รับรถไว้ซ่อม / ช่างผู้รับผิดชอบ';
        $completedAt = now();
        $archiveNo = $request->archive_no ?: ('YRU-ARC-' . date('Ymd') . '-' . rand(100, 999));
        $archiveDate = $request->archive_date ?: date('d/m/Y');
        $receiptUrl = $request->receipt_doc_url ?: '';
        $notes = $request->completion_notes ?: 'ดำเนินการซ่อมบำรุงและทดสอบระบบเรียบร้อย รถพร้อมใช้งาน';
        $photos = $request->input('photos', []);

        $req = MaintenanceRequest::where('id', $id)->orWhere('ticket_no', $id)->first();
        $carId = 'EV-01';
        if ($req) {
            $req->update([
                'receiver_name' => $receiverName,
                'completed_at' => $completedAt,
                'archive_no' => $archiveNo,
                'archive_date' => $archiveDate,
                'receipt_doc_url' => $receiptUrl,
                'completion_notes' => $notes,
                'photos' => $photos,
                'status' => 'completed'
            ]);
            $ticketNo = $req->ticket_no;
            $carId = $req->car_id;
            $fullData = $req->toArray();
        } else {
            $ticketNo = $id;
            $fullData = [];
        }

        // Sync Cache
        try {
            $all = Cache::get('yru_maintenance_requests_v5', []);
            if (isset($all[$ticketNo])) {
                $all[$ticketNo]['receiver_name'] = $receiverName;
                $all[$ticketNo]['completed_at'] = (string)$completedAt;
                $all[$ticketNo]['archive_no'] = $archiveNo;
                $all[$ticketNo]['archive_date'] = $archiveDate;
                $all[$ticketNo]['receipt_doc_url'] = $receiptUrl;
                $all[$ticketNo]['completion_notes'] = $notes;
                $all[$ticketNo]['photos'] = $photos;
                $all[$ticketNo]['status'] = 'completed';
                $carId = $all[$ticketNo]['car_id'] ?? $carId;
                $fullData = $all[$ticketNo];
                $this->safeCacheForever('yru_maintenance_requests_v5', $all);
            }

            Cache::put('global_storage_yru_car_status_' . $carId, [
                'status' => 'พร้อมใช้งาน',
                'active_issue' => ''
            ], 86400 * 30);

            $carNum = preg_replace('/\D/', '', $carId) ?: '1';
            Cache::put('current_driver_status_' . $carNum, [
                'status' => 'normal',
                'start_time' => '08:00 น.',
                'updated_at' => date('H:i:s')
            ], 28800);

            // Sync global_storage_yru_trams_v18
            $tramsJson = Cache::get('global_storage_yru_trams_v18');
            if ($tramsJson) {
                $tramsList = is_string($tramsJson) ? json_decode($tramsJson, true) : $tramsJson;
                if (is_array($tramsList)) {
                    $updated = false;
                    foreach ($tramsList as &$t) {
                        if (isset($t['id']) && (string)$t['id'] === (string)$carId) {
                            $t['status'] = 'พร้อมใช้งาน';
                            $t['active_issue'] = '';
                            $updated = true;
                        }
                    }
                    if ($updated) {
                        Cache::forever('global_storage_yru_trams_v18', json_encode($tramsList, JSON_UNESCAPED_UNICODE));
                    }
                }
            }

            // Sync ElectricTrain DB model
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('electric_trains')) {
                    \App\Models\ElectricTrain::where('skytrain_code', $carId)
                        ->update(['status' => 'พร้อมใช้งาน', 'car_status' => 'Active']);
                }
            } catch (\Throwable $ex) {}
        } catch (\Throwable $ex) {}

        return response()->json([
            'status' => 'success',
            'message' => 'บันทึกการซ่อมเสร็จสิ้น บันทึกแฟ้มกลาง และคืนสภาพรถเป็นพร้อมใช้งานเรียบร้อยแล้ว',
            'data' => $fullData
        ]);
    }

    /**
     * Get Maintenance Workflow Statistics
     */
    public function getStatistics()
    {
        $this->ensureTableExists();

        $stats = [
            'total_tickets' => 0,
            'pending_supervisor' => 0,
            'pending_quotation' => 0,
            'pending_director' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'rejected' => 0,
            'total_approved_budget' => 0,
            'government_budget_total' => 0,
            'revenue_budget_total' => 0
        ];

        try {
            $requests = MaintenanceRequest::all();
            $stats['total_tickets'] = $requests->count();
            $stats['pending_supervisor'] = $requests->where('status', 'pending_supervisor')->count();
            $stats['pending_quotation'] = $requests->where('status', 'pending_quotation')->count();
            $stats['pending_director'] = $requests->where('status', 'pending_director')->count();
            $stats['in_progress'] = $requests->where('status', 'in_progress')->count();
            $stats['completed'] = $requests->where('status', 'completed')->count();
            $stats['rejected'] = $requests->where('status', 'rejected')->count();
            
            $approved = $requests->whereIn('status', ['in_progress', 'completed']);
            $stats['total_approved_budget'] = $approved->sum('total_cost');
            $stats['government_budget_total'] = $approved->where('budget_type', 'government_budget')->sum('total_cost');
            $stats['revenue_budget_total'] = $approved->where('budget_type', 'revenue_budget')->sum('total_cost');
        } catch (\Exception $e) {
            $cached = Cache::get('yru_maintenance_requests_v5', []);
            $stats['total_tickets'] = count($cached);
            foreach ($cached as $item) {
                $st = $item['status'] ?? 'pending_supervisor';
                if (isset($stats[$st])) $stats[$st]++;
                if (in_array($st, ['in_progress', 'completed'])) {
                    $c = (float)($item['total_cost'] ?? 0);
                    $stats['total_approved_budget'] += $c;
                    if (($item['budget_type'] ?? '') === 'government_budget') $stats['government_budget_total'] += $c;
                    if (($item['budget_type'] ?? '') === 'revenue_budget') $stats['revenue_budget_total'] += $c;
                }
            }
        }

        return response()->json(['status' => 'success', 'data' => $stats]);
    }

    /**
     * Delete a maintenance request
     */
    public function destroy($id)
    {
        $this->ensureTableExists();

        $req = MaintenanceRequest::where('id', $id)->orWhere('ticket_no', $id)->first();
        $ticketNo = $id;
        if ($req) {
            $ticketNo = $req->ticket_no;
            $req->delete();
        }

        // Remove from cache
        try {
            $all = Cache::get('yru_maintenance_requests_v5', []);
            if (isset($all[$ticketNo])) {
                unset($all[$ticketNo]);
                $this->safeCacheForever('yru_maintenance_requests_v5', $all);
            }
        } catch (\Throwable $ex) {}

        return response()->json([
            'status' => 'success',
            'message' => "ลบรายการ {$ticketNo} เรียบร้อยแล้ว"
        ]);
    }

    private function filterDbColumns($data)
    {
        try {
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing('maintenance_requests');
            if (!empty($cols)) {
                return array_intersect_key($data, array_flip($cols));
            }
        } catch (\Throwable $ex) {}
        return $data;
    }
}
