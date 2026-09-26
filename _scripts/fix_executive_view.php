<?php

$targetFile = __DIR__ . '/../resources/views/passenger/executive/index.blade.php';
$content = file_get_contents($targetFile);

$splitMarker = "</main>\n    </div>";
$parts = explode($splitMarker, $content);
if (count($parts) < 2) {
    echo "Could not find split marker\n";
    exit(1);
}

$topHtml = $parts[0] . $splitMarker;

$newBottom = <<<'EOT'

    <!-- Include Printable Official Form Modal -->
    @include('passenger.maintenance.partials.printable-form-modal')

    <!-- Document Archive Modal: ระบบดูใบเสนอราคาย้อนหลังสำหรับผู้บริหาร (Executive Quotation Viewer) -->
    <div id="viewQuotationModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center hidden p-3 z-50 transition-all duration-300 font-kanit">
        <div class="bg-white w-full max-w-[760px] max-h-[95vh] overflow-y-auto rounded-2xl shadow-2xl border border-slate-200 flex flex-col" style="font-family: 'Sarabun', 'Inter', sans-serif;">
            
            <!-- Top Action Header Bar -->
            <div class="bg-gradient-to-r from-pink-600 via-pink-700 to-rose-600 px-6 py-4 flex items-center justify-between text-white shrink-0 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center text-white text-lg">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-wide text-white">เอกสารใบเสนอราคาซ่อมบำรุงย้อนหลัง (Quotation Document)</h3>
                        <p class="text-[11px] text-pink-100">เลขที่ใบเสนอราคา: <span id="vqQuotationNoHeader" class="font-mono font-bold text-amber-200">-</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="printQuotationDocument()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer active:scale-95">
                        <i class="fas fa-print"></i> พิมพ์เอกสาร / PDF
                    </button>
                    <button type="button" onclick="closeViewQuotationModal()" class="text-white/80 hover:text-white transition p-1 cursor-pointer">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Quotation Document Paper Body -->
            <div id="vqPrintableArea" class="p-6 md:p-8 space-y-5 text-[12px] text-slate-800 bg-white">
                
                <!-- Company Header Banner -->
                <div class="border border-slate-300 p-4 rounded-xl space-y-1 bg-slate-50/70 text-center relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-20 h-20 rounded-full border-[8px] border-blue-500/10 flex items-center justify-center text-blue-500/10 text-4xl"><i class="fas fa-stamp"></i></div>
                    <h4 class="text-[15px] font-black text-slate-900">บริษัท เซ้าท์ พี.เค. อินเตอร์ กรุ๊ป จำกัด</h4>
                    <p class="text-[11px] text-slate-600">เลขที่ 268 หมู่ที่ 9 ตำบลสะเตงนอก อำเภอเมืองยะลา จังหวัดยะลา 95000</p>
                    <p class="text-[11px] text-slate-600">เลขที่ผู้เสียภาษี 0955564000058 &nbsp;&bull;&nbsp; โทร. 073-211461</p>
                </div>

                <!-- Document Meta Grid -->
                <div class="grid grid-cols-2 gap-4 border-b border-slate-200 pb-3">
                    <div class="space-y-1">
                        <div><span class="font-bold text-slate-500">เรียน (To):</span> <span id="vqGarageTo" class="font-bold text-slate-900">อธิการบดี มหาวิทยาลัยราชภัฏยะลา</span></div>
                        <div><span class="font-bold text-slate-500">โครงการ (Project):</span> <span id="vqGarageProject" class="font-medium text-slate-900">-</span></div>
                        <div><span class="font-bold text-slate-500">ใบขออนุญาตซ่อม:</span> <span id="vqTicketNo" class="font-mono font-bold text-slate-800">-</span></div>
                    </div>
                    <div class="space-y-1 text-right">
                        <div><span class="font-bold text-slate-500">เลขที่ใบเสนอราคา:</span> <span id="vqQuotationNo" class="font-mono font-bold text-blue-700 text-sm bg-blue-50 px-2 py-0.5 rounded border border-blue-200 inline-block">-</span></div>
                        <div><span class="font-bold text-slate-500">วันที่เสนอราคา (Date):</span> <span id="vqQuotationDate" class="font-bold text-slate-900">-</span></div>
                    </div>
                </div>

                <!-- Items Breakdown Table -->
                <div class="space-y-2">
                    <span class="font-bold text-slate-800 text-xs">รายการอะไหล่และค่าบริการประเมินราคา</span>
                    <table class="w-full text-left border border-slate-300 text-[11px] rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300">
                                <th class="py-2.5 px-3 w-[45%]">รายละเอียด (Description)</th>
                                <th class="py-2.5 px-1 w-[10%] text-center">จำนวน</th>
                                <th class="py-2.5 px-1 w-[12%] text-center">หน่วย</th>
                                <th class="py-2.5 px-2 w-[15%] text-right">ราคา/หน่วย</th>
                                <th class="py-2.5 px-3 w-[18%] text-right">รวมเงิน (บาท)</th>
                            </tr>
                        </thead>
                        <tbody id="vqItemsTbody" class="divide-y divide-slate-200 bg-white">
                            <!-- JS Populates saved item rows -->
                        </tbody>
                    </table>

                    <!-- Financial Totals Box -->
                    <div class="border border-slate-300 rounded-lg overflow-hidden text-[12px] bg-slate-50">
                        <div class="flex justify-between px-3.5 py-2 border-b border-slate-200">
                            <span class="font-bold text-slate-700">รวมราคา (Subtotal):</span>
                            <span class="font-bold font-mono text-slate-800" id="vqSubtotalDisplay">0.00 บาท</span>
                        </div>
                        <div class="flex justify-between px-3.5 py-2 border-b border-slate-200">
                            <span class="font-medium text-slate-600">บวก ภาษีมูลค่าเพิ่ม 7% (VAT 7%):</span>
                            <span class="font-bold font-mono text-slate-700" id="vqVatDisplay">0.00 บาท</span>
                        </div>
                        <div class="flex justify-between px-3.5 py-2.5 bg-blue-50/90">
                            <span class="font-black text-blue-900">รวมราคาทั้งสิ้น (Grand Total):</span>
                            <span class="font-black font-mono text-blue-700 text-[14px]" id="vqTotalCostDisplay">0.00 บาท</span>
                        </div>
                        <div class="px-3.5 py-1.5 bg-slate-100 text-[10px] text-slate-600 italic text-center font-bold border-t border-slate-200">
                            ตัวอักษร: ( <span id="vqThaiWordsDisplay" class="text-slate-800">-</span> )
                        </div>
                    </div>
                </div>

                <!-- Digital Corporate Seal & Saved Signature Section -->
                <div class="pt-4 border-t border-slate-200 font-kanit">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-slate-50 border border-slate-200/90 rounded-2xl">
                        <!-- e-Seal -->
                        <div class="flex items-center gap-3.5">
                            <img src="/img/south-pk-seal.png?v={{ time() }}" 
                                 alt="ตราประทับดิจิทัล บริษัท เช้าท์ พี.เค. อินเตอร์ กรุ๊ป จำกัด" 
                                 class="w-16 h-16 sm:w-18 sm:h-18 object-contain drop-shadow-xs"
                                 onerror="this.onerror=null; this.src='https://406665014.student.yru.ac.th/img/south-pk-seal.png';">
                            <div class="space-y-0.5 text-left">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-slate-800">ตราประทับดิจิทัล</span>
                                    <span class="text-[9px] px-2 py-0.5 bg-emerald-100 text-emerald-700 font-extrabold rounded border border-emerald-300">Verified</span>
                                </div>
                                <p class="text-[11px] text-slate-800 font-bold">บริษัท เซ้าท์ พี.เค. อินเตอร์ กรุ๊ป จำกัด</p>
                                <p class="text-[10px] font-mono text-slate-500 flex items-center gap-1">
                                    <i class="fas fa-shield-alt text-blue-600"></i> e-Seal Ref: <strong class="text-slate-700">#PK-SEC-2028</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Saved Digital Signature -->
                        <div class="text-center sm:text-right space-y-1 w-full sm:w-auto">
                            <span class="text-[11px] font-bold text-slate-600 block">ขอแสดงความนับถือ</span>
                            <div id="vqSignatureBox" class="min-h-[50px] flex items-center justify-center sm:justify-end py-1">
                                <!-- JS renders signature image / SVG -->
                            </div>
                            <div class="text-[11px] text-slate-800 font-bold leading-tight">( <span id="vqSignerName">-</span> )</div>
                            <div class="text-[10px] text-slate-500" id="vqSignerRole">ช่างซ่อมบำรุง / ผู้จัดทำใบเสนอราคา</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Action -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end gap-2 shrink-0 rounded-b-2xl">
                <button type="button" onclick="closeViewQuotationModal()" class="px-5 py-2 rounded-xl text-xs font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 transition cursor-pointer">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <!-- Modal แสดงรายการที่ได้รับอนุมัติจากผู้บริหาร -->
    <div id="executiveApprovalDetailModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300 font-kanit">
        <div class="bg-white p-6 md:p-7 rounded-3xl shadow-2xl w-full max-w-xl max-h-[92vh] overflow-y-auto border border-slate-100">
            <div class="flex justify-between items-center mb-4 border-b border-purple-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-black text-base shadow-md shadow-purple-500/30">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800">รายการแจ้งซ่อมที่ได้รับอนุมัติจากผู้บริหาร</h3>
                        <p class="text-xs text-slate-400 font-medium">เลขที่เอกสาร: <span id="eadTicketNo" class="font-mono font-bold text-purple-700">-</span></p>
                    </div>
                </div>
                <button onclick="closeExecutiveApprovalDetailModal()" class="text-slate-400 hover:text-slate-600 p-1 focus:outline-none transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <input type="hidden" id="eadTargetTicketId">

            <div class="space-y-4 text-xs">
                <!-- Vehicle & Budget Source Card -->
                <div class="bg-gradient-to-r from-purple-50 to-indigo-50 p-4 rounded-2xl border border-purple-100 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">ขบวนรถ / ทะเบียน:</span>
                        <span id="eadCarInfo" class="text-pink-600 font-extrabold text-sm">EV-01</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">แหล่งเงินงบประมาณ:</span>
                        <span id="eadBudgetType" class="text-purple-800 font-bold">เงินงบประมาณแผ่นดิน</span>
                    </div>
                    <div class="flex justify-between items-center pt-1.5 border-t border-purple-200/60">
                        <span class="text-slate-600 font-bold">งบประมาณรวมที่ผู้บริหารอนุมัติ:</span>
                        <span id="eadApprovedTotalCost" class="text-emerald-600 font-mono font-black text-base">0.00 บาท</span>
                    </div>
                </div>

                <!-- Approved Items List -->
                <div>
                    <h4 class="font-black text-slate-800 text-xs mb-2 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-emerald-500"></i> รายการที่ได้รับอนุมัติให้ซ่อมบำรุง
                    </h4>
                    <div id="eadApprovedItemsContainer" class="space-y-1.5 bg-emerald-50/60 p-3.5 rounded-2xl border border-emerald-100">
                        <!-- Dynamic items -->
                    </div>
                </div>

                <!-- Rejected Items List (if any) -->
                <div id="eadRejectedItemsWrapper" class="hidden">
                    <h4 class="font-black text-slate-800 text-xs mb-2 flex items-center gap-1.5 text-rose-700">
                        <i class="fas fa-times-circle text-rose-500"></i> รายการที่ไม่ไม่อนุมัติ / ตัดออก
                    </h4>
                    <div id="eadRejectedItemsContainer" class="space-y-1.5 bg-rose-50/60 p-3.5 rounded-2xl border border-rose-100">
                        <!-- Dynamic rejected items -->
                    </div>
                </div>

                <!-- Director Remarks & Signature -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-2">
                    <div class="flex justify-between items-center text-slate-700 font-bold">
                        <span>ผู้อนุมัติ (ผอ./ผู้บริหาร):</span>
                        <span id="eadDirectorName" class="text-purple-900 font-extrabold">-</span>
                    </div>
                    <div id="eadRemarksArea" class="hidden text-slate-600 text-[11px] bg-white p-2.5 rounded-xl border border-slate-200">
                        <span class="font-bold text-slate-500">ความเห็น/ข้อสั่งการ:</span>
                        <p id="eadDirectorRemarks" class="mt-0.5 whitespace-pre-line text-slate-800">-</p>
                    </div>
                    <div id="eadSignatureArea" class="hidden text-center pt-2">
                        <img id="eadSignatureImg" src="" class="max-h-16 mx-auto border-b border-slate-300 pb-1">
                        <span class="text-[10px] text-slate-400 block mt-1">(ลายมือชื่อดิจิทัลผู้อนุมัติ)</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 mt-6 pt-3.5 border-t border-slate-100">
                <button onclick="closeExecutiveApprovalDetailModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition cursor-pointer">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>

    <!-- Driver Feedback & Reviews Modal -->
    <div id="driverFeedbackModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300 font-kanit">
        <div class="bg-white p-6 md:p-7 rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-100">
            <!-- Modal Header -->
            <div class="flex justify-between items-start border-b border-gray-100 pb-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-pink-500 to-rose-400 text-white flex items-center justify-center font-black text-xl shadow-md shadow-pink-500/20">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800" id="dfm-driver-name">นายอัสมี มูเล็ง</h3>
                        <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500">
                            <span class="font-bold text-pink-600 font-mono" id="dfm-car-id">EV-01</span>
                            <span>&bull;</span>
                            <span id="dfm-plate">กค 1234 ยะลา</span>
                            <span>&bull;</span>
                            <span class="text-slate-400" id="dfm-emp-id">รหัส USR003</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeDriverFeedbackModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-xl hover:bg-gray-100 transition cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <!-- Overall Rating Hero Box -->
            <div class="bg-amber-50/60 border border-amber-200/80 rounded-2xl p-4 mb-5 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider block mb-0.5">คะแนนประเมินรวม</span>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-black text-slate-800" id="dfm-avg-score">-</span>
                        <span class="text-xs text-slate-500 font-medium" id="dfm-total-reviews">(จาก 0 ครั้ง)</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 bg-amber-400 text-amber-950 font-black rounded-full text-xs shadow-xs inline-block" id="dfm-rank-badge">-</span>
                </div>
            </div>

            <!-- 5 Evaluation Dimensions Breakdown -->
            <div class="space-y-2.5 mb-6 text-xs">
                <span class="font-bold text-slate-700 block text-xs">คะแนนเฉลี่ยรายด้าน (5 มิติ):</span>
                
                <div>
                    <div class="flex justify-between text-slate-600 font-medium mb-1">
                        <span>1. ความตรงต่อเวลาในการออกรถ</span>
                        <span class="text-slate-800 font-mono font-bold" id="dfm-dim-1-val">-</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="dfm-dim-1-bar" class="bg-pink-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-slate-600 font-medium mb-1">
                        <span>2. ความปลอดภัยและการขับขี่</span>
                        <span class="text-slate-800 font-mono font-bold" id="dfm-dim-2-val">-</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="dfm-dim-2-bar" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-slate-600 font-medium mb-1">
                        <span>3. มารยาทและอัธยาศัยไมตรี</span>
                        <span class="text-slate-800 font-mono font-bold" id="dfm-dim-3-val">-</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="dfm-dim-3-bar" class="bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-slate-600 font-medium mb-1">
                        <span>4. ความสะอาดและความเรียบร้อย</span>
                        <span class="text-slate-800 font-mono font-bold" id="dfm-dim-4-val">-</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="dfm-dim-4-bar" class="bg-amber-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-slate-600 font-medium mb-1">
                        <span>5. ความพึงพอใจในภาพรวม</span>
                        <span class="text-slate-800 font-mono font-bold" id="dfm-dim-5-val">-</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="dfm-dim-5-bar" class="bg-purple-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Passenger Comments List -->
            <div>
                <span class="font-bold text-slate-700 block text-xs mb-2.5 flex items-center gap-1.5">
                    <i class="fas fa-comment-dots text-pink-500"></i> ข้อเสนอแนะและความคิดเห็นจากผู้โดยสารจริง
                </span>
                <div id="dfm-comments-list" class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    <!-- Dynamic Comments Content -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-between items-center gap-2.5 mt-6 pt-4 border-t border-gray-100">
                <button type="button" onclick="printDriverEvaluationCard()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-print"></i> พิมพ์การ์ดประเมิน
                </button>
                <button type="button" onclick="closeDriverFeedbackModal()" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition cursor-pointer">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <!-- Approval Action Modal -->
    <div id="approvalActionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300 font-kanit">
        <div class="bg-white p-6 md:p-7 rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-100">
            <div class="flex justify-between items-start border-b border-gray-100 pb-3 mb-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800" id="approvalModalTitle">พิจารณาอนุมัติการซ่อมบำรุง</h3>
                    <p class="text-xs text-slate-400">เลขที่คำขอ: <span id="modalTicketCode" class="font-mono font-bold text-pink-600">-</span></p>
                </div>
                <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <input type="hidden" id="modalTicketId" value="">
            <input type="hidden" id="modalActionType" value="approve">

            <div class="space-y-3 text-xs mb-5">
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 space-y-2">
                    <div class="flex justify-between"><span class="text-slate-500 font-bold">ขบวนรถ:</span> <span id="modalTramInfo" class="font-bold text-slate-800">-</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 font-bold">อาการชำรุด:</span> <span id="modalIssueText" class="font-medium text-slate-800 text-right">-</span></div>
                    <div class="flex justify-between"><span class="text-slate-500 font-bold">ศูนย์บริการ/อู่:</span> <span id="modalGarageInfo" class="font-medium text-slate-800 text-right">-</span></div>
                    <div class="flex justify-between pt-2 border-t border-slate-200"><span class="text-slate-700 font-black text-sm">ยอดเงินประเมิน:</span> <span id="modalQuotationTotal" class="font-mono font-black text-pink-600 text-base">-</span></div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">ความเห็น / ข้อสั่งการเพิ่มเติม:</label>
                    <textarea id="modalApproverRemarks" rows="3" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-pink-500 resize-none" placeholder="ระบุข้อสั่งการหรือหมายเหตุ (ถ้ามี)...">อนุมัติการซ่อมบำรุงตามที่เสนอ</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" onclick="closeApprovalModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="button" onclick="submitExecutiveApprovalAction()" class="px-5 py-2 bg-pink-600 hover:bg-pink-700 text-white font-bold rounded-xl text-xs transition cursor-pointer shadow-md shadow-pink-600/20">
                    <i class="fas fa-check-circle mr-1"></i> ยืนยันผลการพิจารณา
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- DRIVER RATINGS & PASSENGER FEEDBACK REAL-TIME ENGINE     -->
    <!-- (ข้อเสนอแนะและความคิดเห็นจากผู้โดยสารจริง 100%)       -->
    <!-- ======================================================== -->
    <script>
        const carPlates = {
            "EV-01": "กค 1234 ยะลา", "EV-02": "กค 5678 ยะลา", "EV-03": "กข 9911 ยะลา",
            "EV-04": "กค 3456 ยะลา", "EV-05": "กค 7890 ยะลา", "EV-06": "กค 1122 ยะลา",
            "EV-07": "กค 3344 ยะลา", "EV-08": "กค 5566 ยะลา", "EV-09": "กค 7788 ยะลา",
            "EV-10": "กค 9900 ยะลา"
        };

        const baseDriverProfiles = [
            { id: "USR003", car_id: "EV-01", name: "นายอัสมี มูเล็ง", plate: "กค 1234 ยะลา", avatarBg: "bg-pink-600" },
            { id: "USR004", car_id: "EV-02", name: "นายอัรฟาน มะเระ", plate: "กค 5678 ยะลา", avatarBg: "bg-purple-600" },
            { id: "USR005", car_id: "EV-03", name: "นายซูเฟียน มะโอะ", plate: "กข 9911 ยะลา", avatarBg: "bg-blue-600" },
            { id: "USR006", car_id: "EV-04", name: "นายอุสมาน สาและ", plate: "กค 3456 ยะลา", avatarBg: "bg-emerald-600" },
            { id: "USR007", car_id: "EV-05", name: "นายบัดรี สาและ", plate: "กค 7890 ยะลา", avatarBg: "bg-amber-600" },
            { id: "USR008", car_id: "EV-06", name: "นายตอรริก ลือแมะ", plate: "กค 1122 ยะลา", avatarBg: "bg-cyan-600" },
            { id: "USR009", car_id: "EV-07", name: "นายสมหวัง ใจดี", plate: "กค 3344 ยะลา", avatarBg: "bg-rose-600" },
            { id: "USR010", car_id: "EV-08", name: "นายประเสริฐ ดีเลิศ", plate: "กค 5566 ยะลา", avatarBg: "bg-indigo-600" },
            { id: "USR011", car_id: "EV-09", name: "นายกิตติ ตั้งใจ", plate: "กค 7788 ยะลา", avatarBg: "bg-teal-600" },
            { id: "USR012", car_id: "EV-10", name: "นายรุสลัน สอเฮาะ", plate: "กค 9900 ยะลา", avatarBg: "bg-violet-600" }
        ];

        let currentFilteredDrivers = [];
        let currentAllPassengerReviews = [];
        let activeRatingSubTab = 'leaderboard';

        // รายการคีย์เวิร์ดความคิดเห็นทดสอบเก่าที่ต้องคัดกรองออกอย่างหมดจด
        const legacyMockKeywords = [
            "ระมัดระวังคนข้ามถนนดีมาก", "ยิ้มแย้มแจ่มใส ทักทายผู้โดยสาร", "รถสะอาดเอี่ยม ขับนิ่งมาก",
            "ประสานงานกับสถานีดีเยี่ยม", "ไม่มีการกระตุกเลย", "มีจิตบริการสูงมาก", "เข้าจอดเทียบชานชาลาตรงจุด",
            "ลมโกรกสบาย", "ช่วยเหลือนักศึกษาขนของขึ้นรถ", "ให้ทางคนข้ามถนนเสมอ", "ไม่กระชาก",
            "รถสะอาดเรียบร้อย ขับนิ่ม นั่งสบาย", "ไม่ต้องรอนาน", "ช่วยพยุงตอนขึ้นรถ", "เขตจำกัดความเร็วเคร่งครัด",
            "มีน้ำใจบริการ", "บรรยากาศดีครับ", "อยากให้เพิ่มรอบช่วงเย็นเลิกเรียนครับ", "เป็นกันเอง",
            "ขับรถนุ่ม ไม่เร็ว ปลอดภัยดีมากค่ะ", "มีไมตรีจิต", "เข้าเทียบชานชาลาเป๊ะ", "พนักงานน่ารักมาก",
            "ลมเย็นดีค่ะ", "ช่วยแนะนำเส้นทางจุดจอดในมหาลัยดีมากครับ", "ผู้โดยสารให้คะแนนการบริการระดับดีเยี่ยม",
            "คนขับพูดจาสุภาพมากครับ รถขับนิ่มปลอดภัยดีมาก", "รถวิ่งช้าไปนิดนึง แต่อย่างอื่นดีหมดเลยค่ะ",
            "มารับตรงเวลา ดีมากครับ", "สุดยอดการให้บริการครับ ประทับใจมาก"
        ];

        const legacyMockEmails = [
            'fatimah@gmail.com', 'nuriyah@outlook.com', 'abdul@gmail.com'
        ];

        function getLiveDriverEvaluationData() {
            let rawSurveys = [];
            try {
                const raw = localStorage.getItem('yru_surveys');
                if (raw) rawSurveys = JSON.parse(raw);
            } catch(e) {}

            if (!Array.isArray(rawSurveys)) {
                rawSurveys = [];
            }

            // คัดกรองข้อมูลความคิดเห็นปลอม/สร้างขึ้นอัตโนมัติออกให้หมดจด เหลือเฉพาะที่ผู้ใช้งานจริงเขียนเอง
            const genuineSurveys = rawSurveys.filter(s => {
                if (!s || typeof s !== 'object') return false;
                const email = (s.userEmail || '').trim().toLowerCase();
                if (legacyMockEmails.includes(email)) return false;

                const commentText = (s.comment || '').trim();
                for (let kw of legacyMockKeywords) {
                    if (commentText.includes(kw)) return false;
                }
                return true;
            });

            // อัปเดต localStorage ให้สะอาดเสมอ ปราศจากข้อมูลทดสอบเก่า
            if (genuineSurveys.length !== rawSurveys.length) {
                try {
                    localStorage.setItem('yru_surveys', JSON.stringify(genuineSurveys));
                } catch(e) {}
            }

            const surveys = genuineSurveys;

            // แมปข้อมูลการประเมินให้เข้ากับโปรไฟล์คนขับรถปัจจุบัน
            surveys.forEach(s => {
                const matchedProfile = baseDriverProfiles.find(p => 
                    (s.driverId && (s.driverId === p.id || s.driverId === p.id.replace('USR', 'USR-00000') || s.driverId === p.car_id)) ||
                    (s.carId && s.carId === p.car_id) ||
                    (s.driverName && p.name && (s.driverName.includes(p.name.split(' ')[0]) || p.name.includes(s.driverName.split(' ')[0])))
                );

                if (matchedProfile) {
                    s.driverId = matchedProfile.id;
                    s.driverName = matchedProfile.name;
                    s.carId = matchedProfile.car_id;
                    s.plate = matchedProfile.plate;
                } else if (!s.carId) {
                    s.carId = "EV-01";
                    s.plate = "กค 1234 ยะลา";
                }

                if (!s.ratings) {
                    const score = parseFloat(s.avg || 5.0);
                    s.ratings = { q1: score, q2: score, q3: score, q4: score, q5: score };
                }
                if (!s.avg) {
                    s.avg = parseFloat(((s.ratings.q1 + s.ratings.q2 + s.ratings.q3 + s.ratings.q4 + s.ratings.q5) / 5).toFixed(2));
                }
            });

            // คำนวณผลการประเมินของคนขับแต่ละท่านจากข้อมูลจริงที่ผู้โดยสารส่งมาเท่านั้น
            const drivers = baseDriverProfiles.map(p => {
                const driverSurveys = surveys.filter(s => 
                    s.driverId === p.id || 
                    s.carId === p.car_id || 
                    (s.driverName && p.name && s.driverName.includes(p.name.split(' ')[0]))
                );
                const count = driverSurveys.length;
                let q1 = null, q2 = null, q3 = null, q4 = null, q5 = null, avg = null;
                let comments = [];

                if (count > 0) {
                    const sumQ1 = driverSurveys.reduce((acc, s) => acc + (s.ratings?.q1 || s.avg || 0), 0);
                    const sumQ2 = driverSurveys.reduce((acc, s) => acc + (s.ratings?.q2 || s.avg || 0), 0);
                    const sumQ3 = driverSurveys.reduce((acc, s) => acc + (s.ratings?.q3 || s.avg || 0), 0);
                    const sumQ4 = driverSurveys.reduce((acc, s) => acc + (s.ratings?.q4 || s.avg || 0), 0);
                    const sumQ5 = driverSurveys.reduce((acc, s) => acc + (s.ratings?.q5 || s.avg || 0), 0);

                    q1 = parseFloat((sumQ1 / count).toFixed(2));
                    q2 = parseFloat((sumQ2 / count).toFixed(2));
                    q3 = parseFloat((sumQ3 / count).toFixed(2));
                    q4 = parseFloat((sumQ4 / count).toFixed(2));
                    q5 = parseFloat((sumQ5 / count).toFixed(2));
                    avg = parseFloat(((q1 + q2 + q3 + q4 + q5) / 5).toFixed(2));
                    // เอาเฉพาะข้อความที่ผู้โดยสารพิมพ์เอง ไม่เอาสตริงว่าง
                    comments = driverSurveys.filter(s => s.comment && s.comment.trim() !== '').map(s => s.comment.trim());
                }

                return {
                    ...p,
                    baseReviews: count,
                    baselineAvg: avg,
                    q1, q2, q3, q4, q5,
                    comments
                };
            });

            // จัดอันดับ: คนขับที่มีการประเมินจริงจะจัดตามคะแนนเฉลี่ยมากไปน้อย คนที่ยังไม่มีการประเมินจะอยู่ท้ายสุด
            drivers.sort((a, b) => {
                if (a.baselineAvg === null && b.baselineAvg === null) return 0;
                if (a.baselineAvg === null) return 1;
                if (b.baselineAvg === null) return -1;
                if (b.baselineAvg !== a.baselineAvg) {
                    return b.baselineAvg - a.baselineAvg;
                }
                return b.baseReviews - a.baseReviews;
            });

            // รายการรีวิวทั้งหมดที่ผู้โดยสารส่งเข้ามาจริง
            const reviewsList = surveys.map(s => ({
                id: s.id || '',
                date: s.time || s.date || "ไม่ระบุเวลา",
                driverId: s.driverId,
                driverName: s.driverName,
                carId: s.carId,
                plate: s.plate,
                score: parseFloat(s.avg || 5.0).toFixed(2),
                comment: s.comment ? s.comment.trim() : "(ไม่ได้ระบุข้อความเพิ่มเติม)",
                hasComment: !!(s.comment && s.comment.trim() !== ''),
                user: s.userEmail || "ผู้โดยสาร / นักศึกษา มรย."
            })).reverse();

            // คำนวณภาพรวมของทั้งกองรถ (Fleet Analytics) จากข้อมูลจริง
            const totalSurveysCount = surveys.length;
            const sumAllAvg = surveys.reduce((acc, s) => acc + (s.avg || 0), 0);
            const fleetAvg = totalSurveysCount > 0 ? (sumAllAvg / totalSurveysCount).toFixed(2) : "-";

            const dimSum = surveys.reduce((acc, s) => {
                acc.q1 += (s.ratings?.q1 || s.avg || 0);
                acc.q2 += (s.ratings?.q2 || s.avg || 0);
                acc.q3 += (s.ratings?.q3 || s.avg || 0);
                acc.q4 += (s.ratings?.q4 || s.avg || 0);
                acc.q5 += (s.ratings?.q5 || s.avg || 0);
                return acc;
            }, { q1: 0, q2: 0, q3: 0, q4: 0, q5: 0 });

            const dimAverages = {
                q1: totalSurveysCount > 0 ? (dimSum.q1 / totalSurveysCount).toFixed(2) : "-",
                q2: totalSurveysCount > 0 ? (dimSum.q2 / totalSurveysCount).toFixed(2) : "-",
                q3: totalSurveysCount > 0 ? (dimSum.q3 / totalSurveysCount).toFixed(2) : "-",
                q4: totalSurveysCount > 0 ? (dimSum.q4 / totalSurveysCount).toFixed(2) : "-",
                q5: totalSurveysCount > 0 ? (dimSum.q5 / totalSurveysCount).toFixed(2) : "-"
            };

            const satisfactionCount = surveys.filter(s => (s.avg || 0) >= 4.0).length;
            const satisfactionPct = totalSurveysCount > 0 ? ((satisfactionCount / totalSurveysCount) * 100).toFixed(1) : "0.0";
            const topDriver = drivers.find(d => d.baseReviews > 0) || drivers[0];

            return {
                drivers,
                reviewsList,
                totalSurveysCount,
                fleetAvg,
                dimAverages,
                satisfactionPct,
                topDriver
            };
        }

        function getStarVisualHtml(score) {
            if (score === null || isNaN(score)) return '<span class="text-xs text-slate-400 italic">ยังไม่มีคะแนน</span>';
            const num = parseFloat(score);
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (num >= i) {
                    stars += '<i class="fas fa-star text-amber-400 text-xs"></i>';
                } else if (num >= i - 0.5) {
                    stars += '<i class="fas fa-star-half-alt text-amber-400 text-xs"></i>';
                } else {
                    stars += '<i class="far fa-star text-slate-200 text-xs"></i>';
                }
            }
            return `<div class="flex items-center gap-0.5 justify-center">${stars}</div>`;
        }

        function renderDriverRatingsPage() {
            const data = getLiveDriverEvaluationData();
            currentFilteredDrivers = data.drivers;
            currentAllPassengerReviews = data.reviewsList;

            // 1. Update KPI Metrics from Real Survey Data Only
            const elFleetAvg = document.getElementById('rating-kpi-fleet-avg');
            if (elFleetAvg) elFleetAvg.innerText = data.fleetAvg !== '-' ? `${data.fleetAvg}` : '-';

            const elTotalSurveys = document.getElementById('rating-kpi-total-surveys');
            if (elTotalSurveys) elTotalSurveys.innerText = `${data.totalSurveysCount} ครั้ง`;

            const elLiveCountBadge = document.getElementById('ratingsLiveCountBadge');
            if (elLiveCountBadge) elLiveCountBadge.innerText = `${data.totalSurveysCount} แบบประเมิน`;

            const elSubtabBadge = document.getElementById('subtab-reviews-badge');
            if (elSubtabBadge) elSubtabBadge.innerText = `${data.totalSurveysCount} รีวิว`;

            // Top Performer Driver Card
            const top = data.topDriver;
            const elTopName = document.getElementById('rating-kpi-top-driver');
            const elTopCar = document.getElementById('rating-kpi-top-car');
            const elTopStats = document.getElementById('rating-kpi-top-stats');

            if (top && top.baseReviews > 0) {
                if (elTopName) elTopName.innerText = top.name;
                if (elTopCar) elTopCar.innerText = top.car_id;
                if (elTopStats) elTopStats.innerText = `${top.baselineAvg}★ (${top.baseReviews} รีวิว)`;
            } else {
                if (elTopName) elTopName.innerText = "ยังไม่มีข้อมูล";
                if (elTopCar) elTopCar.innerText = "-";
                if (elTopStats) elTopStats.innerText = "รอผลประเมินจากผู้โดยสาร";
            }

            const elSatisfy = document.getElementById('rating-kpi-satisfaction-rate') || document.getElementById('rating-kpi-satisfaction-pct');
            if (elSatisfy) elSatisfy.innerText = data.totalSurveysCount > 0 ? `${data.satisfactionPct}%` : '-';

            // Top 5 Dimensions Summary Banner
            const dim1 = document.getElementById('dim-banner-1') || document.getElementById('rating-dim-1-val');
            const dim2 = document.getElementById('dim-banner-2') || document.getElementById('rating-dim-2-val');
            const dim3 = document.getElementById('dim-banner-3') || document.getElementById('rating-dim-3-val');
            const dim4 = document.getElementById('dim-banner-4') || document.getElementById('rating-dim-4-val');
            const dim5 = document.getElementById('dim-banner-5') || document.getElementById('rating-dim-5-val');

            if (dim1) dim1.innerText = data.dimAverages.q1 !== '-' ? `${data.dimAverages.q1}★` : '-';
            if (dim2) dim2.innerText = data.dimAverages.q2 !== '-' ? `${data.dimAverages.q2}★` : '-';
            if (dim3) dim3.innerText = data.dimAverages.q3 !== '-' ? `${data.dimAverages.q3}★` : '-';
            if (dim4) dim4.innerText = data.dimAverages.q4 !== '-' ? `${data.dimAverages.q4}★` : '-';
            if (dim5) dim5.innerText = data.dimAverages.q5 !== '-' ? `${data.dimAverages.q5}★` : '-';

            // Overview Tab Highlight Cards
            const elDashFleetAvg = document.getElementById('dash-highlight-fleet-avg');
            if (elDashFleetAvg) elDashFleetAvg.innerText = data.fleetAvg !== '-' ? `${data.fleetAvg} ★` : '-';

            const elDashTotalReviews = document.getElementById('dash-highlight-total-reviews');
            if (elDashTotalReviews) elDashTotalReviews.innerText = `/ 5.00 (${data.totalSurveysCount} ครั้ง)`;

            const elDashTopDriver = document.getElementById('dash-highlight-top-driver');
            if (elDashTopDriver) elDashTopDriver.innerText = (top && top.baseReviews > 0) ? top.name : 'รอผลประเมิน';

            const elDashTopStats = document.getElementById('dash-highlight-top-stats');
            if (elDashTopStats) elDashTopStats.innerText = (top && top.baseReviews > 0) ? `(${top.car_id}, ${top.baselineAvg} ★)` : '';

            // 2. Render Leaderboard & Reviews Tables
            renderDriverRatingsTable();
            renderPassengerReviewsTable();
        }

        function renderDriverRatingsTable() {
            const tbody = document.getElementById('driverRatingsTableBody');
            if (!tbody) return;

            if (!currentFilteredDrivers || currentFilteredDrivers.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-400 font-medium">ไม่พบข้อมูลพนักงานขับรถตรงกับเงื่อนไขการค้นหา</td></tr>`;
                return;
            }

            let html = '';
            currentFilteredDrivers.forEach((d, index) => {
                let rankBadge = '';
                let scoreHtml = '';
                let dimensionsHtml = '';
                let reviewsHtml = '';
                let actionBtnHtml = '';

                if (d.baseReviews > 0) {
                    if (index === 0) {
                        rankBadge = `<span class="w-7 h-7 rounded-full bg-amber-400 text-amber-950 font-black text-xs flex items-center justify-center shadow-xs mx-auto"><i class="fas fa-crown text-[11px]"></i></span>`;
                    } else if (index === 1) {
                        rankBadge = `<span class="w-7 h-7 rounded-full bg-slate-200 text-slate-800 font-black text-xs flex items-center justify-center shadow-xs mx-auto">2</span>`;
                    } else if (index === 2) {
                        rankBadge = `<span class="w-7 h-7 rounded-full bg-amber-700 text-amber-100 font-black text-xs flex items-center justify-center shadow-xs mx-auto">3</span>`;
                    } else {
                        rankBadge = `<span class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center mx-auto">${index + 1}</span>`;
                    }

                    scoreHtml = `
                        <div class="flex flex-col items-center">
                            <div class="flex items-center gap-1.5">
                                <span class="text-base font-black text-slate-800">${parseFloat(d.baselineAvg).toFixed(2)}</span>
                                <span class="text-xs text-amber-500 font-bold">★</span>
                            </div>
                            ${getStarVisualHtml(d.baselineAvg)}
                        </div>
                    `;

                    dimensionsHtml = `
                        <div class="grid grid-cols-5 gap-1 text-center max-w-xs text-[10px] mx-auto">
                            <div class="bg-pink-50 text-pink-700 py-1 px-1 rounded font-bold" title="ความตรงต่อเวลา">
                                <span class="block text-[8px] text-pink-400">ตรงเวลา</span>
                                ${d.q1 !== null ? d.q1 + '★' : '-'}
                            </div>
                            <div class="bg-emerald-50 text-emerald-700 py-1 px-1 rounded font-bold" title="ความปลอดภัย">
                                <span class="block text-[8px] text-emerald-400">ปลอดภัย</span>
                                ${d.q2 !== null ? d.q2 + '★' : '-'}
                            </div>
                            <div class="bg-indigo-50 text-indigo-700 py-1 px-1 rounded font-bold" title="ความสุภาพ">
                                <span class="block text-[8px] text-indigo-400">สุภาพ</span>
                                ${d.q3 !== null ? d.q3 + '★' : '-'}
                            </div>
                            <div class="bg-amber-50 text-amber-700 py-1 px-1 rounded font-bold" title="ความสะอาด">
                                <span class="block text-[8px] text-amber-400">สะอาด</span>
                                ${d.q4 !== null ? d.q4 + '★' : '-'}
                            </div>
                            <div class="bg-purple-50 text-purple-700 py-1 px-1 rounded font-bold" title="ภาพรวม">
                                <span class="block text-[8px] text-purple-400">ภาพรวม</span>
                                ${d.q5 !== null ? d.q5 + '★' : '-'}
                            </div>
                        </div>
                    `;

                    reviewsHtml = `
                        <span class="bg-pink-50 text-pink-700 font-bold px-2.5 py-1 rounded-full text-xs">
                            ${d.baseReviews} รีวิว
                        </span>
                    `;

                    actionBtnHtml = `
                        <button type="button" onclick="viewDriverFeedbackModal('${d.id}')" class="px-3 py-1.5 bg-pink-50 hover:bg-pink-100 text-pink-700 border border-pink-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 mx-auto cursor-pointer shadow-2xs active:scale-95">
                            <i class="fas fa-comment-dots text-pink-500"></i> ข้อเสนอแนะ (${d.comments.length})
                        </button>
                    `;
                } else {
                    rankBadge = `<span class="w-7 h-7 rounded-full bg-slate-50 text-slate-400 flex items-center justify-center font-normal text-xs mx-auto">-</span>`;
                    scoreHtml = `<span class="text-xs text-slate-400 font-medium italic">ยังไม่มีการประเมิน</span>`;
                    dimensionsHtml = `<span class="text-[11px] text-slate-400 italic">ยังไม่มีข้อมูลการประเมิน</span>`;
                    reviewsHtml = `<span class="text-slate-400 font-normal px-2.5 py-1 rounded-full text-xs">0 รีวิว</span>`;
                    actionBtnHtml = `
                        <button type="button" onclick="viewDriverFeedbackModal('${d.id}')" class="px-3 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-500 border border-slate-200 rounded-xl text-xs font-medium transition flex items-center gap-1.5 mx-auto cursor-pointer">
                            <i class="far fa-comment text-slate-400"></i> ข้อเสนอแนะ (0)
                        </button>
                    `;
                }

                html += `
                    <tr class="hover:bg-amber-50/30 transition group">
                        <td class="p-3 pl-2 text-center">${rankBadge}</td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <span class="font-black text-pink-600 text-sm font-mono">${d.car_id}</span>
                                <span class="text-[11px] text-gray-500">${d.plate}</span>
                            </div>
                        </td>
                        <td class="p-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl ${d.avatarBg || 'bg-pink-500'} text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                                    ${d.name ? d.name.charAt(3) || d.name.charAt(0) : 'พ'}
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm group-hover:text-pink-600 transition-colors">${d.name}</h4>
                                    <span class="text-[10px] text-slate-400 font-mono">รหัสพนักงาน: ${d.id}</span>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            ${scoreHtml}
                        </td>
                        <td class="p-3 text-center">
                            ${dimensionsHtml}
                        </td>
                        <td class="p-3 text-center">
                            ${reviewsHtml}
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            ${actionBtnHtml}
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function renderPassengerReviewsTable() {
            const tbody = document.getElementById('passengerReviewsTableBody');
            if (!tbody) return;

            if (!currentAllPassengerReviews || currentAllPassengerReviews.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="p-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="fas fa-comment-slash text-3xl text-slate-300"></i>
                                <p class="font-bold text-slate-600 text-sm">ยังไม่มีความคิดเห็นหรือข้อเสนอแนะที่ส่งจากผู้โดยสาร</p>
                                <p class="text-xs text-slate-400">เมื่อผู้โดยสารส่งแบบประเมินพร้อมพิมพ์ข้อเสนอแนะ ข้อความจริงที่เขียนเองจะแสดงที่นี่ทันที</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            currentAllPassengerReviews.forEach(r => {
                html += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 pl-2 text-xs text-slate-500 whitespace-nowrap">
                            <i class="far fa-clock mr-1 text-slate-400"></i> ${r.date}
                        </td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-pink-600 font-mono text-xs">${r.carId}</span>
                                    <span class="text-xs font-bold text-slate-700">${r.driverName}</span>
                                </div>
                                <span class="text-[10px] text-slate-400">${r.plate}</span>
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-xs font-black">
                                <i class="fas fa-star text-amber-400 text-[10px]"></i>
                                <span>${r.score}</span>
                            </div>
                        </td>
                        <td class="p-3 text-xs text-slate-700 max-w-md">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex items-start gap-2">
                                <i class="fas fa-quote-left text-slate-400 text-xs mt-0.5 shrink-0"></i>
                                <p class="font-medium text-slate-700 leading-relaxed ${r.hasComment ? '' : 'text-slate-400 italic'}">${r.comment}</p>
                            </div>
                        </td>
                        <td class="p-3 text-center text-xs text-slate-500 whitespace-nowrap font-mono">
                            <span class="bg-purple-50 text-purple-700 px-2 py-0.5 rounded text-[10px] font-semibold">${r.user}</span>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function switchRatingSubTab(tab) {
            activeRatingSubTab = tab;
            const btnLeaderboard = document.getElementById('subtab-btn-leaderboard');
            const btnReviews = document.getElementById('subtab-btn-reviews');
            const containerLeaderboard = document.getElementById('ratings-leaderboard-container');
            const containerReviews = document.getElementById('ratings-reviews-container');

            if (tab === 'leaderboard') {
                if (btnLeaderboard) {
                    btnLeaderboard.className = "px-4 py-2.5 font-black text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer";
                }
                if (btnReviews) {
                    btnReviews.className = "px-4 py-2.5 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer";
                }
                if (containerLeaderboard) containerLeaderboard.classList.remove('hidden');
                if (containerReviews) containerReviews.classList.add('hidden');
            } else {
                if (btnLeaderboard) {
                    btnLeaderboard.className = "px-4 py-2.5 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer";
                }
                if (btnReviews) {
                    btnReviews.className = "px-4 py-2.5 font-black text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer";
                }
                if (containerLeaderboard) containerLeaderboard.classList.add('hidden');
                if (containerReviews) containerReviews.classList.remove('hidden');
            }
        }

        function filterDriverRatings() {
            const searchInput = document.getElementById('driver-rating-search')?.value.trim().toLowerCase() || '';
            const starFilter = document.getElementById('driver-rating-star-filter')?.value || 'all';
            const clearBtn = document.getElementById('driver-rating-search-clear');
            if (clearBtn) {
                if (searchInput) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            const data = getLiveDriverEvaluationData();
            currentFilteredDrivers = data.drivers.filter(d => {
                const matchQuery = !searchInput || 
                    d.name.toLowerCase().includes(searchInput) ||
                    d.car_id.toLowerCase().includes(searchInput) ||
                    d.plate.toLowerCase().includes(searchInput);

                let matchStar = true;
                const score = d.baselineAvg !== null ? parseFloat(d.baselineAvg) : null;
                if (starFilter === '4.8') matchStar = score !== null && score >= 4.80;
                else if (starFilter === '4.5') matchStar = score !== null && score >= 4.50 && score < 4.80;
                else if (starFilter === 'below4.5') matchStar = score === null || score < 4.50;

                return matchQuery && matchStar;
            });

            currentAllPassengerReviews = data.reviewsList.filter(r => {
                const matchQuery = !searchInput || 
                    r.driverName.toLowerCase().includes(searchInput) ||
                    r.carId.toLowerCase().includes(searchInput) ||
                    r.plate.toLowerCase().includes(searchInput) ||
                    r.comment.toLowerCase().includes(searchInput);

                let matchStar = true;
                const score = parseFloat(r.score);
                if (starFilter === '4.8') matchStar = score >= 4.80;
                else if (starFilter === '4.5') matchStar = score >= 4.50 && score < 4.80;
                else if (starFilter === 'below4.5') matchStar = score < 4.50;

                return matchQuery && matchStar;
            });

            renderDriverRatingsTable();
            renderPassengerReviewsTable();
        }

        function clearDriverRatingSearch() {
            const input = document.getElementById('driver-rating-search');
            if (input) input.value = '';
            const starSelect = document.getElementById('driver-rating-star-filter');
            if (starSelect) starSelect.value = 'all';
            filterDriverRatings();
        }

        function viewDriverFeedbackModal(driverId) {
            const data = getLiveDriverEvaluationData();
            const driver = data.drivers.find(d => d.id === driverId) || data.drivers[0];
            if (!driver) return;

            const rank = data.drivers.filter(d => d.baseReviews > 0).findIndex(d => d.id === driver.id) + 1;

            const elName = document.getElementById('dfm-driver-name');
            if (elName) elName.innerText = driver.name;

            const elCar = document.getElementById('dfm-car-id');
            if (elCar) elCar.innerText = driver.car_id;

            const elPlate = document.getElementById('dfm-plate');
            if (elPlate) elPlate.innerText = driver.plate;

            const elEmp = document.getElementById('dfm-emp-id');
            if (elEmp) elEmp.innerText = `รหัส ${driver.id}`;

            const elAvg = document.getElementById('dfm-avg-score');
            const elReviews = document.getElementById('dfm-total-reviews');
            const elRank = document.getElementById('dfm-rank-badge');

            if (driver.baseReviews > 0) {
                if (elAvg) elAvg.innerText = `${parseFloat(driver.baselineAvg).toFixed(2)} ★`;
                if (elReviews) elReviews.innerText = `${driver.baseReviews} ครั้ง`;
                if (elRank) elRank.innerText = rank > 0 ? `อันดับที่ ${rank}` : '-';

                const q1 = driver.q1 || 5.0;
                const q2 = driver.q2 || 5.0;
                const q3 = driver.q3 || 5.0;
                const q4 = driver.q4 || 5.0;
                const q5 = driver.q5 || 5.0;

                document.getElementById('dfm-dim-1-val').innerText = `${q1} / 5.0`;
                document.getElementById('dfm-dim-1-bar').style.width = `${(q1 / 5) * 100}%`;

                document.getElementById('dfm-dim-2-val').innerText = `${q2} / 5.0`;
                document.getElementById('dfm-dim-2-bar').style.width = `${(q2 / 5) * 100}%`;

                document.getElementById('dfm-dim-3-val').innerText = `${q3} / 5.0`;
                document.getElementById('dfm-dim-3-bar').style.width = `${(q3 / 5) * 100}%`;

                document.getElementById('dfm-dim-4-val').innerText = `${q4} / 5.0`;
                document.getElementById('dfm-dim-4-bar').style.width = `${(q4 / 5) * 100}%`;

                document.getElementById('dfm-dim-5-val').innerText = `${q5} / 5.0`;
                document.getElementById('dfm-dim-5-bar').style.width = `${(q5 / 5) * 100}%`;
            } else {
                if (elAvg) elAvg.innerText = '-';
                if (elReviews) elReviews.innerText = `0 ครั้ง`;
                if (elRank) elRank.innerText = `ยังไม่มีการประเมิน`;

                document.getElementById('dfm-dim-1-val').innerText = `-`;
                document.getElementById('dfm-dim-1-bar').style.width = `0%`;

                document.getElementById('dfm-dim-2-val').innerText = `-`;
                document.getElementById('dfm-dim-2-bar').style.width = `0%`;

                document.getElementById('dfm-dim-3-val').innerText = `-`;
                document.getElementById('dfm-dim-3-bar').style.width = `0%`;

                document.getElementById('dfm-dim-4-val').innerText = `-`;
                document.getElementById('dfm-dim-4-bar').style.width = `0%`;

                document.getElementById('dfm-dim-5-val').innerText = `-`;
                document.getElementById('dfm-dim-5-bar').style.width = `0%`;
            }

            // Render passenger comments written by users
            const commentsContainer = document.getElementById('dfm-comments-list');
            if (commentsContainer) {
                const driverReviews = data.reviewsList.filter(r => 
                    (r.driverId === driver.id || 
                    r.carId === driver.car_id ||
                    (r.driverName && driver.name && r.driverName.includes(driver.name.split(' ')[0]))) &&
                    r.hasComment
                );

                if (driverReviews.length === 0) {
                    commentsContainer.innerHTML = `
                        <div class="p-6 text-center bg-slate-50 rounded-2xl border border-slate-100">
                            <i class="far fa-comment-dots text-2xl text-slate-300 mb-2"></i>
                            <p class="text-xs text-slate-600 font-bold">ยังไม่มีข้อเสนอแนะจากผู้โดยสารสำหรับพนักงานท่านนี้</p>
                            <p class="text-[11px] text-slate-400 mt-1">เมื่อผู้โดยสารเขียนข้อความเพิ่มเติมในแบบประเมิน ข้อความจริงจะแสดงที่นี่</p>
                        </div>
                    `;
                } else {
                    let cHtml = '';
                    driverReviews.forEach(r => {
                        cHtml += `
                            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs">
                                <div class="flex items-center justify-between mb-1 text-[11px]">
                                    <span class="font-bold text-slate-700 font-mono">${r.user}</span>
                                    <span class="text-amber-500 font-bold">${r.score}★</span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed font-medium">${r.comment}</p>
                                <span class="text-[9px] text-slate-400 mt-1 block">${r.date}</span>
                            </div>
                        `;
                    });
                    commentsContainer.innerHTML = cHtml;
                }
            }

            const modal = document.getElementById('driverFeedbackModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeDriverFeedbackModal() {
            const modal = document.getElementById('driverFeedbackModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function printDriverEvaluationCard() {
            window.print();
        }

        function exportDriverRatingsExcel() {
            const data = getLiveDriverEvaluationData();
            let csv = "\uFEFFอันดับ,ขบวนรถ,ทะเบียน,พนักงานขับรถ,รหัสพนักงาน,คะแนนรวม,ตรงเวลา,ความปลอดภัย,มารยาทสุภาพ,ความสะอาด,ภาพรวม,จำนวนรีวิว\n";
            data.drivers.forEach((d, idx) => {
                const avgScore = d.baselineAvg !== null ? d.baselineAvg : '-';
                const q1 = d.q1 !== null ? d.q1 : '-';
                const q2 = d.q2 !== null ? d.q2 : '-';
                const q3 = d.q3 !== null ? d.q3 : '-';
                const q4 = d.q4 !== null ? d.q4 : '-';
                const q5 = d.q5 !== null ? d.q5 : '-';
                csv += `"${idx + 1}","${d.car_id}","${d.plate}","${d.name}","${d.id}","${avgScore}","${q1}","${q2}","${q3}","${q4}","${q5}","${d.baseReviews}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", `yru_driver_evaluations_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // ========================================================
        // EXECUTIVE MAINTENANCE & WORKFLOW FUNCTIONS
        // ========================================================
        function submitExecutiveApprovalAction() {
            const ticketId = document.getElementById('modalTicketId')?.value;
            const actionType = document.getElementById('modalActionType')?.value || 'approve';
            const remarks = document.getElementById('modalApproverRemarks')?.value || '';

            if (!ticketId) {
                closeApprovalModal();
                return;
            }

            let tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
            const idx = tickets.findIndex(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));

            const isApprove = (actionType === 'approve');
            const newStatus = isApprove ? 'approved' : 'rejected';

            if (idx !== -1) {
                tickets[idx].status = newStatus;
                tickets[idx].director_action = newStatus;
                tickets[idx].director_remarks = remarks;
                tickets[idx].director_name = 'ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)';
                tickets[idx].approved_at = new Date().toISOString().replace('T', ' ').slice(0, 16);
            } else {
                tickets.push({
                    id: ticketId,
                    ticket_no: ticketId,
                    status: newStatus,
                    director_action: newStatus,
                    director_remarks: remarks,
                    director_name: 'ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)',
                    approved_at: new Date().toISOString().replace('T', ' ').slice(0, 16)
                });
            }

            try {
                localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(tickets));
                if (typeof syncRemoteStorage === 'function') {
                    syncRemoteStorage('yru_maintenance_tickets_v3', tickets);
                }
            } catch(e) {}

            closeApprovalModal();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: isApprove ? 'success' : 'info',
                    title: isApprove ? 'อนุมัติการซ่อมบำรุงเรียบร้อย' : 'บันทึกผลการพิจารณาเรียบร้อย',
                    text: `รายการคำขอ ${ticketId} ได้รับการบันทึกสถานะเรียบร้อยแล้ว`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            initExecutiveData();
        }

        function renderPendingApprovalTable() {
            const tbody = document.getElementById('pendingApprovalTableBody');
            if (!tbody) return;

            const searchQuery = (document.getElementById('pending-maint-search')?.value || '').trim().toLowerCase();
            const tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
            
            const pendingList = tickets.filter(t => {
                const isPending = (t.status === 'pending_director' || t.status === 'pending' || t.status === 'submitted' || !t.status);
                if (!isPending) return false;

                if (!searchQuery) return true;
                const code = (t.ticket_no || t.id || '').toLowerCase();
                const car = (t.car_id || '').toLowerCase();
                const driver = (t.driver_name || t.reporter || '').toLowerCase();
                return code.includes(searchQuery) || car.includes(searchQuery) || driver.includes(searchQuery);
            });

            const badge = document.getElementById('pending-table-badge');
            if (badge) badge.innerText = `${pendingList.length} รายการ`;

            if (pendingList.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400 font-medium">
                            <i class="fas fa-check-circle text-3xl text-emerald-400 mb-2 block"></i>
                            ไม่มีรายการแจ้งซ่อมที่รอการอนุมัติในขณะนี้
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            pendingList.forEach(tk => {
                const tCode = tk.ticket_no || tk.id || 'MNT-2569';
                const carId = tk.car_id || 'EV-01';
                const plate = tk.license_plate || carPlates[carId] || 'ยะลา';
                const cost = Number(tk.total_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
                const driver = tk.driver_name || tk.reporter || 'พนักงานขับรถ';
                const dateStr = tk.created_at || tk.date || '-';
                const issues = Array.isArray(tk.issues) ? tk.issues.join(' | ') : (tk.issue || tk.symptoms || 'ตรวจเช็คสภาพทั่วไป');

                html += `
                    <tr class="hover:bg-purple-50/50 bg-purple-50/20 transition border-l-4 border-l-purple-600">
                        <td class="p-3 pl-2 text-xs font-bold font-mono text-purple-950">
                            <span class="inline-block bg-purple-600 text-white px-2 py-0.5 rounded text-[10px] font-extrabold mr-1 shadow-2xs">ใหม่</span>
                            ${tCode}
                        </td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <span class="font-black text-pink-600 text-sm">${carId}</span>
                                <span class="text-[10px] text-gray-500 font-normal">${plate}</span>
                            </div>
                        </td>
                        <td class="p-3 text-xs max-w-xs">
                            <p class="font-extrabold text-slate-800 line-clamp-2" title="${issues}">${issues}</p>
                            <span class="text-[11px] text-purple-700 font-bold font-mono">ช่างประเมิน: ${cost} บาท</span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <button type="button" onclick="openPrintableFormModalFromData('${tCode}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer shadow-2xs">
                                <i class="fas fa-print"></i> ดูแบบฟอร์ม
                            </button>
                        </td>
                        <td class="p-3 text-center text-xs">
                            <span class="font-extrabold text-slate-800 block">${driver}</span>
                            <span class="text-[10px] text-slate-500 font-medium">${dateStr}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full text-[10px] font-black">
                                ด่วนที่สุด
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openViewQuotationModal('${tCode}')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                    <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                </button>
                                <button type="button" onclick="openApprovalModal('${tCode}', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                    <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function clearPendingSearch() {
            const input = document.getElementById('pending-maint-search');
            if (input) input.value = '';
            renderPendingApprovalTable();
        }

        // Automatic Table & Dashboard Initialization
        function initExecutiveData() {
            try {
                if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
                if (typeof renderDriverRatingsPage === 'function') renderDriverRatingsPage();
            } catch(e) {
                console.error("Init executive error:", e);
            }
        }

        document.addEventListener('DOMContentLoaded', initExecutiveData);
        setTimeout(initExecutiveData, 100);
        setTimeout(initExecutiveData, 500);

        window.addEventListener('storage', (event) => {
            const key = event ? event.key : null;
            if (!key || key === 'yru_surveys') {
                if (typeof renderDriverRatingsPage === 'function') renderDriverRatingsPage();
            }
            if (!key || key === 'yru_maintenance_tickets_v3') {
                if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
            }
        });
    </script>

</body>
</html>
EOT;

file_put_contents($targetFile, $topHtml . $newBottom);
echo "Successfully updated " . $targetFile . "\n";
