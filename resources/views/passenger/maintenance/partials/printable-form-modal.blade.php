<!-- Modal พิมพ์ใบขออนุญาตซ่อม และนำส่งรถไฟฟ้า ซ่อมบำรุง (แบบฟอร์มราชการ มหาวิทยาลัยราชภัฏยะลา) -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Charm:wght@400;700&display=swap');
@media print {
    /* Only activate official form print layout when explicitly printing the official form */
    body.print-official-form-active > *:not(#yruPrintableFormModal) {
        display: none !important;
    }
    
    body.print-official-form-active #yruPrintableFormModal {
        display: block !important;
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
        z-index: 9999999 !important;
        overflow: visible !important;
    }
    
    body.print-official-form-active #yruPrintableFormModal > div {
        border: none !important;
        box-shadow: none !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        display: block !important;
    }
    
    body.print-official-form-active #yruPrintableFormModal > div > div:first-child {
        display: none !important;
    }
    
    body.print-official-form-active #yruPrintableFormModal > div > div:last-child {
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
        overflow: visible !important;
    }
    
    body.print-official-form-active #officialPrintDocument {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 auto !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    
    body.print-official-form-active {
        background: white !important;
    }

    /* When NOT printing the official form, hide the form modal completely */
    body:not(.print-official-form-active) #yruPrintableFormModal {
        display: none !important;
    }
}
</style>
<div id="yruPrintableFormModal" class="fixed inset-0 z-[9999] bg-slate-900/80 backdrop-blur-sm hidden overflow-y-auto p-4 sm:p-6 flex items-center justify-center font-kanit">
    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col my-8 max-h-[92vh]">
        <!-- Modal Top Bar -->
        <div class="bg-gradient-to-r from-slate-900 via-pink-900 to-slate-900 px-6 py-4 flex items-center justify-between text-white border-b border-pink-500/30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-pink-500/20 border border-pink-400/40 flex items-center justify-center text-pink-300">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-base text-white tracking-wide">ใบแจ้งซ่อมและนำส่งรถไฟฟ้าเพื่อซ่อมบำรุง (มรย.)</h3>
                    <p class="text-xs text-pink-200/80">ระบบเอกสารราชการ มหาวิทยาลัยราชภัฏยะลา • หมายเลข: <span id="printTicketNo" class="font-mono font-bold text-amber-300">-</span></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="printOfficialForm()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer active:scale-95">
                    <i class="fas fa-print"></i> สั่งพิมพ์เอกสาร
                </button>
                <button onclick="closePrintableFormModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Printable Document Body (A4 Simulation) -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 bg-slate-50">
            <div id="officialPrintDocument" class="bg-white border border-slate-300 shadow-lg mx-auto p-6 sm:p-8 text-slate-900 max-w-[210mm] text-[12.5px] leading-relaxed relative font-kanit">
                
                <!-- Document Header -->
                <div class="text-center space-y-1 mb-3 border-b border-slate-300 pb-3">
                    <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-wide">ใบแจ้งซ่อมและนำส่งรถไฟฟ้าเพื่อซ่อมบำรุง</h2>
                    <h3 class="text-xs font-medium text-slate-700">หน่วยยานพาหนะ งานธุรการและสารบรรณ กองกลาง สำนักงานอธิการบดี มหาวิทยาลัยราชภัฏยะลา</h3>
                    <div class="text-right text-xs pt-1">
                        <span>วันที่ <b id="pDocDay" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[24px] text-center">..</b></span>
                        <span class="ml-2">เดือน <b id="pDocMonth" class="border-b border-dotted border-slate-600 px-4 inline-block min-w-[60px] text-center">....</b></span>
                        <span class="ml-2">พ.ศ. <b id="pDocYear" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[40px] text-center">....</b></span>
                    </div>
                </div>

                <!-- Section 1: Driver Request -->
                <div class="space-y-1.5 mb-3">
                    <p class="font-bold text-slate-900">เรียน ผู้อำนวยการสำนักงานอธิการบดี</p>
                    <p class="text-justify indent-8 leading-normal">
                        ข้าพเจ้า <b id="pDriverName" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[140px] text-center">...............</b> 
                        พนักงานขับรถ หมายเลขทะเบียน <b id="pLicensePlate" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[110px] text-center">............</b> 
                        ยี่ห้อ <b id="pBrand" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[70px] text-center">YRU EV</b> 
                        รุ่น <b id="pModel" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[90px] text-center">Tram Electric</b>
                    </p>
                    <p class="leading-normal">
                        มาตรวัดระยะทางปัจจุบัน <b id="pMileage" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[80px] text-center font-mono">.......</b> กิโลเมตร 
                        ขอแจ้งเพื่อให้ตรวจซ่อมบำรุง ดังต่อไปนี้
                    </p>
                    <div class="pl-4 space-y-1 pt-0.5">
                        <p>1. <span id="pIssue1" class="border-b border-dotted border-slate-400 inline-block w-[94%] pl-1 text-slate-800">-</span></p>
                        <p>2. <span id="pIssue2" class="border-b border-dotted border-slate-400 inline-block w-[94%] pl-1 text-slate-800">-</span></p>
                        <p>3. <span id="pIssue3" class="border-b border-dotted border-slate-400 inline-block w-[94%] pl-1 text-slate-800">-</span></p>
                        <p>4. <span id="pIssue4" class="border-b border-dotted border-slate-400 inline-block w-[94%] pl-1 text-slate-800">-</span></p>
                        <p>5. <span id="pIssue5" class="border-b border-dotted border-slate-400 inline-block w-[94%] pl-1 text-slate-800">-</span></p>
                    </div>
                    <div class="text-right pt-2 pr-6">
                        <p>ลงชื่อ <b id="pDriverSign" class="border-b border-dotted border-slate-600 px-4 inline-block min-w-[140px] text-center">.........................</b> พนักงานขับรถ</p>
                        <p class="pr-20 text-xs text-slate-500">( <span id="pDriverSubName">........................................</span> )</p>
                    </div>
                </div>

                <!-- Section 2: Supervisor Verification -->
                <div class="border-t border-slate-300 pt-2 mb-3 space-y-1.5">
                    <p class="font-bold text-slate-900">การตรวจสอบสภาพการชำรุด เสียหาย รถไฟฟ้าราชการส่วนกลาง</p>
                    <div class="p-2 bg-slate-50/70 border border-slate-200 rounded min-h-[42px] text-slate-800 text-[12px]" id="pSupervisorNotes">
                        (รอการตรวจสอบสภาพความเสียหายจากหัวหน้าหน่วยงานยานพาหนะ)
                    </div>
                    <div class="text-left pt-1 pl-4">
                        <p>ลงชื่อ <b id="pSupervisorSign" class="border-b border-dotted border-slate-600 px-4 inline-block min-w-[140px] text-center">.........................</b> หัวหน้าหน่วยงานยานพาหนะ</p>
                        <p class="pl-12 text-xs text-slate-500">( <span id="pSupervisorSubName">........................................</span> )</p>
                    </div>
                </div>

                <!-- Section 3 & 4: Approval Grid (Opinion, Budget, Director Sign) -->
                <div class="border border-slate-400 grid grid-cols-2 mb-3">
                    <!-- Left Column: Opinion & Budget Choice -->
                    <div class="p-3 border-r border-slate-400 space-y-2">
                        <p class="font-bold text-slate-900 underline text-xs">เสนอความเห็น</p>
                        <div class="space-y-1 pl-2 text-xs">
                            <label class="flex items-center gap-2 cursor-default">
                                <span id="chkApproved" class="w-3.5 h-3.5 rounded-full border border-slate-600 flex items-center justify-center text-[10px] font-bold"></span>
                                <span>เห็นควรอนุมัติให้ซ่อม</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-default">
                                <span id="chkRejected" class="w-3.5 h-3.5 rounded-full border border-slate-600 flex items-center justify-center text-[10px] font-bold"></span>
                                <span>ไม่ควรอนุมัติให้ซ่อม</span>
                            </label>
                        </div>
                        <p class="font-bold text-slate-900 underline pt-0.5 text-xs">งบประมาณในการดำเนินการ</p>
                        <div class="space-y-1 pl-2 text-xs">
                            <label class="flex items-center gap-2 cursor-default">
                                <span id="chkGovBudget" class="w-3.5 h-3.5 rounded-full border border-slate-600 flex items-center justify-center text-[10px] font-bold"></span>
                                <span>เงินงบประมาณแผ่นดิน</span>
                            </label>
                            <label class="flex items-start gap-2 cursor-default">
                                <span id="chkRevenueBudget" class="w-3.5 h-3.5 rounded-full border border-slate-600 flex items-center justify-center text-[10px] font-bold mt-0.5"></span>
                                <div>
                                    <span>เงินรายได้ (ระบุ) <b id="pRevenueSource" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[90px]">....................</b></span>
                                </div>
                            </label>
                        </div>
                        <div class="pt-1 text-[11px] text-slate-600 font-mono bg-slate-50 p-1.5 rounded border border-slate-200">
                            <div>ประเมินค่าซ่อม: <b id="pQuotationTotal" class="text-pink-700">0.00</b> บาท</div>
                            <div>ศูนย์บริการ/อู่: <span id="pGarageName">-</span></div>
                        </div>
                    </div>

                    <!-- Right Column: Director Approval Signature -->
                    <div class="p-3 flex flex-col justify-between text-center min-h-[140px]">
                        <p class="font-bold text-slate-900 text-sm">อนุมัติให้ซ่อม</p>
                        <div class="space-y-0.5 my-auto pt-3">
                            <p>ลงชื่อ <b id="pDirectorSign" class="border-b border-dotted border-slate-600 px-4 inline-block min-w-[140px] text-center">.........................</b></p>
                            <p class="text-xs text-slate-700">( <span id="pDirectorSubName">........................................</span> )</p>
                            <p class="text-xs font-bold text-slate-800 pt-0.5">ผู้อำนวยการสำนักงานอธิการบดี</p>
                            <p class="text-xs text-slate-700">มหาวิทยาลัยราชภัฏยะลา</p>
                        </div>
                        <div class="text-[10px] text-slate-500 pt-1" id="pDirectorSignedDate"></div>
                    </div>
                </div>

                <!-- Section 5: External Garage & Vehicle Transfer Order -->
                <div class="border-t border-slate-300 pt-2 mb-3 space-y-1.5">
                    <p class="leading-normal">
                        <b>เรียน</b> ผู้จัดการ <span id="pGarageManager" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[180px]">..................................................</span>
                    </p>
                    <p class="indent-8 leading-normal">
                        มหาวิทยาลัยราชภัฏยะลา ขอส่งรถไฟฟ้าหมายเลขทะเบียน <b id="pTransferPlate" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[110px]">..................</b> 
                        ยี่ห้อ <b id="pTransferBrand" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[70px]">YRU EV</b> เพื่อตรวจซ่อมบำรุง
                    </p>
                    <div class="text-right pt-2 pr-6">
                        <p>ลงชื่อ <b id="pTransferDirectorSign" class="border-b border-dotted border-slate-600 px-4 inline-block min-w-[140px] text-center">.........................</b></p>
                        <p class="pr-12 text-xs text-slate-700">( <span id="pTransferDirectorSubName">........................................</span> )</p>
                        <p class="text-xs font-bold text-slate-800 pr-4">ผู้อำนวยการสำนักงานอธิการบดี</p>
                    </div>
                </div>

                <!-- Bottom Signatures: Repair Completion & Central Archive -->
                <div class="border-t border-slate-400 pt-2 flex flex-row items-center justify-between text-xs gap-2">
                    <div>
                        <p>ลายมือชื่อผู้รับรถไว้ซ่อม <b id="pReceiverSign" class="border-b border-dotted border-slate-600 px-3 inline-block min-w-[130px]">..............................</b></p>
                        <p class="pl-24 text-[11px] text-slate-500">( <span id="pReceiverSubName">........................................</span> )</p>
                    </div>
                    <div>
                        <p>แฟ้มกลาง วันที่ <b id="pArchiveDay" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[20px]">..</b> / เดือน <b id="pArchiveMonth" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[20px]">..</b> / พ.ศ. <b id="pArchiveYear" class="border-b border-dotted border-slate-600 px-2 inline-block min-w-[30px]">....</b></p>
                        <p class="text-[10px] text-slate-400 text-right font-mono" id="pArchiveRef">REF: -</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Charm:wght@400;700&family=Caveat:wght@700&family=Dancing+Script:wght@700&display=swap');
</style>

<script>
function generateRealisticSignatureSvg(name, strokeColor = '#1e3a8a') {
    const cleanName = (name && name.trim()) ? name.trim() : 'ลงนามดิจิทัล';
    let hash = 0;
    for (let i = 0; i < cleanName.length; i++) {
        hash = (hash << 5) - hash + cleanName.charCodeAt(i);
        hash |= 0;
    }
    const seed = Math.abs(hash);
    const slant = -4 - (seed % 4);
    
    // Choose dynamic stroke flourish
    const flourishType = seed % 3;
    let flourishPath = '';
    if (flourishType === 0) {
        flourishPath = `M 15 48 C 55 58, 115 54, 160 44 C 190 38, 220 40, 225 35 C 210 50, 150 56, 75 56 C 38 56, 18 51, 10 47`;
    } else if (flourishType === 1) {
        flourishPath = `M 18 46 Q 110 58 215 42 C 230 40, 225 50, 200 52 C 145 56, 75 55, 25 48`;
    } else {
        flourishPath = `M 20 50 C 60 56, 120 52, 170 44 C 198 39, 222 36, 226 42 C 220 54, 165 58, 85 56 C 42 55, 22 50, 15 48`;
    }

    const topLoop = `M ${35 + (seed % 15)} 26 C ${30 + (seed % 10)} 12, ${55 + (seed % 15)} 10, ${60 + (seed % 15)} 20 C ${65 + (seed % 15)} 32, ${40 + (seed % 10)} 36, ${25 + (seed % 10)} 40`;

    return `<svg xmlns="http://www.w3.org/2000/svg" width="220" height="62" viewBox="0 0 220 62" style="display:inline-block; vertical-align:middle; overflow:visible;">
        <defs>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Charm:wght@700&amp;family=Caveat:wght@700&amp;display=swap');
                .sig-txt-${seed} {
                    font-family: 'Charm', 'Caveat', 'Brush Script MT', cursive;
                    font-size: 26px;
                    font-weight: 700;
                    fill: ${strokeColor};
                    letter-spacing: 0.5px;
                }
                .sig-stroke-${seed} {
                    stroke: ${strokeColor};
                    stroke-width: 2.2;
                    fill: none;
                    stroke-linecap: round;
                    stroke-linejoin: round;
                }
            </style>
        </defs>
        <g transform="rotate(${slant} 110 31)">
            <path d="${topLoop}" class="sig-stroke-${seed}" opacity="0.6" stroke-width="1.8"/>
            <text x="110" y="33" dominant-baseline="middle" text-anchor="middle" class="sig-txt-${seed}">${cleanName}</text>
            <path d="${flourishPath}" class="sig-stroke-${seed}" opacity="0.95"/>
        </g>
        <circle cx="210" cy="12" r="3" fill="#10b981" opacity="0.75"/>
    </svg>`;
}

function renderSignature(element, sigData, defaultFallbackName, strokeColor = '#1e3a8a') {
    if (!element) return;
    
    let sigImg = sigData;
    if (sigImg && typeof sigImg === 'string' && (sigImg.startsWith('data:image/') || sigImg.startsWith('http')) && !sigImg.includes('[TRUNCATED')) {
        if (sigImg.startsWith('data:image/svg+xml;base64,')) {
            try {
                const base64Data = sigImg.replace('data:image/svg+xml;base64,', '');
                const rawSvg = decodeURIComponent(escape(atob(base64Data)));
                element.innerHTML = rawSvg;
                return;
            } catch(e) {
                console.error("Decode inline SVG signature failed:", e);
            }
        }
        element.innerHTML = `<img src="${sigImg}" class="h-10 mx-auto object-contain max-w-[160px]" style="display:inline-block; vertical-align:middle; max-height:42px;" alt="ลายเซ็นดิจิทัล">`;
    } else if (defaultFallbackName) {
        const autoSvg = generateRealisticSignatureSvg(defaultFallbackName, strokeColor);
        element.innerHTML = autoSvg;
    }
}

function openPrintableFormModal(ticket, showModal = true) {
    if (!ticket) return;

    // If it's a -REJ ticket or child ticket, resolve parent ticket signatures from localStorage
    if (ticket && (ticket.parent_ticket_no || String(ticket.ticket_no || ticket.id).endsWith('-REJ'))) {
        const parentId = ticket.parent_ticket_no || String(ticket.ticket_no || ticket.id).replace('-REJ', '');
        try {
            const allLocal = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
            const parentTk = allLocal.find(t => (t.ticket_no === parentId || String(t.id) === String(parentId)));
            if (parentTk) {
                if (!ticket.signature_image) ticket.signature_image = parentTk.signature_image || parentTk.driver_signature_img;
                if (!ticket.driver_signature_img) ticket.driver_signature_img = parentTk.driver_signature_img || parentTk.signature_image;
                if (!ticket.supervisor_signature_img) ticket.supervisor_signature_img = parentTk.supervisor_signature_img || parentTk.supervisor_signature_image;
                if (!ticket.supervisor_signature_image) ticket.supervisor_signature_image = parentTk.supervisor_signature_image || parentTk.supervisor_signature_img;
                if (!ticket.supervisor_name) ticket.supervisor_name = parentTk.supervisor_name;
                if (!ticket.supervisor_notes && parentTk.supervisor_notes) ticket.supervisor_notes = parentTk.supervisor_notes;
            }
        } catch(e) {}
    }

    document.getElementById('printTicketNo').innerText = ticket.ticket_no || ticket.id || '-';
    
    // Doc Date
    document.getElementById('pDocDay').innerText = ticket.doc_date || '-';
    document.getElementById('pDocMonth').innerText = ticket.doc_month || '-';
    document.getElementById('pDocYear').innerText = ticket.doc_year || '-';

    // 1. Driver Section
    const driverName = ticket.driver_name || ticket.reporter || 'นายอัรฟาน มะเระ';
    document.getElementById('pDriverName').innerText = driverName;
    document.getElementById('pDriverSubName').innerText = driverName;
    
    const driverSigEl = document.getElementById('pDriverSign');
    const driverBadge = document.getElementById('pDriverAutoBadge');
    let sigImg = ticket.signature_image || ticket.driver_signature_img || ticket.driver_signature_image || ticket.signature_url;
    if (!sigImg && typeof ticket.driver_signature === 'string' && (ticket.driver_signature.startsWith('data:image/') || ticket.driver_signature.startsWith('http'))) {
        sigImg = ticket.driver_signature;
    }

    renderSignature(driverSigEl, sigImg, driverName, '#1e3a8a');
    if (driverBadge) {
        driverBadge.innerText = (sigImg && typeof sigImg === 'string' && sigImg.startsWith('data:image/png')) ? '(ลงลายมือชื่อดิจิทัล)' : '(ลงลายมือชื่ออิเล็กทรอนิกส์)';
        driverBadge.style.display = 'block';
    }

    document.getElementById('pLicensePlate').innerText = ticket.license_plate || ticket.car_id || '-';
    document.getElementById('pBrand').innerText = ticket.brand || 'YRU EV';
    document.getElementById('pModel').innerText = ticket.model || 'Tram Electric';
    document.getElementById('pMileage').innerText = Number(ticket.mileage || 0).toLocaleString();

    // Issues 1 - 5
    const rawIssues = ticket.issues || ticket.issue || ticket.symptoms || ticket.details || ticket.description;
    let issues = [];
    if (Array.isArray(rawIssues)) {
        issues = rawIssues;
    } else if (typeof rawIssues === 'string' && rawIssues.trim() !== '') {
        try {
            const parsed = JSON.parse(rawIssues);
            if (Array.isArray(parsed)) issues = parsed;
            else if (typeof parsed === 'string') issues = [parsed];
        } catch (e) {
            if (rawIssues.includes('|')) issues = rawIssues.split('|').map(s => s.trim());
            else if (rawIssues.includes('\n')) issues = rawIssues.split('\n').map(s => s.trim());
            else if (rawIssues.includes(',')) issues = rawIssues.split(',',).map(s => s.trim());
            else issues = [rawIssues];
        }
    } else if (rawIssues) {
        issues = [String(rawIssues)];
    }
    issues = issues.map(s => String(s).trim()).filter(s => s.length > 0);

    for (let i = 1; i <= 5; i++) {
        const el = document.getElementById('pIssue' + i);
        if (el) {
            el.innerText = issues[i - 1] ? issues[i - 1] : '-';
        }
    }

    // 2. Supervisor Section
    const supNotesEl = document.getElementById('pSupervisorNotes');
    const supBadge = document.getElementById('pSupervisorAutoBadge');
    const supSigEl = document.getElementById('pSupervisorSign');
    let supImg = ticket.supervisor_signature_img || ticket.supervisor_signature_image || (typeof ticket.supervisor_signature === 'string' && ticket.supervisor_signature.startsWith('data:image/') ? ticket.supervisor_signature : null);
    
    if (ticket.supervisor_notes || ticket.supervisor_name || ticket.supervisor_verified_at || ticket.supervisor_signature_img || ticket.supervisor_signature_image || ticket.status === 'pending_quotation' || ticket.status === 'pending_director' || ticket.status === 'in_progress' || ticket.status === 'completed' || ticket.status === 'rejected') {
        supNotesEl.innerText = ticket.supervisor_notes || 'ตรวจสอบสภาพการชำรุดเสียหายเรียบร้อยแล้ว';
        const supName = ticket.supervisor_name || 'นายฮาดี ลือแมะ';
        document.getElementById('pSupervisorSubName').innerText = supName;
        
        renderSignature(supSigEl, supImg, supName, '#059669');
        if (supBadge) {
            supBadge.innerText = (supImg && typeof supImg === 'string' && supImg.startsWith('data:image/png')) ? '(ลงลายมือชื่อดิจิทัล)' : '(ลงลายมือชื่ออิเล็กทรอนิกส์)';
            supBadge.style.display = 'block';
        }
    } else {
        supNotesEl.innerText = '(รอการตรวจสอบสภาพความเสียหายจากหัวหน้าหน่วยงานยานพาหนะ)';
        if (supSigEl) supSigEl.innerText = '.........................';
        document.getElementById('pSupervisorSubName').innerText = '........................................';
        if (supBadge) supBadge.style.display = 'none';
    }

    // 3. Opinion & Budget Choices
    const chkApp = document.getElementById('chkApproved');
    const chkRej = document.getElementById('chkRejected');
    const chkGov = document.getElementById('chkGovBudget');
    const chkRev = document.getElementById('chkRevenueBudget');

    chkApp.innerHTML = (ticket.director_opinion === 'approved' || ticket.status === 'in_progress' || ticket.status === 'completed') ? '✓' : '';
    chkRej.innerHTML = (ticket.director_opinion === 'rejected' || ticket.status === 'rejected') ? '✓' : '';
    chkGov.innerHTML = (ticket.budget_type === 'government_budget' || !ticket.budget_type) ? '✓' : '';
    chkRev.innerHTML = (ticket.budget_type === 'revenue_budget') ? '✓' : '';
    document.getElementById('pRevenueSource').innerText = ticket.revenue_budget_source || '....................';

    document.getElementById('pQuotationTotal').innerText = Number(ticket.total_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
    document.getElementById('pGarageName').innerText = ticket.garage_name || '-';

    // 4. Director Approval & Vehicle Transfer Signatures
    const dirBadge = document.getElementById('pDirectorAutoBadge');
    const dirSignEl = document.getElementById('pDirectorSign');
    const transferDirSignEl = document.getElementById('pTransferDirectorSign');

    if (ticket.director_signed_at || ticket.director_name || ticket.director_opinion || ticket.status === 'in_progress' || ticket.status === 'completed') {
        const defaultExecName = typeof getLoggedInExecutiveName === 'function' ? getLoggedInExecutiveName() : 'ดร.สมชาย เรียนดี';
        const dirName = (ticket.director_name && !ticket.director_name.includes('ศิริชัย')) ? ticket.director_name : defaultExecName;
        const dirSigImg = ticket.director_signature_img || ticket.director_signature_image || (typeof ticket.director_signature === 'string' && ticket.director_signature.startsWith('data:image/') ? ticket.director_signature : null);
        
        renderSignature(dirSignEl, dirSigImg, dirName, '#2563eb');
        renderSignature(transferDirSignEl, dirSigImg, dirName, '#2563eb');

        document.getElementById('pDirectorSubName').innerText = dirName;
        document.getElementById('pTransferDirectorSubName').innerText = dirName;
        document.getElementById('pDirectorSignedDate').innerText = 'ลงนามเมื่อ: ' + (ticket.director_signed_at || '');
        if (dirBadge) {
            dirBadge.innerText = (dirSigImg && dirSigImg.startsWith('data:image/png')) ? '(ลงลายมือชื่อดิจิทัล)' : '(ลงชื่ออัตโนมัติจากระบบอิเล็กทรอนิกส์)';
            dirBadge.style.display = 'block';
        }
    } else {
        if (dirSignEl) dirSignEl.innerText = '.........................';
        if (transferDirSignEl) transferDirSignEl.innerText = '.........................';
        document.getElementById('pDirectorSubName').innerText = '........................................';
        document.getElementById('pTransferDirectorSubName').innerText = '........................................';
        document.getElementById('pDirectorSignedDate').innerText = '';
        if (dirBadge) dirBadge.style.display = 'none';
    }

    // Garage Transfer
    document.getElementById('pGarageManager').innerText = ticket.garage_name || ticket.garage_manager || 'บริษัท ยะลายานยนต์ อีวี จำกัด';
    document.getElementById('pTransferPlate').innerText = ticket.license_plate || ticket.car_id || '-';
    document.getElementById('pTransferBrand').innerText = ticket.brand || 'YRU EV';

    // Receiver & Archive
    if (ticket.status === 'completed' || ticket.completed_at) {
        document.getElementById('pReceiverSign').innerText = ticket.receiver_name || 'นายประสาน งานดี (ช่าง มรย.)';
        document.getElementById('pReceiverSubName').innerText = ticket.receiver_name || 'นายประสาน งานดี';
        const arcParts = (ticket.archive_date || '').split('/');
        document.getElementById('pArchiveDay').innerText = arcParts[0] || '..';
        document.getElementById('pArchiveMonth').innerText = arcParts[1] || '..';
        document.getElementById('pArchiveYear').innerText = arcParts[2] || '....';
        document.getElementById('pArchiveRef').innerText = 'REF: ' + (ticket.archive_no || 'YRU-ARC-2026');
    } else {
        document.getElementById('pReceiverSign').innerText = '..............................';
        document.getElementById('pReceiverSubName').innerText = '........................................';
        document.getElementById('pArchiveDay').innerText = '..';
        document.getElementById('pArchiveMonth').innerText = '..';
        document.getElementById('pArchiveYear').innerText = '....';
        document.getElementById('pArchiveRef').innerText = 'REF: -';
    }

    if (showModal) {
        document.getElementById('yruPrintableFormModal').classList.remove('hidden');
    }
}

function printDirectly(ticket) {
    openPrintableFormModal(ticket, true);
    printOfficialForm();
}

function closePrintableFormModal() {
    document.getElementById('yruPrintableFormModal').classList.add('hidden');
}

function printOfficialForm() {
    document.body.classList.add('print-official-form-active');
    const images = document.querySelectorAll('#officialPrintDocument img');
    const promises = Array.from(images).map(img => {
        if (img.complete) return Promise.resolve();
        return new Promise(resolve => {
            img.onload = resolve;
            img.onerror = resolve;
        });
    });
    Promise.all(promises).then(() => {
        setTimeout(() => {
            window.print();
            setTimeout(() => {
                document.body.classList.remove('print-official-form-active');
            }, 1000);
        }, 150);
    });
}
</script>
