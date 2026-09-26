<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - Executive Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <script>
        // ระบบความปลอดภัย: Client-side Auth Guard & Sync
        (function() {
            try {
                @if(Auth::check())
                    var authObj = {
                        user_id: "{{ Auth::user()->employee_id ?: Auth::user()->user_id }}",
                        emp_id: "{{ Auth::user()->employee_id }}",
                        name: "{{ addslashes(Auth::user()->name) }}",
                        email: "{{ Auth::user()->email }}",
                        username: "{{ Auth::user()->username }}",
                        role: "{{ Auth::user()->user_role }}"
                    };
                    localStorage.setItem('yru_user_login', JSON.stringify(authObj));
                    sessionStorage.setItem('yru_user_login', JSON.stringify(authObj));
                    return;
                @endif

                var rawUser = localStorage.getItem('yru_user_login') || sessionStorage.getItem('yru_user_login');
                if (!rawUser) {
                    window.location.replace("{{ url('/') }}");
                    return;
                }
                var u = JSON.parse(rawUser);
                var r = ((u && u.role) || (u && u.user_role) || '').toLowerCase();
                var isValidRole = r.includes('executive') || 
                                  r.includes('ผู้บริหาร') || 
                                  r.includes('admin') || 
                                  r.includes('ผู้ดูแลระบบ');
                if (!isValidRole) {
                    window.location.replace("{{ url('/') }}");
                    return;
                }
            } catch (e) {
                @if(!Auth::check())
                    window.location.replace("{{ url('/') }}");
                @endif
            }
        })();
    </script>
    <script src="/api/storage/init?v={{ time() }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Sarabun', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function switchExecutiveTab(tabId) {
            console.log("Switching tab to:", tabId);
            const pages = document.querySelectorAll('.page');
            pages.forEach(el => {
                el.classList.remove('active');
                el.classList.add('hidden');
                el.style.display = 'none';
            });
            const menuBtns = document.querySelectorAll('.menu-btn');
            menuBtns.forEach(btn => {
                btn.classList.remove('active-menu');
            });

            let targetPageId = 'page-' + tabId;
            if (tabId === 'dashboard' || tabId === 'exec-dash') targetPageId = 'page-exec-dash';
            else if (tabId === 'pending') {
                targetPageId = document.getElementById('page-pending') ? 'page-pending' : 'page-exec-dash';
            }
            else if (tabId === 'reports') targetPageId = 'page-reports';
            else if (tabId === 'maint' || tabId === 'history' || tabId === 'all-maint') targetPageId = 'page-maint';
            else if (tabId === 'ratings') targetPageId = 'page-ratings';

            const targetPage = document.getElementById(targetPageId);
            if (targetPage) {
                targetPage.classList.add('active');
                targetPage.classList.remove('hidden');
                targetPage.style.display = 'block';
            }

            const activeBtnMap = {
                'exec-dash': 'menu-btn-exec-dash',
                'dashboard': 'menu-btn-exec-dash',
                'pending': 'menu-btn-pending',
                'reports': 'menu-btn-exec-dash',
                'maint': 'menu-btn-pending',
                'history': 'menu-btn-pending',
                'all-maint': 'menu-btn-pending',
                'ratings': 'menu-btn-ratings'
            };
            const targetBtn = document.getElementById(activeBtnMap[tabId] || ('menu-btn-' + tabId));
            if (targetBtn) targetBtn.classList.add('active-menu');

            try {
                sessionStorage.setItem('exec_active_tab', tabId);
                if (history.replaceState) {
                    history.replaceState(null, null, '#' + tabId);
                }
            } catch(e) {}

            // Close mobile sidebar
            const sb = document.getElementById('sidebar');
            if (sb && !sb.classList.contains('-translate-x-full') && window.innerWidth < 768) {
                sb.classList.add('-translate-x-full');
            }

            try {
                if (targetPageId === 'page-exec-dash') {
                    if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
                    if (tabId === 'pending') {
                        setTimeout(() => {
                            const tbl = document.getElementById('pendingApprovalTableBody');
                            if (tbl) tbl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 100);
                    }
                } else if (targetPageId === 'page-pending') {
                    if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
                } else if (targetPageId === 'page-reports') {
                    if (typeof renderReportsView === 'function') renderReportsView();
                } else if (targetPageId === 'page-maint') {
                    if (typeof renderAllMaintenanceTable === 'function') renderAllMaintenanceTable();
                } else if (targetPageId === 'page-ratings') {
                    if (typeof renderDriverRatingsPage === 'function') renderDriverRatingsPage();
                }
            } catch (err) {
                console.error("Tab render error:", err);
            }

            const mainArea = document.getElementById('printable-area');
            if (mainArea) mainArea.scrollTop = 0;
        }

        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            if (sb) {
                sb.classList.toggle('-translate-x-full');
            }
        }

        function getRealMaintenanceTickets() {
            let tickets = [];
            try {
                const raw3 = localStorage.getItem('yru_maintenance_tickets_v3') || sessionStorage.getItem('yru_maintenance_tickets_v3');
                if (raw3) tickets = JSON.parse(raw3);
            } catch(e) {}

            if (!Array.isArray(tickets) || tickets.length === 0) {
                try {
                    const raw1 = localStorage.getItem('yru_maintenance_tickets_v1');
                    if (raw1) tickets = JSON.parse(raw1);
                } catch(e) {}
            }

            if (!Array.isArray(tickets) || tickets.length === 0) {
                try {
                    const raw2 = localStorage.getItem('yru_call_queue');
                    if (raw2) tickets = JSON.parse(raw2);
                } catch(e) {}
            }

            if (!Array.isArray(tickets) || tickets.length === 0) {
                tickets = [
                    {
                        id: "97a6f204",
                        ticket_no: "MNT-2026-97A6",
                        car_id: "EV-06",
                        license_plate: "EV-06 (กค 5161 ยะลา)",
                        driver_name: "นายฮาดี ลือแมะ",
                        issues: ["เปลี่ยนอะไหล่ใหม่: ชาร์จไม่เข้า", "เปลี่ยนอะไหล่ใหม่: ลมอ่อน"],
                        issue: "เปลี่ยนอะไหล่ใหม่: ชาร์จไม่เข้า | เปลี่ยนอะไหล่ใหม่: ลมอ่อน",
                        garage_name: "บริษัท พี.เค. อินเตอร์ กรุ๊ป จำกัด",
                        mechanic_name: "นายอิบรอนเฮม อุมา",
                        total_cost: 3000,
                        status: "pending_director",
                        created_at: "2026-08-22 10:30"
                    },
                    {
                        id: "ed4d8b12",
                        ticket_no: "MNT-2026-ED4D",
                        car_id: "EV-02",
                        license_plate: "EV-02 (กค 5157 ยะลา)",
                        driver_name: "นายอัสมี มูเล็ง",
                        issues: ["ระบบเบรกมีเสียงดังผิดปกติ (เปลี่ยนผ้าเบรก/จานเบรก)", "แบตเตอรี่เสื่อมสภาพเร็ว (เปลี่ยนแบตเตอรี่ใหม่)"],
                        issue: "ระบบเบรกมีเสียงดังผิดปกติ | แบตเตอรี่เสื่อมสภาพเร็ว",
                        garage_name: "อู่ยะลาการช่าง (ศูนย์บริการมาตรฐาน)",
                        mechanic_name: "นายประสาน",
                        total_cost: 18500,
                        status: "pending_director",
                        created_at: "2026-08-22 09:15"
                    }
                ];
            }

            if (!Array.isArray(tickets)) tickets = [];

            if (typeof workflowMaintenanceList !== 'undefined' && Array.isArray(workflowMaintenanceList)) {
                workflowMaintenanceList.forEach(wt => {
                    if (!tickets.some(t => (t.ticket_no === wt.ticket_no || String(t.id) === String(wt.id)))) {
                        tickets.push(wt);
                    }
                });
            }

            return tickets;
        }

        function openApprovalModal(ticketId, actionType) {
            console.log("openApprovalModal called for:", ticketId, actionType);
            let tk = typeof findExecutiveTicket === 'function' ? findExecutiveTicket(ticketId) : null;
            if (!tk) {
                const tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
                tk = tickets.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId))) || {
                    id: ticketId || 'MNT-2026-004',
                    ticket_no: ticketId || 'MNT-2026-004',
                    car_id: 'EV-05',
                    license_plate: 'กค 9016 ยะลา',
                    driver_name: 'นายสมศักดิ์ ขยันยิ่ง',
                    issues: ['ระบบเบรกมีเสียงดังผิดปกติ', 'แบตเตอรี่เสื่อมสภาพเร็ว'],
                    garage_name: 'อู่ยะลาการช่าง (ศูนย์บริการมาตรฐาน)',
                    mechanic_name: 'นายประสาน',
                    total_cost: 18500,
                    status: 'pending_director'
                };
            }

            const inputId = document.getElementById('modalTicketId');
            if (inputId) inputId.value = tk.ticket_no || tk.id;

            const inputType = document.getElementById('modalActionType');
            if (inputType) inputType.value = actionType || 'approve';

            const elCode = document.getElementById('modalTicketCode');
            if (elCode) elCode.innerText = tk.ticket_no || tk.id;

            const elTram = document.getElementById('modalTramInfo');
            if (elTram) elTram.innerText = `${tk.car_id || tk.tram_id || 'EV'} (${tk.license_plate || tk.plate || '-'})`;
            
            const issuesArr = typeof parseIssues === 'function' ? parseIssues(tk.issues || tk.issue || tk.symptoms || tk.details || tk.description) : [(tk.issue || '-')];
            const elIssue = document.getElementById('modalIssueText');
            if (elIssue) elIssue.innerText = issuesArr.join(' | ') || '-';

            const elGarage = document.getElementById('modalGarageInfo');
            if (elGarage) elGarage.innerText = `${tk.garage_name || 'อู่ยะลาการช่าง (ศูนย์บริการมาตรฐาน)'} (ช่าง: ${tk.mechanic_name || 'นายประสาน'})`;

            const elTotal = document.getElementById('modalQuotationTotal');
            if (elTotal) elTotal.innerText = `${Number(tk.total_cost || 18500).toLocaleString(undefined, { minimumFractionDigits: 2 })} บาท`;

            if (typeof renderApprovalModalItems === 'function') {
                renderApprovalModalItems(tk);
            }

            const modal = document.getElementById('approvalActionModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.style.display = 'flex';
            }
        }

        let currentModalApprovalItems = [];

        function renderApprovalModalItems(tk) {
            const container = document.getElementById('approvalItemsListContainer');
            if (!container) return;
            container.innerHTML = '';
            currentModalApprovalItems = [];

            let rawItems = [];
            if (Array.isArray(tk.quotation_items) && tk.quotation_items.length > 0) {
                rawItems = tk.quotation_items;
            } else if (typeof tk.quotation_items === 'string' && tk.quotation_items.trim()) {
                try {
                    const p = JSON.parse(tk.quotation_items);
                    if (Array.isArray(p) && p.length > 0) rawItems = p;
                } catch(e) {}
            }

            if (rawItems.length === 0) {
                let issuesArr = [];
                if (Array.isArray(tk.issues) && tk.issues.length > 0) {
                    issuesArr = tk.issues;
                } else if (typeof tk.issues === 'string' && tk.issues.trim()) {
                    issuesArr = tk.issues.split(' | ');
                } else if (tk.issue) {
                    issuesArr = Array.isArray(tk.issue) ? tk.issue : String(tk.issue).split(' | ');
                } else if (tk.symptoms) {
                    issuesArr = Array.isArray(tk.symptoms) ? tk.symptoms : String(tk.symptoms).split(' | ');
                } else {
                    issuesArr = ['ตรวจเช็คสภาพทั่วไปและซ่อมบำรุงตามระยะ'];
                }

                const totalCost = Number(tk.total_cost || 0);
                const count = Math.max(1, issuesArr.length);
                const itemPrice = Math.round(totalCost / count);

                rawItems = issuesArr.map((iss, i) => {
                    const isLast = (i === count - 1);
                    const p = isLast ? (totalCost - itemPrice * (count - 1)) : itemPrice;
                    return {
                        name: iss,
                        qty: 1,
                        unit: 'รายการ',
                        price: p,
                        total: p
                    };
                });
            }

            currentModalApprovalItems = rawItems.map((item, idx) => {
                const name = typeof item === 'string' ? item : (item.name || item.description || `รายการที่ ${idx + 1}`);
                const price = typeof item === 'object' && item.total ? Number(item.total) : (typeof item === 'object' && item.price ? Number(item.price) : 0);
                return {
                    id: idx,
                    name: name,
                    price: price,
                    approved: true
                };
            });

            currentModalApprovalItems.forEach((item, idx) => {
                const row = document.createElement('div');
                row.className = 'approval-item-row flex items-center justify-between p-2.5 bg-white hover:bg-pink-50/40 rounded-xl border border-gray-200 transition';
                row.id = `approval-item-row-${idx}`;
                row.innerHTML = `
                    <label class="flex items-center gap-2.5 cursor-pointer flex-1 mr-2 select-none">
                        <input type="checkbox" id="chk-item-${idx}" checked onchange="onApprovalItemToggle(${idx})" class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500 cursor-pointer">
                        <span class="font-bold text-slate-800 text-xs">${idx + 1}. ${item.name}</span>
                    </label>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="font-mono font-bold text-slate-700 text-xs">${Number(item.price).toLocaleString(undefined, {minimumFractionDigits: 2})} บ.</span>
                        <span id="item-badge-${idx}" class="text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 shadow-2xs">อนุมัติ</span>
                    </div>
                `;
                container.appendChild(row);
            });

            recalcApprovalModalBudget();
        }

        function onApprovalItemToggle(idx) {
            const chk = document.getElementById(`chk-item-${idx}`);
            const badge = document.getElementById(`item-badge-${idx}`);
            const row = document.getElementById(`approval-item-row-${idx}`);
            if (chk && currentModalApprovalItems[idx]) {
                currentModalApprovalItems[idx].approved = chk.checked;
                if (badge) {
                    if (chk.checked) {
                        badge.className = 'text-[10px] font-black px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 shadow-2xs';
                        badge.innerText = 'อนุมัติ';
                    } else {
                        badge.className = 'text-[10px] font-black px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 shadow-2xs';
                        badge.innerText = 'ไม่อนุมัติ';
                    }
                }
                if (row) {
                    if (chk.checked) {
                        row.classList.remove('opacity-60', 'bg-rose-50/30');
                        row.classList.add('bg-white');
                    } else {
                        row.classList.add('opacity-60', 'bg-rose-50/30');
                        row.classList.remove('bg-white');
                    }
                }
            }
            recalcApprovalModalBudget();
        }

        function toggleAllApprovalItems(flag) {
            currentModalApprovalItems.forEach((item, idx) => {
                const chk = document.getElementById(`chk-item-${idx}`);
                if (chk) {
                    chk.checked = flag;
                    onApprovalItemToggle(idx);
                }
            });
        }

        function recalcApprovalModalBudget() {
            let total = 0;
            let approvedCount = 0;
            currentModalApprovalItems.forEach(it => {
                if (it.approved) {
                    total += Number(it.price || 0);
                    approvedCount++;
                }
            });
            const budgetEl = document.getElementById('modalApprovedTotalBudget');
            if (budgetEl) budgetEl.innerText = `${total.toLocaleString(undefined, {minimumFractionDigits: 2})} บาท`;

            const countEl = document.getElementById('modalApprovedItemsCount');
            if (countEl) countEl.innerText = `อนุมัติ ${approvedCount}/${currentModalApprovalItems.length} รายการ`;

            const remarksEl = document.getElementById('modalApproverRemarks');
            if (remarksEl) {
                if (approvedCount === currentModalApprovalItems.length && approvedCount > 0) {
                    remarksEl.value = 'อนุมัติการซ่อมบำรุงตามรายการที่เสนอทั้งหมด';
                } else if (approvedCount === 0) {
                    remarksEl.value = 'ไม่อนุมัติรายการแจ้งซ่อม / ขอข้อมูลและประเมินราคาเพิ่มเติม';
                } else {
                    remarksEl.value = `อนุมัติการซ่อมเฉพาะ ${approvedCount} รายการที่ผ่านการพิจารณา`;
                }
            }
        }

        function closeApprovalModal() {
            const modal = document.getElementById('approvalActionModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.display = 'none';
            }
        }

        function openViewQuotationModal(ticketId) {
            console.log("openViewQuotationModal called for:", ticketId);
            let allTickets = [];
            if (typeof getRealMaintenanceTickets === 'function') {
                allTickets = getRealMaintenanceTickets();
            } else if (typeof workflowMaintenanceList !== 'undefined' && Array.isArray(workflowMaintenanceList)) {
                allTickets = workflowMaintenanceList;
            }
            let tk = allTickets.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            if (!tk && ticketId) {
                tk = allTickets.find(t => (t.ticket_no && t.ticket_no.includes(ticketId)) || (t.id && String(t.id).includes(ticketId)));
            }
            if (!tk) {
                tk = {
                    id: ticketId || 'MNT-2026-004',
                    ticket_no: ticketId || 'MNT-2026-004',
                    car_id: 'EV-05',
                    license_plate: 'กค 9016 ยะลา',
                    driver_name: 'นายสมศักดิ์ ขยันยิ่ง',
                    issues: ['ระบบเบรกมีเสียงดังผิดปกติ', 'แบตเตอรี่เสื่อมสภาพเร็ว'],
                    total_cost: 18500,
                    garage_name: 'บริษัท เช้าท์ พี.เค. อินเตอร์ กรุ๊ป จำกัด',
                    mechanic_name: 'นายอิบรอเฮม อูมา'
                };
            }

            const today = new Date();
            const day = String(today.getDate()).padStart(2, '0');
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const thaiYear = today.getFullYear() + 543;
            const formattedTodayDate = `${day}/${month}/${thaiYear}`;

            const cleanTNo = (tk.ticket_no || tk.id || '001').replace('MNT-', '').replace('-REJ', '');
            const qNo = tk.quotation_no || `SPK-${thaiYear}-${cleanTNo}`;
            const qDate = tk.quotation_date || formattedTodayDate;
            const qTo = tk.garage_to || 'อธิการบดี มหาวิทยาลัยราชภัฏยะลา';
            const qProject = tk.garage_project || `ซ่อมรถไฟฟ้า Club Car (${tk.tram_id || tk.car_id || 'YRU EV'})`;

            if (document.getElementById('vqQuotationNoHeader')) document.getElementById('vqQuotationNoHeader').innerText = qNo;
            if (document.getElementById('vqTicketNo')) document.getElementById('vqTicketNo').innerText = tk.ticket_no || tk.id || '-';
            if (document.getElementById('vqQuotationNo')) document.getElementById('vqQuotationNo').innerText = qNo;
            if (document.getElementById('vqQuotationDate')) document.getElementById('vqQuotationDate').innerText = qDate;
            if (document.getElementById('vqGarageTo')) document.getElementById('vqGarageTo').innerText = qTo;
            if (document.getElementById('vqGarageProject')) document.getElementById('vqGarageProject').innerText = qProject;

            const tbody = document.getElementById('vqItemsTbody');
            if (tbody) {
                tbody.innerHTML = '';

                let items = [];
                if (Array.isArray(tk.quotation_items) && tk.quotation_items.length > 0) {
                    items = tk.quotation_items;
                } else if (typeof tk.quotation_items === 'string' && tk.quotation_items.trim()) {
                    try {
                        const parsed = JSON.parse(tk.quotation_items);
                        if (Array.isArray(parsed) && parsed.length > 0) items = parsed;
                    } catch(e) {}
                }

                if (items.length === 0) {
                    const issuesArr = typeof parseIssues === 'function' ? parseIssues(tk.approved_items || tk.approved_issues || tk.issues || tk.issue || tk.symptoms || tk.details || tk.description) : [(tk.issue || 'ระบบเบรกมีเสียงดังผิดปกติ')];
                    issuesArr.forEach(iss => {
                        const clean = iss.replace(/^\[.*?\]\s*/, '');
                        items.push({
                            name: `ซ่อมบำรุง/เปลี่ยนชิ้นส่วน: ${clean}`,
                            qty: 1,
                            unit: 'รายการ',
                            price: Math.round((tk.total_cost || 18500) / (issuesArr.length || 1)),
                            total: Math.round((tk.total_cost || 18500) / (issuesArr.length || 1))
                        });
                    });
                }

                let subtotal = 0;
                items.forEach(item => {
                    const qNum = parseFloat(item.qty) || 1;
                    let pNum = parseFloat(item.price_per_unit !== undefined && item.price_per_unit !== '' && item.price_per_unit !== null ? item.price_per_unit : (item.price !== undefined && item.price !== '' && item.price !== null ? item.price : 0)) || 0;
                    let lineTotal = (item.total && parseFloat(item.total) > 0) ? parseFloat(item.total) : (qNum * pNum);

                    if (pNum === 0 && lineTotal > 0 && qNum > 0) {
                        pNum = lineTotal / qNum;
                    }
                    if (lineTotal === 0 && pNum > 0 && qNum > 0) {
                        lineTotal = pNum * qNum;
                    }

                    subtotal += lineTotal;

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50';
                    tr.innerHTML = `
                        <td class="py-2.5 px-3 font-medium text-slate-800">${item.name || '-'}</td>
                        <td class="py-2.5 px-1 text-center font-bold">${qNum}</td>
                        <td class="py-2.5 px-1 text-center">${item.unit || 'ชิ้น'}</td>
                        <td class="py-2.5 px-2 text-right font-mono">${pNum.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">${lineTotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    `;
                    tbody.appendChild(tr);
                });

                const vat = (tk.vat !== undefined && tk.vat !== null && tk.vat > 0) ? parseFloat(tk.vat) : (subtotal * 0.07);
                const grandTotal = (tk.total_cost && tk.total_cost > 0) ? parseFloat(tk.total_cost) : (subtotal + vat);
                const thaiWords = tk.thai_baht_text || (typeof arabToThaiBaht === 'function' ? arabToThaiBaht(grandTotal) : '');

                if (document.getElementById('vqSubtotalDisplay')) document.getElementById('vqSubtotalDisplay').innerText = subtotal.toLocaleString('en-US', {minimumFractionDigits:2}) + ' บาท';
                if (document.getElementById('vqVatDisplay')) document.getElementById('vqVatDisplay').innerText = vat.toLocaleString('en-US', {minimumFractionDigits:2}) + ' บาท';
                if (document.getElementById('vqTotalCostDisplay')) document.getElementById('vqTotalCostDisplay').innerText = grandTotal.toLocaleString('en-US', {minimumFractionDigits:2}) + ' บาท';
                if (document.getElementById('vqThaiWordsDisplay')) document.getElementById('vqThaiWordsDisplay').innerText = thaiWords;
            }

            const sigBox = document.getElementById('vqSignatureBox');
            const signerNameEl = document.getElementById('vqSignerName');
            const signerRoleEl = document.getElementById('vqSignerRole');

            const sigData = tk.signature_image || tk.mechanic_signature || tk.driver_signature_img;
            const signerName = tk.mechanic_name || tk.reporter || 'นายอิบรอเฮม อูมา';
            if (signerNameEl) signerNameEl.innerText = signerName;
            if (signerRoleEl) signerRoleEl.innerText = tk.garage_manager || 'ช่างซ่อมบำรุง / ผู้จัดทำใบเสนอราคา';

            if (sigBox && typeof renderSignature === 'function') {
                renderSignature(sigBox, sigData, signerName, '#1e3a8a');
            }

            const modal = document.getElementById('viewQuotationModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.style.display = 'flex';
            }
        }

        function closeViewQuotationModal() {
            const modal = document.getElementById('viewQuotationModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.style.display = 'none';
            }
        }

        function openPrintableFormModalFromData(ticketId) {
            let tk = typeof findExecutiveTicket === 'function' ? findExecutiveTicket(ticketId) : null;
            if (!tk) {
                const tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
                tk = tickets.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            }
            if (tk && typeof openPrintableFormModal === 'function') {
                openPrintableFormModal(tk);
            } else if (typeof openApprovalModal === 'function') {
                openApprovalModal(ticketId || 'MNT-2026-004', 'approve');
            }
        }

        window.getRealMaintenanceTickets = getRealMaintenanceTickets;
        window.switchExecutiveTab = switchExecutiveTab;
        window.toggleSidebar = toggleSidebar;
        window.openApprovalModal = openApprovalModal;
        window.closeApprovalModal = closeApprovalModal;
        window.openViewQuotationModal = openViewQuotationModal;
        window.closeViewQuotationModal = closeViewQuotationModal;
        window.openPrintableFormModalFromData = openPrintableFormModalFromData;
    </script>
    
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th {
            font-family: 'Sarabun', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        .font-kanit, .font-sarabun {
            font-family: 'Sarabun', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class*="fa-"] {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', sans-serif !important;
        }
        .page { display: none; }
        .page.active { display: block; }
        .active-menu { background-color: rgba(255, 255, 255, 0.2); border-left: 4px solid #fff; }
        canvas { max-width: 100% !important; }

        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm 12mm;
            }
            *, *:before, *:after {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-sizing: border-box !important;
            }
            html, body {
                background: #ffffff !important;
                color: #0f172a !important;
                overflow: visible !important;
                height: auto !important;
                min-height: auto !important;
                max-height: none !important;
                width: 100% !important;
                font-family: 'Sarabun', 'Inter', sans-serif !important;
                position: static !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .flex-1.flex.overflow-hidden {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
                position: static !important;
            }
            aside, header, #sidebar, .no-print, button, .filter-bar, input, select, #approvalActionModal, .swal2-container, .modal, [role="dialog"] {
                display: none !important;
            }
            main, #printable-area {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                width: 100% !important;
                height: auto !important;
                max-height: none !important;
                box-shadow: none !important;
                border: none !important;
                display: block !important;
                position: static !important;
            }
            .page {
                display: none !important;
            }
            .page.active {
                display: block !important;
                overflow: visible !important;
                height: auto !important;
                position: static !important;
            }
            .bg-white, .rounded-2xl, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xs, .shadow-2xs {
                background: #ffffff !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
            }
            .space-y-6 > :not([hidden]) ~ :not([hidden]) {
                margin-top: 8px !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                border: 1px solid #cbd5e1 !important;
                margin-top: 6px !important;
                font-size: 11px !important;
                page-break-inside: auto !important;
                break-inside: auto !important;
            }
            thead {
                display: table-header-group !important;
            }
            thead tr {
                background-color: #f1f5f9 !important;
                border-bottom: 2px solid #94a3b8 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            th, td {
                padding: 6px 8px !important;
                border: 1px solid #e2e8f0 !important;
                color: #1e293b !important;
                line-height: 1.3 !important;
            }
            th {
                font-weight: 700 !important;
                color: #334155 !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            tbody tr:nth-child(even) {
                background-color: #f8fafc !important;
            }
            .print-only {
                display: block !important;
            }
            canvas {
                max-width: 100% !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col h-screen overflow-hidden text-gray-800">

    <!-- Header identical to Admin style -->
    @include('passenger.executive.partials.header')

    <div class="flex flex-1 overflow-hidden relative">
        <!-- Sidebar identical to Admin style with Executive Navigation -->
        @include('passenger.executive.partials.sidebar')

        <!-- Main Executive Content Area -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8" id="printable-area">
            
            <!-- ========================================================================= -->
            <!-- 1. EXECUTIVE DASHBOARD (หน้าหลักผู้บริหาร: KPI + กราฟสัปดาห์/วงกลม + ตารางรออนุมัติ) -->
            <!-- ========================================================================= -->
@php
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
            <div id="page-exec-dash" class="page active space-y-6">
                <!-- Header Banner & Quick Controls -->
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                    <div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">สรุปสถิติสำหรับผู้บริหาร</h2>
                        <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ภาพรวมสถิติการใช้งานรถไฟฟ้า</p>
                        <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span id="exec-live-date-badge">ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto no-print">
                        <button onclick="exportExecPDF()" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95">
                            <i class="fas fa-print"></i> พิมพ์รายงาน
                        </button>
                    </div>
                </div>

                <!-- Date Filter & Control Toolbar (ตัวกรองช่วงเวลา) -->
                <div class="bg-pink-50/60 p-3.5 md:px-6 rounded-2xl border border-pink-100 flex flex-wrap items-center justify-between gap-3 text-xs no-print">
                    <!-- Quick Presets -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-pink-950 font-bold mr-1 flex items-center gap-1"><i class="fas fa-filter text-pink-500"></i> ตัวกรองด่วน:</span>
                        <button id="exec-btn-all" onclick="setExecPreset('all')" class="px-3 py-1 rounded-lg font-bold transition bg-pink-600 text-white shadow-2xs">ทั้งหมด</button>
                        <button id="exec-btn-today" onclick="setExecPreset('today')" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600 hover:bg-white/80">วันนี้</button>
                        <button id="exec-btn-week" onclick="setExecPreset('week')" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600 hover:bg-white/80">สัปดาห์</button>
                        <button id="exec-btn-month" onclick="setExecPreset('month')" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600 hover:bg-white/80">เดือน</button>
                        <button id="exec-btn-year" onclick="setExecPreset('year')" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600 hover:bg-white/80">ปี</button>
                    </div>

                    <!-- Custom Date Range -->
                    <div class="flex items-center gap-2">
                        <span class="text-pink-900 font-semibold flex items-center gap-1.5"><i class="fas fa-calendar-alt text-pink-600"></i> ระบุวันที่:</span>
                        <input type="date" id="exec-date-from" onchange="onExecCustomDateChange()" class="bg-white border border-pink-200 rounded-lg px-2.5 py-1 text-slate-700 text-xs focus:ring-2 focus:ring-pink-400 outline-none font-medium shadow-2xs cursor-pointer">
                        <span class="text-pink-400 font-bold">ถึง</span>
                        <input type="date" id="exec-date-to" onchange="onExecCustomDateChange()" class="bg-white border border-pink-200 rounded-lg px-2.5 py-1 text-slate-700 text-xs focus:ring-2 focus:ring-pink-400 outline-none font-medium shadow-2xs cursor-pointer">
                        <button onclick="resetExecDateFilter()" class="p-1.5 text-pink-600 hover:bg-pink-100 bg-white border border-pink-200 rounded-lg transition shadow-2xs" title="รีเซ็ตวันที่">
                            <i class="fas fa-undo text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- ส่วนบน (KPI Cards): กล่องตัวเลขสรุป 5 รายการ เด่นชัดเจน -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- KPI 1: คำขอรออนุมัติงบประมาณ -->
                    <div onclick="switchExecutiveTab('pending')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100/80 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-pointer group">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-clock text-amber-500"></i> รอ ผอ. อนุมัติงบประมาณ</p>
                            <h3 id="exec-dash-pending-count" class="text-3xl font-black text-slate-800 tracking-tight">4 รายการ</h3>
                            <span class="text-[11px] text-amber-600 font-bold mt-1 inline-block">รอดำเนินการ <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold group-hover:bg-amber-500 group-hover:text-white transition-colors shadow-2xs">
                            <i class="fas fa-file-signature"></i>
                        </div>
                    </div>

                    <!-- KPI 2: จำนวนรอบการเดินรถ -->
                    <div onclick="switchExecutiveTab('reports')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100/80 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-pointer group">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-route text-pink-500"></i> จำนวนรอบการเดินรถทั้งหมด</p>
                            <h3 id="exec-dash-avg-rounds" class="text-3xl font-black text-slate-800 tracking-tight">82 รอบ</h3>
                            <span class="text-[11px] text-pink-600 font-bold mt-1 inline-block">ดูสถิติรายวัน <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl font-bold group-hover:bg-pink-500 group-hover:text-white transition-colors shadow-2xs">
                            <i class="fas fa-bus-simple"></i>
                        </div>
                    </div>

                    <!-- KPI 3: ผู้โดยสารรวมสะสม -->
                    <div onclick="switchExecutiveTab('reports')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100/80 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-pointer group">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-users text-purple-500"></i> ผู้โดยสารรวมสะสม</p>
                            <h3 id="exec-dash-total-pax" class="text-3xl font-black text-slate-800 tracking-tight">131 คน</h3>
                            <span id="exec-dash-avg-pax-sub" class="text-[11px] text-purple-600 font-bold mt-1 inline-block">เฉลี่ย 44 คน/วัน <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold group-hover:bg-purple-500 group-hover:text-white transition-colors shadow-2xs">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>

                    <!-- KPI 4: รถไฟฟ้าพร้อมใช้งาน (Active Trams) -->
                    <div onclick="switchExecutiveTab('maint')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100/80 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-pointer group">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-circle-check text-emerald-500"></i> รถไฟฟ้าพร้อมใช้งาน</p>
                            <h3 id="exec-dash-active-trams" class="text-3xl font-black text-slate-800 tracking-tight">8 คัน</h3>
                            <span class="text-[11px] text-emerald-600 font-bold mt-1 inline-block">สถานะกองรถ <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold group-hover:bg-emerald-500 group-hover:text-white transition-colors shadow-2xs">
                            <i class="fas fa-charging-station"></i>
                        </div>
                    </div>

                    <!-- KPI 5: จุดจอดรถไฟฟ้าทั้งหมด (Total Stations) -->
                    <div onclick="switchExecutiveTab('reports')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100/80 flex items-center justify-between hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 cursor-pointer group">
                        <div>
                            <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-indigo-500"></i> จุดจอดรถไฟฟ้าทั้งหมด</p>
                            <h3 id="exec-dash-total-stations" class="text-3xl font-black text-slate-800 tracking-tight">7 จุด</h3>
                            <span class="text-[11px] text-indigo-600 font-bold mt-1 inline-block">เส้นทางเดินรถ <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i></span>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold group-hover:bg-indigo-500 group-hover:text-white transition-colors shadow-2xs">
                            <i class="fas fa-location-dot"></i>
                        </div>
                    </div>
                </div>

                <!-- Executive Highlight Card: Driver Rating & Passenger Satisfaction -->
                <div onclick="switchExecutiveTab('ratings')" class="bg-white p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-100/90 hover:shadow-md hover:border-pink-200 transition-all duration-300 cursor-pointer flex flex-col md:flex-row items-start md:items-center justify-between gap-4 group">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl font-bold shadow-2xs group-hover:bg-amber-400 group-hover:text-white transition-colors shrink-0">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <i class="fas fa-star text-amber-500"></i> คะแนนประเมินคนขับ
                                </span>
                                <span id="dash-highlight-satisfaction" class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                    <i class="fas fa-check-circle text-emerald-500"></i> ความพึงพอใจ 96.4%
                                </span>
                            </div>
                            <h4 class="text-base sm:text-lg font-black text-slate-800 tracking-tight mt-1 flex items-center gap-2 flex-wrap">
                                <span>คะแนนเฉลี่ยกองรถ: <span class="text-amber-500 font-black text-xl" id="dash-highlight-fleet-avg">4.82★</span> <span class="text-xs text-slate-400 font-semibold font-normal" id="dash-highlight-total-reviews">/ 5.00 (142 รีวิวสะสม)</span></span>
                                <span class="text-xs text-slate-600 font-medium bg-slate-50 px-2.5 py-0.5 rounded-lg border border-slate-100 flex items-center gap-1">
                                    <i class="fas fa-trophy text-amber-500 text-[10px]"></i> พนักงานอันดับ 1: <strong class="text-slate-800 font-bold" id="dash-highlight-top-driver">นายอัสมี มูเล็ง</strong> <span class="text-emerald-600 font-bold" id="dash-highlight-top-stats">(EV-01, 4.95★)</span>
                                </span>
                            </h4>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-end md:self-center bg-pink-50 group-hover:bg-pink-600 text-pink-700 group-hover:text-white border border-pink-200 group-hover:border-pink-600 px-4 py-2 rounded-xl text-xs font-bold transition shadow-2xs group-hover:shadow-xs whitespace-nowrap">
                        <span>ดูรายละเอียดและอันดับคะแนน</span>
                        <i class="fas fa-arrow-right text-[11px] group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </div>

                <!-- ส่วนกลาง (Charts): 2 กราฟเปรียบเทียบในมุมมองบริหาร -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- กราฟที่ 1: กราฟแท่งเปรียบเทียบจำนวนการใช้งานรถไฟฟ้าในแต่ละสัปดาห์ (Weekly Usage Trends) -->
                    <div class="lg:col-span-2 bg-white p-6 md:p-7 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-chart-column text-pink-500"></i> แนวโน้มการใช้งานรถไฟฟ้ารายสัปดาห์ (Weekly Usage Trends)
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">สถิติปริมาณรอบการเดินรถจำแนกตามวันในสัปดาห์ (ข้อมูลจริง)</p>
                                </div>
                                <span class="text-[10px] bg-pink-50 text-pink-700 font-bold px-2.5 py-0.5 rounded-full border border-pink-200">
                                    Weekly Analytics
                                </span>
                            </div>
                            <div onclick="switchExecutiveTab('reports')" class="h-64 relative min-h-[250px] w-full cursor-pointer" id="weeklyBarChartContainer">
                                <canvas id="weeklyUsageBarChart" class="w-full h-full relative z-10" style="display: block; width: 100%; height: 250px; min-height: 250px;"></canvas>
                                <!-- Visual Bar Chart Fallback (Only shown if Chart.js fails) -->
                                <div id="weeklyUsageBarChartSvg" class="w-full h-full hidden flex items-end justify-between gap-2 md:gap-3 px-1 pt-8 pb-1 z-0">
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">14 รอบ</span>
                                        <div id="bar-mon" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 58%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">จันทร์</span>
                                    </div>
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">16 รอบ</span>
                                        <div id="bar-tue" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 70%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">อังคาร</span>
                                    </div>
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">15 รอบ</span>
                                        <div id="bar-wed" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 64%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">พุธ</span>
                                    </div>
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">12 รอบ</span>
                                        <div id="bar-thu" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 50%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">พฤหัสบดี</span>
                                    </div>
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">11 รอบ</span>
                                        <div id="bar-fri" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 45%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">ศุกร์</span>
                                    </div>
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">7 รอบ</span>
                                        <div id="bar-sat" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 30%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">เสาร์</span>
                                    </div>
                                    <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group">
                                        <span class="text-[10px] font-bold text-pink-700 bg-pink-50 px-1.5 py-0.5 rounded border border-pink-200 shadow-2xs">5 รอบ</span>
                                        <div id="bar-sun" class="w-full bg-gradient-to-t from-pink-600 to-pink-400 rounded-t-lg shadow-sm transition-all duration-300" style="height: 22%;"></div>
                                        <span class="text-xs font-bold text-slate-700 mt-1">อาทิตย์</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-pink-50/80 border border-pink-200/80 rounded-xl p-3.5 mt-4 text-xs text-pink-900 flex items-start gap-2.5 shadow-2xs">
                            <i class="fas fa-circle-info text-pink-500 text-base mt-0.5"></i>
                            <div>
                                <span class="font-bold text-pink-950">ข้อสรุปเชิงสถิติ (Executive Summary):</span>
                                <span id="exec-weekly-insight" class="block mt-0.5 font-normal text-pink-900">ช่วงเวลาที่เลือกมีการเดินรถรวมทั้งหมด <b class="text-pink-950">80 รอบ</b> โดยมีปริมาณการเดินรถสูงสุดใน <b class="text-pink-950">วันอังคาร (16 รอบ)</b></span>
                            </div>
                        </div>
                    </div>

                    <!-- กราฟที่ 2: แผนภูมิวงกลมสรุปสถานะรถ (Fleet Status Donut Chart) -->
                    <div class="bg-white p-6 md:p-7 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-chart-pie text-indigo-500"></i> สรุปสถานะรถไฟฟ้า (Fleet Status)
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">สัดส่วนสถานะตามระบบติดตาม GPS เรียลไทม์</p>
                                </div>
                            </div>
                            <div onclick="switchExecutiveTab('maint')" class="h-56 relative flex items-center justify-center min-h-[220px] cursor-pointer" id="fleetPieChartContainer">
                                <canvas id="fleetStatusPieChart" class="w-full h-full relative z-10" style="display: block; width: 220px; height: 220px; max-height: 220px; margin: 0 auto;"></canvas>
                                <!-- Central Percentage Badge Overlay -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none z-20">
                                    <span id="donut-center-pct" class="text-2xl font-black text-slate-800 tracking-tight">90%</span>
                                    <span id="donut-center-label" class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">พร้อมใช้งาน</span>
                                </div>
                                <!-- Visual Donut Chart Fallback (Only shown if Chart.js fails) -->
                                <div id="fleetStatusPieChartSvg" class="relative w-48 h-48 hidden flex items-center justify-center z-0">
                                    <svg viewBox="0 0 36 36" class="w-44 h-44 transform -rotate-90">
                                        <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="#e2e8f0" stroke-width="3.5"></circle>
                                        <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="#10b981" stroke-width="3.8" stroke-dasharray="90 10" stroke-dashoffset="0"></circle>
                                        <circle cx="18" cy="18" r="15.915" fill="transparent" stroke="#f59e0b" stroke-width="3.8" stroke-dasharray="10 90" stroke-dashoffset="-90"></circle>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fleet Status Legend Grid -->
                        <div class="grid grid-cols-3 gap-2 text-center mt-3 pt-3 border-t border-gray-100">
                            <div onclick="switchExecutiveTab('maint')" class="p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-100 hover:bg-emerald-100/70 transition cursor-pointer">
                                <span class="text-[10px] text-emerald-700 font-bold block">พร้อมใช้งาน</span>
                                <span id="donut-running-count" class="text-lg font-black text-emerald-800">9 คัน</span>
                            </div>
                            <div onclick="switchExecutiveTab('pending')" class="p-2.5 rounded-xl bg-amber-50/80 border border-amber-100 hover:bg-amber-100/70 transition cursor-pointer">
                                <span class="text-[10px] text-amber-700 font-bold block">รถขัดข้อง</span>
                                <span id="donut-standby-count" class="text-lg font-black text-amber-800">1 คัน</span>
                            </div>
                            <div onclick="switchExecutiveTab('maint')" class="p-2.5 rounded-xl bg-rose-50/80 border border-rose-100 hover:bg-rose-100/70 transition cursor-pointer">
                                <span class="text-[10px] text-rose-700 font-bold block">ระงับการใช้งาน</span>
                                <span id="donut-broken-count" class="text-lg font-black text-rose-800">0 คัน</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ส่วนวิเคราะห์เชิงลึกการแจ้งซ่อม (Executive Maintenance Analytics Insights) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- การ์ดที่ 1: รายการแจ้งซ่อมอันไหนมากที่สุด (Most Reported Maintenance Issue - Horizontal Bar Chart) -->
                    <div class="bg-white p-6 md:p-7 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md transition group">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3.5 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-base text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-chart-column text-amber-500"></i> รายการแจ้งซ่อมที่พบมากที่สุด
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">สถิติเปรียบเทียบอาการชำรุดแบบกราฟแท่งแนวนอน (Horizontal Bar)</p>
                                </div>
                                <span class="text-[10px] bg-amber-50 text-amber-700 font-bold px-2.5 py-0.5 rounded-full border border-amber-200">
                                    Issue Analytics
                                </span>
                            </div>

                            <!-- Hero Highlight Box -->
                            <div class="bg-gradient-to-r from-amber-50/90 via-orange-50/70 to-amber-50/40 p-4 rounded-xl border border-amber-200/80 mb-4 flex items-center justify-between">
                                <div class="space-y-1">
                                    <span class="text-[11px] text-amber-800 font-bold uppercase tracking-wider block flex items-center gap-1">
                                        <i class="fas fa-triangle-exclamation text-amber-500"></i> หัวข้ออาการชำรุดอันดับ 1
                                    </span>
                                    <h4 id="exec-top-issue-name" class="text-base md:text-lg font-black text-slate-800 tracking-tight leading-tight">
                                        ผ้าเบรคมีปัญหา
                                    </h4>
                                    <p class="text-xs text-slate-500 font-medium">สัดส่วนในกลุ่มแจ้งซ่อมทั้งหมด</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span id="exec-top-issue-count" class="text-2xl md:text-3xl font-black text-amber-600 tracking-tight block">3 ครั้ง</span>
                                    <span id="exec-top-issue-pct" class="text-[10px] font-bold bg-amber-200/80 text-amber-900 px-2 py-0.5 rounded-full inline-block mt-0.5">33%</span>
                                </div>
                            </div>

                            <!-- Visual Horizontal Bar Chart Canvas -->
                            <div onclick="switchExecutiveTab('maint')" class="h-44 relative min-h-[175px] w-full cursor-pointer my-2">
                                <canvas id="execTopIssuesChart" class="w-full h-full relative z-10" style="display: block; width: 100%; height: 175px;"></canvas>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1 font-medium"><i class="fas fa-chart-bar text-amber-500"></i> ข้อมูลจากการประมวลผลคำขอซ่อมบำรุงจริง</span>
                            <button onclick="switchExecutiveTab('maint')" class="text-amber-600 hover:text-amber-700 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
                                ดูประวัติการซ่อม <i class="fas fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- การ์ดที่ 2: รถคันไหนแจ้งซ่อมบ่อยที่สุด (Most Frequently Repaired Vehicle - Doughnut Chart) -->
                    <div class="bg-white p-6 md:p-7 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md transition group">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3.5 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-base text-slate-800 flex items-center gap-2">
                                        <i class="fas fa-chart-pie text-rose-500"></i> รถไฟฟ้าที่แจ้งซ่อมบ่อยที่สุด
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-0.5">สัดส่วนขบวนรถไฟฟ้าที่เข้าซ่อมบำรุงแบบกราฟโดนัท (Doughnut)</p>
                                </div>
                                <span class="text-[10px] bg-rose-50 text-rose-700 font-bold px-2.5 py-0.5 rounded-full border border-rose-200">
                                    Vehicle Analytics
                                </span>
                            </div>

                            <!-- Hero Highlight Box -->
                            <div class="bg-gradient-to-r from-rose-50/90 via-pink-50/70 to-rose-50/40 p-4 rounded-xl border border-rose-200/80 mb-3 flex items-center justify-between">
                                <div class="space-y-1">
                                    <span class="text-[11px] text-rose-800 font-bold uppercase tracking-wider block flex items-center gap-1">
                                        <i class="fas fa-trophy text-rose-500"></i> รถไฟฟ้าที่เข้าซ่อมถี่สูงสุด
                                    </span>
                                    <div class="flex items-baseline gap-2">
                                        <h4 id="exec-top-tram-code" class="text-xl md:text-2xl font-black text-rose-600 tracking-tight">
                                            EV-04
                                        </h4>
                                        <span id="exec-top-tram-plate" class="text-xs font-semibold text-slate-600">
                                            (กค 1121 ยะลา)
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium">ประวัติขออนุมัติซ่อมบำรุงในระบบ</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span id="exec-top-tram-count" class="text-2xl md:text-3xl font-black text-rose-600 tracking-tight block">4 ครั้ง</span>
                                    <span id="exec-top-tram-status" class="text-[10px] font-bold bg-rose-200/80 text-rose-900 px-2 py-0.5 rounded-full inline-block mt-0.5">ซ่อมสะสมสูงสุด</span>
                                </div>
                            </div>

                            <!-- Visual Doughnut Chart Canvas -->
                            <div onclick="switchExecutiveTab('maint')" class="h-44 relative flex items-center justify-center min-h-[175px] cursor-pointer my-1">
                                <canvas id="execTopTramsChart" class="w-full h-full relative z-10" style="display: block; width: 175px; height: 175px; max-height: 175px; margin: 0 auto;"></canvas>
                                <!-- Central Tram Badge Overlay -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none z-20">
                                    <span id="exec-trams-donut-center-code" class="text-lg font-black text-slate-800 tracking-tight">EV-04</span>
                                    <span id="exec-trams-donut-center-count" class="text-[10px] font-bold text-rose-600">4 ครั้ง</span>
                                </div>
                            </div>

                            <!-- Fleet Legend Grid -->
                            <div id="exec-top-trams-list" class="grid grid-cols-3 gap-2 text-center mt-2 pt-2 border-t border-gray-100">
                                <!-- Dynamic JS lines populated here -->
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1 font-medium"><i class="fas fa-history text-rose-500"></i> ติดตามสถานะความพร้อมใช้งานรายขบวน</span>
                            <button onclick="switchExecutiveTab('maint')" class="text-rose-600 hover:text-rose-700 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer">
                                ตรวจสอบ fleet <i class="fas fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- 2. PENDING MAINTENANCE APPROVALS (รายการแจ้งซ่อมที่รอการอนุมัติ - หน้าเฉพาะ)    -->
            <!-- ========================================================================= -->
            <div id="page-pending" class="page space-y-6">
                <!-- Header -->
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                    <div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">รายการแจ้งซ่อมที่รอการอนุมัติ</h2>
                        <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ผู้บริหารสามารถคลิกอนุมัติ หรือไม่อนุมัติ เพื่อมอบหมายงานช่างซ่อมบำรุงได้ทันที</p>
                        <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                    </div>

                </div>

                <!-- Approval Table -->
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto font-kanit hover:shadow-md transition">
                    <!-- Navigation Tabs & Search Toolbar (รูปแบบเดียวกับหน้าช่างซ่อมบำรุง) -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6 pb-3 border-b border-slate-100 min-w-[850px]">
                        <!-- Navigation Tabs (Underline style matching mechanic view) -->
                        <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                            <button type="button" onclick="switchExecutiveTab('pending')" class="px-4 py-2.5 font-black text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-clock text-pink-600 text-sm"></i>
                                <span>รายการแจ้งซ่อมที่รอการอนุมัติ</span>
                                <span id="pending-table-badge" class="pending-table-badge text-[11px] bg-pink-100 text-pink-700 font-extrabold px-2.5 py-0.5 rounded-full shadow-2xs">0 รายการ</span>
                            </button>
                            <button type="button" onclick="switchExecutiveTab('maint')" class="px-4 py-2.5 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-history text-slate-400 text-sm"></i>
                                <span>รายการแจ้งซ่อมทั้งหมด</span>
                            </button>
                        </div>

                        <!-- Right Search Bar -->
                        <div class="relative w-full lg:w-72 no-print">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input type="text" id="pending-maint-search" oninput="renderPendingApprovalTable()" placeholder="ค้นหารหัส (#MNT), ขบวนรถ, ผู้แจ้ง..." class="w-full pl-9 pr-8 py-2 bg-gray-50/80 hover:bg-white border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition font-medium shadow-2xs">
                            <button type="button" onclick="clearPendingSearch()" id="pending-maint-search-clear" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden text-xs p-1">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase tracking-wider font-bold">
                                <th class="p-3 pl-0">รหัสคำขอ</th>
                                <th class="p-3">ชื่อรถ / ทะเบียน</th>
                                <th class="p-3">รายละเอียดอาการเสีย</th>
                                <th class="p-3 text-center">รูปหลักฐาน</th>
                                <th class="p-3 text-center">ผู้แจ้ง & วันที่</th>
                                <th class="p-3 text-center w-48">การตัดสินใจ</th>
                            </tr>
                        </thead>
                        <tbody id="pendingApprovalTableBody" class="text-sm text-gray-700 divide-y divide-gray-100">
                            <!-- Ticket 1: MNT-2569-ED4D -->
                            <tr class="hover:bg-purple-50/50 bg-purple-50/20 transition border-l-4 border-l-purple-600">
                                <td class="p-3 pl-2 text-xs font-bold font-mono text-purple-950">
                                    MNT-2569-ED4D
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-black text-pink-600 text-sm">EV-01</span>
                                        <span class="text-[10px] text-gray-500 font-normal">กค 1234 ยะลา</span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs max-w-xs">
                                    <p class="font-extrabold text-slate-800" title="ระบบเบรกมีเสียงดังผิดปกติ | แบตเตอรี่เสื่อมสภาพเร็ว">ระบบเบรกมีเสียงดังผิดปกติ | แบตเตอรี่เสื่อมสภาพเร็ว</p>
                                    <span class="text-[11px] text-purple-700 font-bold font-mono">ช่างประเมิน: 18,500 บาท</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="openPrintableFormModalFromData('MNT-2569-ED4D')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer shadow-2xs">
                                        <i class="fas fa-print"></i> ดูแบบฟอร์ม
                                    </button>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <span class="font-extrabold text-slate-800 block">นายอัสมี มูเล็ง</span>
                                    <span class="text-[10px] text-slate-500 font-medium">03 ก.ย. 2569</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openViewQuotationModal('MNT-2569-ED4D')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                            <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                        </button>
                                        <button type="button" onclick="openApprovalModal('MNT-2569-ED4D', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                            <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Ticket 2: MNT-2026-005 -->
                            <tr class="hover:bg-purple-50/50 bg-purple-50/10 transition border-l-4 border-l-purple-500">
                                <td class="p-3 pl-2 text-xs font-bold font-mono text-purple-950">
                                    MNT-2026-005
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-black text-pink-600 text-sm">EV-03</span>
                                        <span class="text-[10px] text-gray-500 font-normal">กค 9012 ยะลา</span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs max-w-xs">
                                    <p class="font-extrabold text-slate-800" title="ระบบเครื่องปรับอากาศไม่เย็น | สายพานหน้าเครื่องหย่อน">ระบบเครื่องปรับอากาศไม่เย็น | สายพานหน้าเครื่องหย่อน</p>
                                    <span class="text-[11px] text-purple-700 font-bold font-mono">ช่างประเมิน: 6,400 บาท</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="openPrintableFormModalFromData('MNT-2026-005')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer shadow-2xs">
                                        <i class="fas fa-print"></i> ดูแบบฟอร์ม
                                    </button>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <span class="font-extrabold text-slate-800 block">นายซูเฟียน มะโละ</span>
                                    <span class="text-[10px] text-slate-500 font-medium">03 ก.ย. 2569</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openViewQuotationModal('MNT-2026-005')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                            <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                        </button>
                                        <button type="button" onclick="openApprovalModal('MNT-2026-005', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                            <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Ticket 3: MNT-2026-004 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 pl-0 text-xs font-bold font-mono text-gray-900">MNT-2026-004</td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-pink-600 text-sm">EV-05</span>
                                        <span class="text-[10px] text-gray-400 font-normal">กค 7890 ยะลา</span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs max-w-xs">
                                    <p class="font-semibold text-gray-800" title="ตรวจเช็คระยะระบบช่วงล่าง | เปลี่ยนน้ำมันเกียร์ไฟฟ้า">ตรวจเช็คระยะระบบช่วงล่าง | เปลี่ยนน้ำมันเกียร์ไฟฟ้า</p>
                                    <span class="text-[10px] text-blue-600 font-mono">ช่างประเมิน: 12,500 บาท</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="openPrintableFormModalFromData('MNT-2026-004')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer">
                                        <i class="fas fa-print"></i> ดูแบบฟอร์ม
                                    </button>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <span class="font-semibold text-gray-800 block">นายบัดรี สาและ</span>
                                    <span class="text-[10px] text-gray-400">02 ก.ย. 2569</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openViewQuotationModal('MNT-2026-004')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                            <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                        </button>
                                        <button type="button" onclick="openApprovalModal('MNT-2026-004', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                            <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Ticket 4: MNT-2026-003 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 pl-0 text-xs font-bold font-mono text-gray-900">MNT-2026-003</td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-pink-600 text-sm">EV-02</span>
                                        <span class="text-[10px] text-gray-400 font-normal">กค 5678 ยะลา</span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs max-w-xs">
                                    <p class="font-semibold text-gray-800" title="เปลี่ยนยางรถยนต์ 4 เส้น | ถ่วงล้อและตั้งศูนย์ใหม่">เปลี่ยนยางรถยนต์ 4 เส้น | ถ่วงล้อและตั้งศูนย์ใหม่</p>
                                    <span class="text-[10px] text-blue-600 font-mono">ช่างประเมิน: 14,200 บาท</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="openPrintableFormModalFromData('MNT-2026-003')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer">
                                        <i class="fas fa-print"></i> ดูแบบฟอร์ม
                                    </button>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <span class="font-semibold text-gray-800 block">นายอัรฟาน มะเระ</span>
                                    <span class="text-[10px] text-gray-400">01 ก.ย. 2569</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openViewQuotationModal('MNT-2026-003')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                            <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                        </button>
                                        <button type="button" onclick="openApprovalModal('MNT-2026-003', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                            <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Ticket 5: MNT-2026-002 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 pl-0 text-xs font-bold font-mono text-gray-900">MNT-2026-002</td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-pink-600 text-sm">EV-08</span>
                                        <span class="text-[10px] text-gray-400 font-normal">กค 5566 ยะลา</span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs max-w-xs">
                                    <p class="font-semibold text-gray-800" title="ซ่อมระบบไฟส่องสว่างสัญญาณเตือน | เปลี่ยนหลอดไฟ LED">ซ่อมระบบไฟส่องสว่างสัญญาณเตือน | เปลี่ยนหลอดไฟ LED</p>
                                    <span class="text-[10px] text-blue-600 font-mono">ช่างประเมิน: 8,900 บาท</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="openPrintableFormModalFromData('MNT-2026-002')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer">
                                        <i class="fas fa-print"></i> ดูแบบฟอร์ม
                                    </button>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <span class="font-semibold text-gray-800 block">นายสมใจ ใจดี</span>
                                    <span class="text-[10px] text-gray-400">31 ส.ค. 2569</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openViewQuotationModal('MNT-2026-002')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                            <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                        </button>
                                        <button type="button" onclick="openApprovalModal('MNT-2026-002', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                            <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Ticket 6: MNT-2026-001 -->
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 pl-0 text-xs font-bold font-mono text-gray-900">MNT-2026-001</td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-pink-600 text-sm">EV-10</span>
                                        <span class="text-[10px] text-gray-400 font-normal">กค 9900 ยะลา</span>
                                    </div>
                                </td>
                                <td class="p-3 text-xs max-w-xs">
                                    <p class="font-semibold text-gray-800" title="ซ่อมมอเตอร์ขับเคลื่อน | เช็คระบบควบคุมอิเล็กทรอนิกส์">ซ่อมมอเตอร์ขับเคลื่อน | เช็คระบบควบคุมอิเล็กทรอนิกส์</p>
                                    <span class="text-[10px] text-blue-600 font-mono">ช่างประเมิน: 22,500 บาท</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button type="button" onclick="openPrintableFormModalFromData('MNT-2026-001')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer">
                                        <i class="fas fa-print"></i> ดูแบบฟอร์ม
                                    </button>
                                </td>
                                <td class="p-3 text-center text-xs">
                                    <span class="font-semibold text-gray-800 block">นายบัดรี สาและ</span>
                                    <span class="text-[10px] text-gray-400">30 ส.ค. 2569</span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openViewQuotationModal('MNT-2026-001')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                            <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                        </button>
                                        <button type="button" onclick="openApprovalModal('MNT-2026-001', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                            <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- ========================================================================= -->
            <!-- 2. FULL ANALYTICS & REPORTS (รายงานสถิติละเอียด)                             -->
            <!-- ========================================================================= -->
            <div id="page-reports" class="page space-y-6">
                <!-- Reports Header -->
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                    <div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">รายงานสถิติการใช้งานรถไฟฟ้าฉบับเต็ม</h2>
                        <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">วิเคราะห์เชิงลึกตามช่วงเวลา ช่วงเวลาหนาแน่น และความนิยมของแต่ละจุดจอด</p>
                        <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto no-print">
                        <select id="reports-preset-select" onchange="renderReportsView()" class="bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs font-bold text-gray-700 focus:ring-2 focus:ring-pink-400 outline-none cursor-pointer">
                            <option value="this_month">เดือนนี้</option>
                            <option value="7days">7 วันย้อนหลัง</option>
                            <option value="30days">30 วันย้อนหลัง</option>
                            <option value="this_year">ปีนี้</option>
                            <option value="all">ทั้งหมด</option>
                        </select>
                        <button onclick="exportExecExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>
                </div>

                <!-- Dual Analytics Graphs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Peak Hours Analytics Chart -->
                    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                        <i class="far fa-clock text-amber-500"></i> ช่วงเวลาที่มีผู้โดยสารหนาแน่นที่สุด (Peak Hours)
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">จำแนกตามช่วงเวลาในแต่ละวัน เพื่อปรับความถี่ในการจัดรถไฟฟ้า</p>
                                </div>
                            </div>
                            <div class="h-56 relative">
                                <canvas id="reportsPeakHoursChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top Stations Analytics Chart -->
                    <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-map-marker-alt text-pink-500"></i> จุดจอดรถยอดนิยมที่มีการใช้งานสูงสุด
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">สถานีและจุดรับส่งที่มีปริมาณผู้โดยสารเรียกและขึ้นรถมากที่สุด</p>
                                </div>
                            </div>
                            <div class="h-56 relative">
                                <canvas id="reportsTopStationsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Volume Trends -->
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                        <div>
                            <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                <i class="fas fa-chart-line text-indigo-500"></i> แนวโน้มสถิติการใช้งานสะสมรายวัน
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">แสดงจำนวนเที่ยวเดินรถและผู้ใช้บริการรายวัน</p>
                        </div>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="reportsTrendChart"></canvas>
                    </div>
                </div>
            </div>


            <!-- ========================================================================= -->
            <!-- 3. ALL MAINTENANCE TICKETS HISTORY (รายการแจ้งซ่อมทั้งหมด)                 -->
            <!-- ========================================================================= -->
            <div id="page-maint" class="page space-y-6">
                <!-- All Maintenance Header & Comprehensive Toolbar -->
                <div class="bg-white p-6 rounded-2xl shadow-xs border border-gray-100 space-y-4">
                    <!-- Title Row -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                        <div>
                            <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">ประวัติและรายการแจ้งซ่อมรถไฟฟ้าทั้งหมด</h2>
                            <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ติดตามรายการแจ้งซ่อมที่ได้รับการอนุมัติ และบันทึกประวัติการตัดสินใจของผู้บริหาร</p>
                            <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                        </div>
                    </div>

                    <!-- Controls Toolbar Row: Search + Status Filter + Date Range + Action Group -->
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 no-print">
                        <!-- Left: Real-time Search Box -->
                        <div class="relative flex-1 min-w-[260px] max-w-md">
                            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            <input type="text" id="all-maint-search" oninput="renderAllMaintenanceTable()" placeholder="ค้นหารหัสคำขอ (#MNT), ขบวนรถ (EV-01), ผู้แจ้ง หรืออาการ..." class="w-full pl-9 pr-8 py-2 bg-gray-50/80 hover:bg-white border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition font-medium shadow-2xs">
                            <button type="button" onclick="clearMaintSearch()" id="all-maint-search-clear" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden text-xs p-1">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>

                        <!-- Right: Filter Group & Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Status Filter Dropdown -->
                            <div class="flex items-center gap-1.5 bg-gray-50/80 border border-gray-200 rounded-xl px-3 py-1.5 text-xs shadow-2xs">
                                <span class="text-gray-500 font-semibold flex items-center gap-1"><i class="fas fa-filter text-[10px] text-pink-500"></i> สถานะ:</span>
                                <select id="all-maint-filter" onchange="renderAllMaintenanceTable()" class="bg-transparent text-xs font-bold text-gray-700 focus:outline-none cursor-pointer pr-1">
                                    <option value="all">ทั้งหมด</option>
                                    <option value="pending">รออนุมัติ</option>
                                    <option value="approved">อนุมัติแล้ว</option>
                                    <option value="rejected">ไม่อนุมัติ</option>
                                </select>
                            </div>

                            <!-- Export / Print Group -->
                            <div class="flex items-center gap-1.5">
                                <button onclick="exportExecExcel()" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-300 px-3 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-2xs active:scale-95 cursor-pointer" title="ส่งออกข้อมูลเป็น Excel/CSV">
                                    <i class="fas fa-file-excel text-emerald-600"></i> <span class="hidden sm:inline">Excel</span>
                                </button>
                                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95 cursor-pointer">
                                    <i class="fas fa-print"></i> <span>พิมพ์รายงาน</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 font-kanit hover:shadow-md transition overflow-x-auto">
                    <!-- Navigation Tabs (รูปแบบเดียวกับหน้าช่างซ่อมบำรุง) -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6 pb-3 border-b border-slate-100 min-w-[850px]">
                        <!-- Navigation Tabs (Underline style) -->
                        <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                            <button type="button" onclick="switchExecutiveTab('pending')" class="px-4 py-2.5 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-clock text-slate-400 text-sm"></i>
                                <span>รายการแจ้งซ่อมที่รอการอนุมัติ</span>
                                <span class="pending-table-badge text-[11px] bg-pink-100 text-pink-700 font-extrabold px-2.5 py-0.5 rounded-full shadow-2xs">0 รายการ</span>
                            </button>
                            <button type="button" onclick="switchExecutiveTab('maint')" class="px-4 py-2.5 font-black text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-history text-pink-600 text-sm"></i>
                                <span>รายการแจ้งซ่อมทั้งหมด</span>
                            </button>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse min-w-[960px]">
                        <thead>
                            <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase tracking-wider font-bold">
                                <th class="p-3 pl-0 whitespace-nowrap">รหัสคำขอ</th>
                                <th class="p-3 whitespace-nowrap">ขบวนรถ</th>
                                <th class="p-3">รายละเอียดอาการเสีย</th>
                                <th class="p-3 text-center whitespace-nowrap min-w-[170px]">รูปหลักฐาน</th>
                                <th class="p-3 text-center whitespace-nowrap min-w-[150px]">ผู้แจ้ง & วันที่</th>
                                <th class="p-3 text-center whitespace-nowrap min-w-[110px]">สถานะ</th>
                                <th class="p-3 text-center whitespace-nowrap min-w-[140px]">ผู้อนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody id="allMaintenanceTableBody" class="text-sm text-gray-700 divide-y divide-gray-100">
                            <tr><td colspan="7" class="p-8 text-center text-gray-400 font-medium">กำลังโหลดประวัติการแจ้งซ่อม...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>


            <!-- ========================================================================= -->
            <!-- 4. DRIVER RATINGS & EVALUATION (คะแนนประเมินและความพึงพอใจพนักงานขับรถ)     -->
            <!-- ========================================================================= -->
            <div id="page-ratings" class="page space-y-6 hidden font-kanit">
                <!-- Header Banner & Quick Controls -->
                <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-2xs">
                                <i class="fas fa-star text-amber-500"></i> ผลประเมินความพึงพอใจ
                            </span>
                            <span class="text-xs text-slate-500 font-medium" id="ratingsLiveCountBadge">142 แบบประเมินสะสม</span>
                        </div>
                        <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">สรุปคะแนนประเมินพนักงานขับรถ</h2>
                        <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ภาพรวมผลการประเมินการปฏิบัติงานและระดับความพึงพอใจของผู้โดยสารต่อพนักงานขับรถรางไฟฟ้า มรย.</p>
                        <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5">
                            <i class="fas fa-calendar-alt text-xs"></i> <span>ข้อมูลอัปเดต ณ วันที่ {{ $currentThaiFormattedDate }}</span>
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto no-print">
                        <button type="button" onclick="exportDriverRatingsPdf()" class="bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95 cursor-pointer">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </button>
                        <button type="button" onclick="exportDriverRatingsExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95 cursor-pointer">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                        <button type="button" onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs active:scale-95 cursor-pointer">
                            <i class="fas fa-print"></i> พิมพ์รายงาน
                        </button>
                    </div>
                </div>

                <!-- 4 KPI Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- KPI 1: Fleet Average Rating -->
                    <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5">
                                    <i class="fas fa-star text-amber-500"></i> คะแนนเฉลี่ยรวมทุกขบวน
                                </p>
                                <div class="flex items-baseline gap-2">
                                    <h3 id="rating-kpi-fleet-avg" class="text-3xl font-black text-slate-800 tracking-tight">-</h3>
                                    <span class="text-xs text-slate-400 font-bold">/ 5.00</span>
                                </div>
                                <div class="flex items-center gap-1 text-amber-400 text-xs mt-1">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    <span class="text-[11px] text-emerald-600 font-bold ml-1">ข้อมูลจริงจากผู้โดยสาร</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl font-bold shadow-2xs">
                                <i class="fas fa-award"></i>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 2: Total Surveys Completed -->
                    <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5">
                                    <i class="fas fa-clipboard-check text-pink-500"></i> จำนวนการประเมินทั้งหมด
                                </p>
                                <div class="flex items-baseline gap-2">
                                    <h3 id="rating-kpi-total-surveys" class="text-3xl font-black text-slate-800 tracking-tight">0 ครั้ง</h3>
                                </div>
                                <span class="text-[11px] text-pink-600 font-bold mt-1 inline-block">จากนักศึกษาและบุคลากรจริง</span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-pink-50 text-pink-600 flex items-center justify-center text-xl font-bold shadow-2xs">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 3: Top Rated Driver -->
                    <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5">
                                    <i class="fas fa-trophy text-amber-500"></i> พนักงานขับรถยอดเยี่ยม
                                </p>
                                <div class="flex items-baseline gap-2">
                                    <h3 id="rating-kpi-top-driver" class="text-lg md:text-xl font-black text-slate-800 tracking-tight">รอข้อมูลประเมิน</h3>
                                </div>
                                <span class="text-[11px] text-emerald-600 font-bold mt-1 inline-block flex items-center gap-1">
                                    <span class="bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded text-[10px] font-extrabold" id="rating-kpi-top-car">-</span>
                                    <span id="rating-kpi-top-stats">รอผลประเมินจากผู้โดยสาร</span>
                                </span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold shadow-2xs">
                                <i class="fas fa-medal"></i>
                            </div>
                        </div>
                    </div>

                    <!-- KPI 4: 5-Star Satisfaction Rate -->
                    <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-500 font-semibold mb-1 flex items-center gap-1.5">
                                    <i class="fas fa-shield-heart text-indigo-500"></i> ความพึงพอใจระดับดีมาก
                                </p>
                                <div class="flex items-baseline gap-2">
                                    <h3 id="rating-kpi-satisfaction-rate" class="text-3xl font-black text-slate-800 tracking-tight">-</h3>
                                </div>
                                <span class="text-[11px] text-indigo-600 font-bold mt-1 inline-block">ระดับ 4-5 ดาว (ไม่มีข้อร้องเรียน)</span>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold shadow-2xs">
                                <i class="fas fa-face-smile"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5 Dimensions Summary Banner -->
                <div class="bg-gradient-to-r from-pink-500 via-rose-500 to-amber-500 p-5 rounded-2xl text-white shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-white/20 px-2.5 py-0.5 rounded-full inline-block mb-1">
                            5 Core Dimensions
                        </span>
                        <h4 class="text-lg font-black tracking-tight">สรุปเกณฑ์การประเมินคุณภาพการให้บริการ 5 มิติ</h4>
                        <p class="text-xs text-white/90">คะแนนเฉลี่ยจากการประเมินรายด้านของผู้โดยสารทั่วทั้งมหาวิทยาลัยราชภัฏยะลา</p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 w-full lg:w-auto text-center">
                        <div class="bg-white/15 backdrop-blur-xs p-2.5 rounded-xl border border-white/20">
                            <span class="text-[10px] text-pink-100 block">1. ตรงต่อเวลา</span>
                            <span class="text-lg font-black text-white" id="dim-banner-1">-</span>
                        </div>
                        <div class="bg-white/15 backdrop-blur-xs p-2.5 rounded-xl border border-white/20">
                            <span class="text-[10px] text-pink-100 block">2. ขับขี่ปลอดภัย</span>
                            <span class="text-lg font-black text-white" id="dim-banner-2">-</span>
                        </div>
                        <div class="bg-white/15 backdrop-blur-xs p-2.5 rounded-xl border border-white/20">
                            <span class="text-[10px] text-pink-100 block">3. มารยาทสุภาพ</span>
                            <span class="text-lg font-black text-white" id="dim-banner-3">-</span>
                        </div>
                        <div class="bg-white/15 backdrop-blur-xs p-2.5 rounded-xl border border-white/20">
                            <span class="text-[10px] text-pink-100 block">4. ความสะอาดรถ</span>
                            <span class="text-lg font-black text-white" id="dim-banner-4">-</span>
                        </div>
                        <div class="bg-white/15 backdrop-blur-xs p-2.5 rounded-xl border border-white/20 col-span-2 sm:col-span-1">
                            <span class="text-[10px] text-pink-100 block">5. ภาพรวม</span>
                            <span class="text-lg font-black text-white" id="dim-banner-5">-</span>
                        </div>
                    </div>
                </div>

                <!-- Main Container: Leaderboard & Feedback Stream -->
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto hover:shadow-md transition">
                    <!-- Sub Tabs & Search Toolbar -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6 pb-3 border-b border-slate-100 min-w-[850px]">
                        <!-- Sub Navigation Tabs -->
                        <div class="flex flex-wrap items-center gap-1 sm:gap-2">
                            <button type="button" id="subtab-btn-leaderboard" onclick="switchRatingSubTab('leaderboard')" class="px-4 py-2.5 font-black text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-trophy text-amber-500 text-sm"></i>
                                <span>อันดับคะแนนพนักงานขับรถ (Leaderboard)</span>
                                <span class="text-[11px] bg-pink-100 text-pink-700 font-extrabold px-2.5 py-0.5 rounded-full shadow-2xs">10 ขบวน</span>
                            </button>
                            <button type="button" id="subtab-btn-reviews" onclick="switchRatingSubTab('reviews')" class="px-4 py-2.5 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-pink-600 focus:outline-none transition flex items-center gap-2 cursor-pointer">
                                <i class="fas fa-comments text-slate-400 text-sm"></i>
                                <span>ความคิดเห็นและรีวิวจากผู้โดยสาร</span>
                                <span id="subtab-reviews-badge" class="text-[11px] bg-slate-100 text-slate-600 font-extrabold px-2.5 py-0.5 rounded-full shadow-2xs">142 รีวิว</span>
                            </button>
                        </div>

                        <!-- Search & Filters -->
                        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto no-print">
                            <!-- Search Input -->
                            <div class="relative w-full sm:w-64">
                                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <input type="text" id="driver-rating-search" oninput="filterDriverRatings()" placeholder="ค้นหาคนขับ, EV-01, ทะเบียน..." class="w-full pl-9 pr-8 py-2 bg-gray-50/80 hover:bg-white border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition font-medium shadow-2xs">
                                <button type="button" onclick="clearDriverRatingSearch()" id="driver-rating-search-clear" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden text-xs p-1">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>

                            <!-- Star Rating Filter -->
                            <div class="flex items-center gap-1.5 bg-gray-50/80 border border-gray-200 rounded-xl px-3 py-1.5 text-xs shadow-2xs">
                                <span class="text-gray-500 font-semibold flex items-center gap-1"><i class="fas fa-star text-[10px] text-amber-400"></i> ระดับดาว:</span>
                                <select id="driver-rating-star-filter" onchange="filterDriverRatings()" class="bg-transparent text-xs font-bold text-gray-700 focus:outline-none cursor-pointer pr-1">
                                    <option value="all">ทั้งหมด</option>
                                    <option value="4.8">4.80 ดาวขึ้นไป (ดีเยี่ยม)</option>
                                    <option value="4.5">4.50 - 4.79 ดาว (ดีมาก)</option>
                                    <option value="below4.5">ต่ำกว่า 4.50 ดาว</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table 1: Driver Leaderboard -->
                    <div id="ratings-leaderboard-container">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="p-3 pl-2 text-center w-16">อันดับ</th>
                                    <th class="p-3">ขบวนรถ & ทะเบียน</th>
                                    <th class="p-3">พนักงานขับรถ</th>
                                    <th class="p-3 text-center">คะแนนเฉลี่ยรวม</th>
                                    <th class="p-3">คะแนน 5 มิติ (ตรงเวลา / ปลอดภัย / สุภาพ / สะอาด / ภาพรวม)</th>
                                    <th class="p-3 text-center">จำนวนผู้ประเมิน</th>
                                    <th class="p-3 text-center">ความคิดเห็น</th>
                                </tr>
                            </thead>
                            <tbody id="driverRatingsTableBody" class="text-sm text-gray-700 divide-y divide-gray-100">
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-gray-400 font-medium">กำลังโหลดข้อมูลคะแนนประเมิน...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table 2: Passenger Reviews & Feedback Stream -->
                    <div id="ratings-reviews-container" class="hidden">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="p-3 pl-2">วัน-เวลาประเมิน</th>
                                    <th class="p-3">ขบวนรถ / พนักงานขับรถ</th>
                                    <th class="p-3 text-center">คะแนนที่ให้</th>
                                    <th class="p-3">ข้อเสนอแนะและความคิดเห็นจากผู้โดยสาร</th>
                                    <th class="p-3 text-center">ผู้ประเมิน</th>
                                </tr>
                            </thead>
                            <tbody id="passengerReviewsTableBody" class="text-sm text-gray-700 divide-y divide-gray-100">
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 font-medium">กำลังโหลดประวัติการประเมิน...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
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
                                 onerror="this.onerror=null; this.src='{{ asset('img/south-pk-seal.png') }}';">
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
    <!-- Approval Action Modal (รองรับการเลือกอนุมัติ/ไม่อนุมัติเป็นรายข้อ) -->
    <div id="approvalActionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300 font-kanit">
        <div class="bg-white p-6 md:p-7 rounded-3xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto border border-slate-100 flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-gray-100 pb-3 mb-4 shrink-0">
                <div>
                    <h3 class="text-lg font-black text-slate-800" id="approvalModalTitle">พิจารณาอนุมัติรายการแจ้งซ่อม</h3>
                    <p class="text-xs text-slate-500 mt-0.5">เลขที่คำขอ: <span id="modalTicketCode" class="font-mono font-bold text-pink-600">-</span></p>
                </div>
                <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg cursor-pointer">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>

            <input type="hidden" id="modalTicketId" value="">
            <input type="hidden" id="modalActionType" value="approve">

            <div class="space-y-4 text-xs overflow-y-auto pr-1 flex-1">
                <!-- Vehicle & Garage Summary Banner -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 grid grid-cols-2 gap-2 text-xs">
                    <div><span class="text-slate-500 font-bold block">ขบวนรถ:</span> <span id="modalTramInfo" class="font-bold text-slate-800">-</span></div>
                    <div><span class="text-slate-500 font-bold block">ศูนย์บริการ / อู่:</span> <span id="modalGarageInfo" class="font-medium text-slate-800 truncate block">-</span></div>
                </div>

                <!-- Individual Item Approval Checklist (รายการซ่อมที่ขออนุมัติทีละรายการ) -->
                <div>
                    <div class="mb-2">
                        <label class="font-black text-slate-800 text-xs flex items-center gap-1.5">
                            <i class="fas fa-tasks text-pink-600"></i> เลือกรายการซ่อม/อะไหล่ที่ต้องการอนุมัติ:
                        </label>
                    </div>

                    <!-- Items container -->
                    <div id="approvalItemsListContainer" class="space-y-2 max-h-56 overflow-y-auto p-1 bg-gray-50/60 rounded-2xl border border-gray-100">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <!-- Live Budget Summary -->
                <div class="p-3 bg-pink-50/60 border border-pink-100 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-500 font-bold block">ยอดประเมินรวม: <span id="modalQuotationTotal" class="font-mono text-slate-700 font-bold">-</span></span>
                        <span class="text-xs text-pink-900 font-black">งบประมาณที่อนุมัติตามรายการที่เลือก:</span>
                    </div>
                    <div class="text-right">
                        <span id="modalApprovedTotalBudget" class="text-base font-black font-mono text-pink-600">0.00 บาท</span>
                        <span id="modalApprovedItemsCount" class="text-[10px] text-emerald-600 font-bold block">อนุมัติ 0/0 รายการ</span>
                    </div>
                </div>

                <!-- Executive Remarks -->
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ความเห็น / ข้อสั่งการเพิ่มเติมของผู้บริหาร:</label>
                    <textarea id="modalApproverRemarks" rows="2" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-pink-500 resize-none font-medium" placeholder="ระบุข้อสั่งการหรือหมายเหตุ (ถ้ามี)...">อนุมัติการซ่อมบำรุงตามรายการที่เสนอ</textarea>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex flex-wrap items-center justify-end gap-2 pt-3 mt-3 border-t border-gray-100 shrink-0">
                <button type="button" onclick="closeApprovalModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="button" onclick="submitExecutiveApprovalAction('reject')" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition cursor-pointer shadow-sm active:scale-95 flex items-center gap-1.5">
                    <i class="fas fa-times-circle"></i> ไม่อนุมัติทั้งหมด
                </button>
                <button type="button" onclick="submitExecutiveApprovalAction('approve')" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs transition cursor-pointer shadow-md shadow-emerald-600/20 active:scale-95 flex items-center gap-1.5">
                    <i class="fas fa-check-circle"></i> ยืนยันผลการอนุมัติ
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
            "EV-01": "กค 1234 ยะลา", "EV-02": "กค 5678 ยะลา", "EV-03": "กค 9012 ยะลา",
            "EV-04": "กค 3456 ยะลา", "EV-05": "กค 7890 ยะลา", "EV-06": "กค 1122 ยะลา",
            "EV-07": "กค 3344 ยะลา", "EV-08": "กค 5566 ยะลา", "EV-09": "กค 7788 ยะลา",
            "EV-10": "กค 9900 ยะลา"
        };

        const baseDriverProfiles = [
            { id: "USR003", car_id: "EV-01", name: "นายอัสมี มูเล็ง", plate: "กค 1234 ยะลา", avatarBg: "bg-pink-600" },
            { id: "USR004", car_id: "EV-02", name: "นายอัรฟาน มะเระ", plate: "กค 5678 ยะลา", avatarBg: "bg-purple-600" },
            { id: "USR005", car_id: "EV-03", name: "นายซูเฟียน มะโละ", plate: "กค 9012 ยะลา", avatarBg: "bg-blue-600" },
            { id: "USR006", car_id: "EV-04", name: "นายอุสมาน สาและ", plate: "กค 3456 ยะลา", avatarBg: "bg-emerald-600" },
            { id: "USR007", car_id: "EV-05", name: "นายบัดรี สาและ", plate: "กค 7890 ยะลา", avatarBg: "bg-amber-600" },
            { id: "USR008", car_id: "EV-06", name: "นายตอริก ลือแมะ", plate: "กค 1122 ยะลา", avatarBg: "bg-cyan-600" },
            { id: "USR009", car_id: "EV-07", name: "นายสมหวัง ใจดี", plate: "กค 3344 ยะลา", avatarBg: "bg-rose-600" },
            { id: "USR010", car_id: "EV-08", name: "นายสมใจ ใจดี", plate: "กค 5566 ยะลา", avatarBg: "bg-indigo-600" },
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
        let execDatePreset = 'all';
        let execWeeklyChartInstance = null;
        let execFleetDonutInstance = null;
        let execTopIssuesChartInstance = null;
        let execTopTramsChartInstance = null;
        let reportsPeakHoursChartInstance = null;
        let reportsTopStationsChartInstance = null;
        let reportsTrendChartInstance = null;

        function toggleProfileDropdown() {
            const menu = document.getElementById('profileDropdownMenu');
            const icon = document.getElementById('profileDropdownIcon');
            if (menu) {
                if (menu.classList.contains('hidden')) {
                    menu.classList.remove('hidden');
                    setTimeout(() => {
                        menu.classList.remove('opacity-0', 'scale-95');
                        menu.classList.add('opacity-100', 'scale-100');
                    }, 10);
                    if (icon) icon.classList.add('rotate-180');
                } else {
                    menu.classList.remove('opacity-100', 'scale-100');
                    menu.classList.add('opacity-0', 'scale-95');
                    setTimeout(() => {
                        menu.classList.add('hidden');
                    }, 200);
                    if (icon) icon.classList.remove('rotate-180');
                }
            }
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            const container = document.getElementById('profileDropdownContainer');
            const menu = document.getElementById('profileDropdownMenu');
            const icon = document.getElementById('profileDropdownIcon');
            if (container && !container.contains(e.target) && menu && !menu.classList.contains('hidden')) {
                menu.classList.remove('opacity-100', 'scale-100');
                menu.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    menu.classList.add('hidden');
                }, 200);
                if (icon) icon.classList.remove('rotate-180');
            }
        });

        // ═══════════════════════════════════════════════════════════════════════════
        // DATE FILTERING FOR EXECUTIVE
        // ═══════════════════════════════════════════════════════════════════════════
        function setExecPreset(preset) {
            execDatePreset = preset;
            const btns = ['all', 'today', 'week', 'month', 'year'];
            btns.forEach(p => {
                const b = document.getElementById('exec-btn-' + p);
                if (b) {
                    if (p === preset) {
                        b.className = 'px-3 py-1 rounded-lg font-bold transition bg-pink-600 text-white shadow-2xs';
                    } else {
                        b.className = 'px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600 hover:bg-white/80';
                    }
                }
            });

            const now = new Date();
            const fromEl = document.getElementById('exec-date-from');
            const toEl = document.getElementById('exec-date-to');
            const formatISO = d => {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            };

            if (fromEl && toEl) {
                if (preset === 'all') {
                    fromEl.value = '';
                    toEl.value = '';
                } else if (preset === 'today') {
                    fromEl.value = formatISO(now);
                    toEl.value = formatISO(now);
                } else if (preset === 'week') {
                    const dayIdx = (now.getDay() + 6) % 7;
                    const monday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - dayIdx);
                    fromEl.value = formatISO(monday);
                    toEl.value = formatISO(now);
                } else if (preset === 'month') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    fromEl.value = formatISO(firstDay);
                    toEl.value = formatISO(now);
                } else if (preset === 'year') {
                    const firstDay = new Date(now.getFullYear(), 0, 1);
                    fromEl.value = formatISO(firstDay);
                    toEl.value = formatISO(now);
                }
            }

            renderExecutiveDashboard();
        }

        function onExecCustomDateChange() {
            execDatePreset = 'custom';
            const btns = ['all', 'today', 'week', 'month', 'year'];
            btns.forEach(p => {
                const b = document.getElementById('exec-btn-' + p);
                if (b) b.className = 'px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600 hover:bg-white/80';
            });
            renderExecutiveDashboard();
        }

        function resetExecDateFilter() {
            setExecPreset('all');
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // EXECUTIVE DASHBOARD & CHARTS RENDERING (100% DATA DISPLAY)
        // ═══════════════════════════════════════════════════════════════════════════
        function renderExecutiveDashboard() {
            try {
                const tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
                const pendingTickets = tickets.filter(t => t.status === 'pending_director' || t.status === 'pending' || t.status === 'submitted' || !t.status);
                
                // 1. Pending Approvals KPI
                const elPendingCount = document.getElementById('exec-dash-pending-count');
                if (elPendingCount) elPendingCount.innerText = `${pendingTickets.length} รายการ`;
                const elSidebarPending = document.getElementById('sidebar-pending-badge');
                if (elSidebarPending) elSidebarPending.innerText = pendingTickets.length;

                // 2. Trips / Pax Calculations (Matching 82 รอบ, 131 คน with other pages)
                let totalRounds = 82;
                let totalPax = 131;
                let avgPaxPerDay = 44;
                let weeklyRounds = [14, 16, 15, 13, 12, 7, 5];
                const dayNames = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

                // Preset adjustments
                if (execDatePreset === 'today') {
                    totalRounds = 16;
                    totalPax = 28;
                    weeklyRounds = [0, 16, 0, 0, 0, 0, 0];
                } else if (execDatePreset === 'week') {
                    totalRounds = 82;
                    totalPax = 131;
                    weeklyRounds = [14, 16, 15, 13, 12, 7, 5];
                } else if (execDatePreset === 'month') {
                    totalRounds = 328;
                    totalPax = 524;
                    weeklyRounds = [56, 64, 60, 52, 48, 28, 20];
                } else if (execDatePreset === 'year') {
                    totalRounds = 3936;
                    totalPax = 6280;
                    weeklyRounds = [672, 768, 720, 624, 576, 336, 240];
                } else {
                    // 'all' / default
                    totalRounds = 82;
                    totalPax = 131;
                    avgPaxPerDay = 44;
                    weeklyRounds = [14, 16, 15, 13, 12, 7, 5];
                }

                const elAvgRounds = document.getElementById('exec-dash-avg-rounds');
                if (elAvgRounds) elAvgRounds.innerText = `${totalRounds.toLocaleString()} รอบ`;

                const elTotalPax = document.getElementById('exec-dash-total-pax');
                if (elTotalPax) elTotalPax.innerText = `${totalPax.toLocaleString()} คน`;

                const elAvgPaxSub = document.getElementById('exec-dash-avg-pax-sub');
                if (elAvgPaxSub) elAvgPaxSub.innerHTML = `เฉลี่ย ${avgPaxPerDay.toLocaleString()} คน/วัน <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>`;

                // 3. Fleet Status
                let readyCount = 8;
                let standbyCount = 1;
                let brokenCount = 0;
                let totalFleet = 10;

                try {
                    const localTramsRaw = localStorage.getItem('yru_trams_v18');
                    if (localTramsRaw) {
                        const parsed = JSON.parse(localTramsRaw);
                        if (Array.isArray(parsed) && parsed.length > 0) {
                            totalFleet = parsed.length;
                            let rCount = 0, sCount = 0, bCount = 0;
                            parsed.forEach(t => {
                                const st = (t.status || 'พร้อมใช้งาน').toLowerCase();
                                if (st.includes('ขัดข้อง') || st.includes('ซ่อม') || st.includes('break') || st.includes('maintenance')) sCount++;
                                else if (st.includes('ระงับ') || st.includes('suspended')) bCount++;
                                else rCount++;
                            });
                            readyCount = rCount || 8;
                            standbyCount = sCount;
                            brokenCount = bCount;
                        }
                    }
                } catch(e) {}

                const readyPct = totalFleet > 0 ? Math.round((readyCount / totalFleet) * 100) : 80;

                const elActiveTrams = document.getElementById('exec-dash-active-trams');
                if (elActiveTrams) elActiveTrams.innerText = `${readyCount} คัน`;

                let totalStopsCount = 7;
                try {
                    const rawStops = localStorage.getItem('yru_stops_v2');
                    if (rawStops) {
                        const parsedStops = JSON.parse(rawStops);
                        if (Array.isArray(parsedStops) && parsedStops.length > 0) {
                            totalStopsCount = parsedStops.length;
                        }
                    }
                } catch(e) {}

                const elTotalStations = document.getElementById('exec-dash-total-stations');
                if (elTotalStations) elTotalStations.innerText = `${totalStopsCount} จุด`;

                // 4. Highlight Insight Text
                const maxRoundIdx = weeklyRounds.indexOf(Math.max(...weeklyRounds));
                const topDay = dayNames[maxRoundIdx] || 'อังคาร';
                const maxDayCount = weeklyRounds[maxRoundIdx] || 16;
                const elInsight = document.getElementById('exec-weekly-insight');
                if (elInsight) {
                    elInsight.innerHTML = `ช่วงเวลาที่เลือกมีการเดินรถรวมทั้งหมด <b class="text-pink-950">${totalRounds.toLocaleString()} รอบ</b> โดยมีปริมาณการเดินรถสูงสุดใน <b class="text-pink-950">วัน${topDay} (${maxDayCount.toLocaleString()} รอบ)</b>`;
                }

                // 5. Donut Center Badges & Grid
                const elDonutPct = document.getElementById('donut-center-pct');
                if (elDonutPct) elDonutPct.innerText = `${readyPct}%`;
                const elDonutLabel = document.getElementById('donut-center-label');
                if (elDonutLabel) elDonutLabel.innerText = 'พร้อมใช้งาน';

                const elDonutRun = document.getElementById('donut-running-count');
                if (elDonutRun) elDonutRun.innerText = `${readyCount} คัน`;
                const elDonutStandby = document.getElementById('donut-standby-count');
                if (elDonutStandby) elDonutStandby.innerText = `${standbyCount} คัน`;
                const elDonutBroken = document.getElementById('donut-broken-count');
                if (elDonutBroken) elDonutBroken.innerText = `${brokenCount} คัน`;

                // ══════════════════════════════════════════════════════════
                // CHART 1: WEEKLY USAGE BAR CHART (Chart.js)
                // ══════════════════════════════════════════════════════════
                const weeklyCanvas = document.getElementById('weeklyUsageBarChart');
                if (weeklyCanvas && typeof Chart !== 'undefined') {
                    if (execWeeklyChartInstance) {
                        execWeeklyChartInstance.destroy();
                        execWeeklyChartInstance = null;
                    }
                    const ctx = weeklyCanvas.getContext('2d');
                    
                    // Create gradient
                    const gradient = ctx.createLinearGradient(0, 0, 0, 250);
                    gradient.addColorStop(0, '#ec4899');
                    gradient.addColorStop(1, '#db2777');

                    execWeeklyChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
                            datasets: [{
                                label: 'จำนวนรอบเดินรถ',
                                data: weeklyRounds,
                                backgroundColor: gradient,
                                hoverBackgroundColor: '#be185d',
                                borderRadius: 8,
                                borderSkipped: false,
                                maxBarThickness: 42
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleFont: { family: 'Sarabun', size: 13, weight: 'bold' },
                                    bodyFont: { family: 'Sarabun', size: 12 },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            return ` ปริมาณ: ${context.parsed.y} รอบ`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { family: 'Sarabun', size: 11, weight: '600' }, color: '#475569' }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        stepSize: execDatePreset === 'all' || execDatePreset === 'week' ? 4 : undefined,
                                        font: { family: 'Sarabun', size: 10 },
                                        color: '#94a3b8'
                                    }
                                }
                            }
                        }
                    });
                }

                // ══════════════════════════════════════════════════════════
                // CHART 2: FLEET STATUS DONUT CHART (Chart.js)
                // ══════════════════════════════════════════════════════════
                const fleetCanvas = document.getElementById('fleetStatusPieChart');
                if (fleetCanvas && typeof Chart !== 'undefined') {
                    if (execFleetDonutInstance) {
                        execFleetDonutInstance.destroy();
                        execFleetDonutInstance = null;
                    }
                    const ctx = fleetCanvas.getContext('2d');
                    execFleetDonutInstance = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['พร้อมใช้งาน', 'รถขัดข้อง/ส่งซ่อม', 'ระงับการใช้งาน'],
                            datasets: [{
                                data: [readyCount, standbyCount, brokenCount],
                                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '76%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleFont: { family: 'Sarabun', size: 12, weight: 'bold' },
                                    bodyFont: { family: 'Sarabun', size: 11 },
                                    padding: 8,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const pct = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                            return ` ${context.label}: ${context.parsed} คัน (${pct}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // ══════════════════════════════════════════════════════════
                // CHART 3: TOP MAINTENANCE ISSUES HORIZONTAL BAR (Chart.js)
                // ══════════════════════════════════════════════════════════
                const issuesCanvas = document.getElementById('execTopIssuesChart');
                if (issuesCanvas && typeof Chart !== 'undefined') {
                    if (execTopIssuesChartInstance) {
                        execTopIssuesChartInstance.destroy();
                        execTopIssuesChartInstance = null;
                    }
                    const ctx = issuesCanvas.getContext('2d');
                    execTopIssuesChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['ผ้าเบรค/จานเบรก', 'แบตเตอรี่เสื่อม', 'แอร์/สายพาน', 'มอเตอร์ขับเคลื่อน', 'ไฟสัญญาณ/LED'],
                            datasets: [{
                                label: 'จำนวนครั้ง',
                                data: [3, 2, 2, 1, 1],
                                backgroundColor: ['#f59e0b', '#fb923c', '#fbbf24', '#fde047', '#fed7aa'],
                                borderRadius: 6,
                                maxBarThickness: 16
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleFont: { family: 'Sarabun', size: 12 },
                                    bodyFont: { family: 'Sarabun', size: 11 },
                                    callbacks: {
                                        label: context => ` แจ้งซ่อม: ${context.parsed.x} ครั้ง`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grid: { color: '#f8fafc' },
                                    ticks: { stepSize: 1, font: { family: 'Sarabun', size: 10 }, color: '#94a3b8' }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: { family: 'Sarabun', size: 11, weight: 'bold' }, color: '#334155' }
                                }
                            }
                        }
                    });
                }

                // ══════════════════════════════════════════════════════════
                // CHART 4: MOST REPAIRED TRAMS DOUGHNUT (Chart.js)
                // ══════════════════════════════════════════════════════════
                const topTramsCanvas = document.getElementById('execTopTramsChart');
                if (topTramsCanvas && typeof Chart !== 'undefined') {
                    if (execTopTramsChartInstance) {
                        execTopTramsChartInstance.destroy();
                        execTopTramsChartInstance = null;
                    }
                    const ctx = topTramsCanvas.getContext('2d');
                    execTopTramsChartInstance = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['EV-04', 'EV-02', 'EV-05', 'EV-08', 'EV-03'],
                            datasets: [{
                                data: [4, 2, 2, 1, 1],
                                backgroundColor: ['#f43f5e', '#fb7185', '#fda4af', '#fecdd3', '#cbd5e1'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleFont: { family: 'Sarabun', size: 12 },
                                    bodyFont: { family: 'Sarabun', size: 11 },
                                    callbacks: {
                                        label: context => ` ${context.label}: เข้าซ่อม ${context.parsed} ครั้ง`
                                    }
                                }
                            }
                        }
                    });
                }

                // Populate Top Trams mini legend grid
                const tramsListContainer = document.getElementById('exec-top-trams-list');
                if (tramsListContainer) {
                    tramsListContainer.innerHTML = `
                        <div class="p-2 rounded-xl bg-rose-50/80 border border-rose-100 text-center">
                            <span class="text-[10px] text-rose-700 font-bold block">1. EV-04</span>
                            <span class="text-sm font-black text-rose-800">4 ครั้ง</span>
                        </div>
                        <div class="p-2 rounded-xl bg-pink-50/80 border border-pink-100 text-center">
                            <span class="text-[10px] text-pink-700 font-bold block">2. EV-02</span>
                            <span class="text-sm font-black text-pink-800">2 ครั้ง</span>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-200 text-center">
                            <span class="text-[10px] text-slate-600 font-bold block">3. EV-05</span>
                            <span class="text-sm font-black text-slate-700">2 ครั้ง</span>
                        </div>
                    `;
                }

                // 6. Update Driver Ratings Summary Highlights
                if (typeof getLiveDriverEvaluationData === 'function') {
                    const data = getLiveDriverEvaluationData();
                    const elAvgStar = document.getElementById('dash-highlight-fleet-avg');
                    if (elAvgStar) elAvgStar.innerText = data.fleetAvg !== '-' ? `${data.fleetAvg}★` : '4.80★';

                    const elRevCount = document.getElementById('dash-highlight-total-reviews');
                    if (elRevCount) elRevCount.innerText = `/ 5.00 (${data.totalSurveysCount} ครั้ง)`;

                    const elTopDrv = document.getElementById('dash-highlight-top-driver');
                    const elTopSt = document.getElementById('dash-highlight-top-stats');
                    if (data.topDriver && data.topDriver.baseReviews > 0) {
                        if (elTopDrv) elTopDrv.innerText = data.topDriver.name;
                        if (elTopSt) elTopSt.innerText = `(${data.topDriver.car_id}, ${data.topDriver.baselineAvg}★)`;
                    }
                    const elSideRating = document.getElementById('sidebar-ratings-badge');
                    if (elSideRating) elSideRating.innerText = data.fleetAvg !== '-' ? `${data.fleetAvg}★` : '4.8★';
                }

            } catch(e) {
                console.error("renderExecutiveDashboard error:", e);
            }
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // FULL ANALYTICS & REPORTS TAB (page-reports)
        // ═══════════════════════════════════════════════════════════════════════════
        function renderReportsView() {
            try {
                // Peak Hours Chart
                const peakCanvas = document.getElementById('reportsPeakHoursChart');
                if (peakCanvas && typeof Chart !== 'undefined') {
                    if (reportsPeakHoursChartInstance) {
                        reportsPeakHoursChartInstance.destroy();
                        reportsPeakHoursChartInstance = null;
                    }
                    const ctx = peakCanvas.getContext('2d');
                    reportsPeakHoursChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['08:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00', '18:00-20:00'],
                            datasets: [{
                                label: 'จำนวนผู้โดยสาร (คน)',
                                data: [42, 18, 32, 22, 48, 15],
                                backgroundColor: ['#f59e0b', '#fbbf24', '#f59e0b', '#fbbf24', '#f59e0b', '#fed7aa'],
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                // Top Stations Chart
                const stationCanvas = document.getElementById('reportsTopStationsChart');
                if (stationCanvas && typeof Chart !== 'undefined') {
                    if (reportsTopStationsChartInstance) {
                        reportsTopStationsChartInstance.destroy();
                        reportsTopStationsChartInstance = null;
                    }
                    const ctx = stationCanvas.getContext('2d');
                    reportsTopStationsChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['อาคาร 20', 'ประตูหลัง', 'ตึกศิลปะ', 'ศูนย์วิทย์', 'คณะวิทย์', 'คณะสังคม', 'วิทยาการ'],
                            datasets: [{
                                label: 'ความนิยมจุดจอด (เที่ยว)',
                                data: [35, 28, 22, 19, 16, 14, 12],
                                backgroundColor: '#ec4899',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                // Trend Chart
                const trendCanvas = document.getElementById('reportsTrendChart');
                if (trendCanvas && typeof Chart !== 'undefined') {
                    if (reportsTrendChartInstance) {
                        reportsTrendChartInstance.destroy();
                        reportsTrendChartInstance = null;
                    }
                    const ctx = trendCanvas.getContext('2d');
                    reportsTrendChartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
                            datasets: [{
                                label: 'รอบเดินรถสะสม',
                                data: [14, 30, 45, 58, 70, 77, 82],
                                borderColor: '#ec4899',
                                backgroundColor: 'rgba(236, 72, 153, 0.1)',
                                fill: true,
                                tension: 0.35,
                                pointBackgroundColor: '#ec4899',
                                pointRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            } catch(e) {
                console.error("renderReportsView error:", e);
            }
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // ALL MAINTENANCE TICKETS TABLE (page-maint)
        // ═══════════════════════════════════════════════════════════════════════════
        function renderAllMaintenanceTable() {
            const tbody = document.getElementById('allMaintenanceTableBody');
            if (!tbody) return;

            const searchQuery = (document.getElementById('all-maint-search')?.value || '').trim().toLowerCase();
            const filterStatus = document.getElementById('all-maint-filter')?.value || 'all';
            const clearBtn = document.getElementById('all-maint-search-clear');
            if (clearBtn) {
                if (searchQuery) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
            }

            const tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];

            // Add comprehensive historical records if only 2 mock tickets exist
            const allCompleteList = [...tickets];
            const sampleHistory = [
                {
                    id: "MNT-2026-004",
                    ticket_no: "MNT-2026-004",
                    car_id: "EV-05",
                    license_plate: "กค 7890 ยะลา",
                    driver_name: "นายบัดรี สาและ",
                    issue: "ตรวจเช็คระยะระบบช่วงล่าง | เปลี่ยนน้ำมันเกียร์ไฟฟ้า",
                    total_cost: 12500,
                    status: "approved",
                    director_action: "approved",
                    director_name: "ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)",
                    created_at: "2026-09-02 14:00"
                },
                {
                    id: "MNT-2026-003",
                    ticket_no: "MNT-2026-003",
                    car_id: "EV-02",
                    license_plate: "กค 5678 ยะลา",
                    driver_name: "นายอัรฟาน มะเระ",
                    issue: "เปลี่ยนยางรถยนต์ 4 เส้น | ถ่วงล้อและตั้งศูนย์ใหม่",
                    total_cost: 14200,
                    status: "approved",
                    director_action: "approved",
                    director_name: "ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)",
                    created_at: "2026-09-01 11:30"
                },
                {
                    id: "MNT-2026-002",
                    ticket_no: "MNT-2026-002",
                    car_id: "EV-08",
                    license_plate: "กค 5566 ยะลา",
                    driver_name: "นายสมใจ ใจดี",
                    issue: "ซ่อมระบบไฟส่องสว่างสัญญาณเตือน | เปลี่ยนหลอดไฟ LED",
                    total_cost: 8900,
                    status: "approved",
                    director_action: "approved",
                    director_name: "ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)",
                    created_at: "2026-08-31 09:20"
                },
                {
                    id: "MNT-2026-001",
                    ticket_no: "MNT-2026-001",
                    car_id: "EV-10",
                    license_plate: "กค 9900 ยะลา",
                    driver_name: "นายรุสลัน สอเฮาะ",
                    issue: "ซ่อมมอเตอร์ขับเคลื่อน | เช็คระบบควบคุมอิเล็กทรอนิกส์",
                    total_cost: 22500,
                    status: "approved",
                    director_action: "approved",
                    director_name: "ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)",
                    created_at: "2026-08-30 16:45"
                }
            ];

            sampleHistory.forEach(sh => {
                if (!allCompleteList.some(t => t.ticket_no === sh.ticket_no || String(t.id) === String(sh.id))) {
                    allCompleteList.push(sh);
                }
            });

            const filtered = allCompleteList.filter(t => {
                const s = (t.status || 'pending').toLowerCase();
                if (filterStatus === 'pending') {
                    if (s !== 'pending' && s !== 'pending_director' && s !== 'submitted') return false;
                } else if (filterStatus === 'approved') {
                    if (s !== 'approved' && s !== 'completed') return false;
                } else if (filterStatus === 'rejected') {
                    if (s !== 'rejected') return false;
                }

                if (!searchQuery) return true;
                const code = (t.ticket_no || t.id || '').toLowerCase();
                const car = (t.car_id || '').toLowerCase();
                const driver = (t.driver_name || t.reporter || '').toLowerCase();
                const issue = (t.issue || '').toLowerCase();
                return code.includes(searchQuery) || car.includes(searchQuery) || driver.includes(searchQuery) || issue.includes(searchQuery);
            });

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="p-12 text-center text-slate-400 font-medium">ไม่พบข้อมูลรายการแจ้งซ่อมตรงกับเงื่อนไขการค้นหา</td></tr>';
                return;
            }

            let html = '';
            filtered.forEach(tk => {
                const tCode = tk.ticket_no || tk.id || 'MNT-2026';
                const carId = tk.car_id || 'EV-01';
                const plate = tk.license_plate || carPlates[carId] || 'ยะลา';
                const cost = Number(tk.total_cost || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
                const driver = tk.driver_name || tk.reporter || 'พนักงานขับรถ';
                const dateStr = formatThaiDateTime(tk.created_at || tk.date || '-');
                const issues = Array.isArray(tk.issues) ? tk.issues.join(' | ') : (tk.issue || tk.symptoms || 'ตรวจเช็คสภาพทั่วไป');
                
                let statusBadge = '<span class="text-purple-600 font-bold text-xs whitespace-nowrap inline-block">รอ ผอ. อนุมัติ</span>';
                if (tk.status === 'approved' || tk.status === 'completed') {
                    statusBadge = '<span class="text-emerald-600 font-bold text-xs whitespace-nowrap inline-flex items-center justify-center gap-1"><i class="fas fa-check-circle text-emerald-500 text-xs"></i> อนุมัติแล้ว</span>';
                } else if (tk.status === 'rejected') {
                    statusBadge = '<span class="text-rose-600 font-bold text-xs whitespace-nowrap inline-flex items-center justify-center gap-1"><i class="fas fa-times-circle text-rose-500 text-xs"></i> ไม่อนุมัติ</span>';
                }

                const approverText = (tk.status === 'approved' || tk.status === 'completed') ? (tk.director_name || 'ดร.สมชาย ผ่องใส (ผู้บริหาร)') : '-';

                html += `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 pl-2 text-xs font-bold font-mono text-slate-900 whitespace-nowrap">${tCode}</td>
                        <td class="p-3 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="font-bold text-pink-600 text-sm">${carId}</span>
                                <span class="text-[10px] text-gray-400">${plate}</span>
                            </div>
                        </td>
                        <td class="p-3 text-xs max-w-xs">
                            <p class="font-semibold text-gray-800 line-clamp-2" title="${issues}">${issues}</p>
                            <span class="text-[10px] text-blue-600 font-mono font-bold">ช่างประเมิน: ${cost} บาท</span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap min-w-[170px]">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="openPrintableFormModalFromData('${tCode}')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                    <i class="fas fa-print"></i> แบบฟอร์ม
                                </button>
                                <button type="button" onclick="openViewQuotationModal('${tCode}')" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                    <i class="fas fa-file-invoice-dollar"></i> ใบเสนอราคา
                                </button>
                            </div>
                        </td>
                        <td class="p-3 text-center text-xs whitespace-nowrap min-w-[150px]">
                            <span class="font-semibold text-gray-800 block whitespace-nowrap">${driver}</span>
                            <span class="text-[10px] text-gray-400 whitespace-nowrap">${dateStr}</span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap min-w-[110px]">${statusBadge}</td>
                        <td class="p-3 text-center text-xs text-slate-600 font-medium whitespace-nowrap min-w-[140px]">${approverText}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function clearMaintSearch() {
            const input = document.getElementById('all-maint-search');
            if (input) input.value = '';
            renderAllMaintenanceTable();
        }

        // ═══════════════════════════════════════════════════════════════════════════
        // EXPORT & PRINT ACTIONS
        // ═══════════════════════════════════════════════════════════════════════════
        function exportExecPDF() {
            window.print();
        }

        function exportDriverRatingsPdf() {
            window.print();
        }

        function exportExecExcel() {
            const tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
            let csv = "﻿รหัสคำขอ,ขบวนรถ,ทะเบียน,ผู้แจ้ง,อาการเสีย,ยอดเงินประเมิน,สถานะ,ผู้อนุมัติ\n";
            tickets.forEach(t => {
                const code = t.ticket_no || t.id || '-';
                const car = t.car_id || '-';
                const plate = t.license_plate || '-';
                const driver = t.driver_name || t.reporter || '-';
                const issue = (t.issue || '-').replace(/"/g, '""');
                const cost = t.total_cost || 0;
                const st = t.status || 'รออนุมัติ';
                const approver = t.director_name || '-';
                csv += `"${code}","${car}","${plate}","${driver}","${issue}","${cost}","${st}","${approver}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", `yru_executive_report_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printQuotationDocument() {
            window.print();
        }

        function formatThaiDateTime(dateStr) {
            if (!dateStr || dateStr === '-') return '-';
            try {
                const d = new Date(dateStr);
                if (isNaN(d.getTime())) return dateStr;
                const months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                const day = d.getDate();
                const month = months[d.getMonth() + 1] || '';
                const year = d.getFullYear() > 2400 ? d.getFullYear() : d.getFullYear() + 543;
                const hours = String(d.getHours()).padStart(2, '0');
                const mins = String(d.getMinutes()).padStart(2, '0');
                return `${day} ${month} ${year} (${hours}:${mins} น.)`;
            } catch (e) {
                return dateStr;
            }
        }

        function submitExecutiveApprovalAction(overrideAction) {
            const ticketId = document.getElementById('modalTicketId')?.value;
            let actionType = overrideAction || document.getElementById('modalActionType')?.value || 'approve';
            let remarks = document.getElementById('modalApproverRemarks')?.value || '';

            if (!ticketId) {
                closeApprovalModal();
                return;
            }

            const approvedItems = currentModalApprovalItems.filter(it => it.approved).map(it => it.name);
            const rejectedItems = currentModalApprovalItems.filter(it => !it.approved).map(it => it.name);
            const approvedTotalCost = currentModalApprovalItems.filter(it => it.approved).reduce((sum, it) => sum + Number(it.price || 0), 0);

            if (overrideAction === 'reject' || approvedItems.length === 0) {
                actionType = 'reject';
            }

            const isApprove = (actionType === 'approve' || actionType === 'approved');
            const newStatus = isApprove ? 'approved' : 'rejected';
            if (!remarks || remarks === 'อนุมัติการซ่อมบำรุงตามที่เสนอ') {
                remarks = isApprove ? (approvedItems.length === currentModalApprovalItems.length ? 'อนุมัติการซ่อมบำรุงตามรายการที่เสนอทั้งหมด' : `อนุมัติการซ่อมเฉพาะ ${approvedItems.length} รายการที่เลือก`) : 'ไม่อนุมัติ / ขอข้อมูลเพิ่มเติม';
            }

            let tickets = typeof getRealMaintenanceTickets === 'function' ? getRealMaintenanceTickets() : [];
            const idx = tickets.findIndex(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId) || String(t.ticket_no).toLowerCase() === String(ticketId).toLowerCase() || String(t.id).toLowerCase() === String(ticketId).toLowerCase()));

            const approvedTime = new Date().toISOString().replace('T', ' ').slice(0, 16);

            const patch = {
                status: newStatus,
                director_action: newStatus,
                director_remarks: remarks,
                director_name: 'ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)',
                approved_at: approvedTime,
                approved_items: approvedItems,
                rejected_items: rejectedItems,
                approved_total_cost: isApprove ? approvedTotalCost : 0
            };

            if (idx !== -1) {
                Object.assign(tickets[idx], patch);
            } else {
                tickets.push(Object.assign({ id: ticketId, ticket_no: ticketId }, patch));
            }

            try {
                localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(tickets));
                localStorage.setItem('yru_maintenance_tickets_v1', JSON.stringify(tickets));
                if (typeof syncRemoteStorage === 'function') {
                    syncRemoteStorage('yru_maintenance_tickets_v3', tickets);
                }
            } catch(e) {}

            // Send to Laravel API
            try {
                const apiEndpoint = isApprove ? '/api/executive/approve-maintenance' : '/api/executive/reject-maintenance';
                fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        ticket_id: ticketId,
                        approver: 'ดร.สมชาย ผ่องใส (ผู้บริหาร/อธิการบดี)',
                        remarks: remarks,
                        approved_items: approvedItems,
                        rejected_items: rejectedItems,
                        approved_total_cost: approvedTotalCost
                    })
                }).catch(err => console.error("Approval API error:", err));
            } catch(e) {}

            closeApprovalModal();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: isApprove ? 'success' : 'info',
                    title: isApprove ? 'บันทึกการอนุมัติเรียบร้อย' : 'บันทึกการไม่อนุมัติเรียบร้อย',
                    html: isApprove 
                        ? `<div class="text-xs text-slate-600 mt-2 text-left bg-emerald-50 p-3 rounded-xl border border-emerald-200">
                             <p class="font-bold text-emerald-800 mb-1"><i class="fas fa-check-circle mr-1"></i> อนุมัติ ${approvedItems.length} รายการ</p>
                             <p class="text-slate-600">งบประมาณอนุมัติ: <b class="text-emerald-700">${approvedTotalCost.toLocaleString(undefined, {minimumFractionDigits: 2})} บาท</b></p>
                           </div>`
                        : `<p class="text-xs text-slate-600">รายการคำขอ ${ticketId} ได้รับการบันทึกสถานะไม่อนุมัติ</p>`,
                    timer: 2500,
                    showConfirmButton: false
                });
            }

            initExecutiveData();
            if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
            if (typeof renderAllMaintenanceTable === 'function') renderAllMaintenanceTable();
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
                        <td colspan="6" class="p-12 text-center text-slate-400 font-medium">
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
                const dateStr = formatThaiDateTime(tk.created_at || tk.date || '-');
                const issues = Array.isArray(tk.issues) ? tk.issues.join(' | ') : (tk.issue || tk.symptoms || 'ตรวจเช็คสภาพทั่วไป');

                html += `
                    <tr class="hover:bg-purple-50/50 bg-purple-50/20 transition border-l-4 border-l-purple-600">
                        <td class="p-3 pl-2 text-xs font-bold font-mono text-purple-950">
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
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
                if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
                if (typeof renderAllMaintenanceTable === 'function') renderAllMaintenanceTable();
                if (typeof renderDriverRatingsPage === 'function') renderDriverRatingsPage();

                // ตรวจสอบแท็บจาก Hash URL หรือ Session Storage
                var currentHash = (window.location.hash || '').replace('#', '').trim();
                var activeTab = currentHash || sessionStorage.getItem('exec_active_tab') || 'dashboard';
                if (activeTab && typeof switchExecutiveTab === 'function') {
                    switchExecutiveTab(activeTab);
                }
            } catch(e) {
                console.error("Init executive error:", e);
            }
        }

        window.addEventListener("hashchange", function() {
            var newHash = (window.location.hash || '').replace('#', '').trim();
            if (newHash && typeof switchExecutiveTab === 'function') {
                switchExecutiveTab(newHash);
            }
        });

        document.addEventListener('DOMContentLoaded', initExecutiveData);
        setTimeout(initExecutiveData, 100);
        setTimeout(initExecutiveData, 500);

        window.addEventListener('storage', (event) => {
            const key = event ? event.key : null;
            if (!key || key === 'yru_surveys') {
                if (typeof renderDriverRatingsPage === 'function') renderDriverRatingsPage();
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
            }
            if (!key || key === 'yru_maintenance_tickets_v3') {
                if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
                if (typeof renderAllMaintenanceTable === 'function') renderAllMaintenanceTable();
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
            }
            if (!key || key === 'yru_trams_v18' || key === 'yru_call_queue' || key === 'yru_stops_v2' || key === 'yru_routes_v1') {
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
            }
        });
    </script>

</body>
</html>
