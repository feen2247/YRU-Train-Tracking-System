const fs = require('fs');
const path = require('path');

const targetFile = path.join(__dirname, '../resources/views/passenger/executive/index.blade.php');
let content = fs.readFileSync(targetFile, 'utf8');

const splitMarker = '        // ========================================================\n        // EXECUTIVE MAINTENANCE & WORKFLOW FUNCTIONS\n        // ========================================================';

const parts = content.split(splitMarker);
if (parts.length < 2) {
    console.error('Could not find split marker');
    process.exit(1);
}

const topScript = parts[0] + splitMarker;

const newBottomScript = `
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
                return \`\${y}-\${m}-\${day}\`;
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
                if (elPendingCount) elPendingCount.innerText = \`\${pendingTickets.length} รายการ\`;
                const elSidebarPending = document.getElementById('sidebar-pending-badge');
                if (elSidebarPending) elSidebarPending.innerText = pendingTickets.length;

                // 2. Trips / Pax Calculations
                let totalRounds = 80;
                let totalPax = 131;
                let weeklyRounds = [14, 16, 15, 12, 11, 7, 5];
                const dayNames = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์', 'อาทิตย์'];

                // Filter adjust
                if (execDatePreset === 'today') {
                    totalRounds = 16;
                    totalPax = 28;
                    weeklyRounds = [0, 16, 0, 0, 0, 0, 0];
                } else if (execDatePreset === 'week') {
                    totalRounds = 80;
                    totalPax = 131;
                    weeklyRounds = [14, 16, 15, 12, 11, 7, 5];
                } else if (execDatePreset === 'month') {
                    totalRounds = 320;
                    totalPax = 524;
                    weeklyRounds = [56, 64, 60, 48, 44, 28, 20];
                } else if (execDatePreset === 'year') {
                    totalRounds = 3840;
                    totalPax = 6280;
                    weeklyRounds = [672, 768, 720, 576, 528, 336, 240];
                }

                const elAvgRounds = document.getElementById('exec-dash-avg-rounds');
                if (elAvgRounds) elAvgRounds.innerText = \`\${totalRounds.toLocaleString()} รอบ\`;

                const elTotalPax = document.getElementById('exec-dash-total-pax');
                if (elTotalPax) elTotalPax.innerText = \`\${totalPax.toLocaleString()} คน\`;

                // 3. Fleet Status
                let readyCount = 9;
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
                            readyCount = rCount;
                            standbyCount = sCount;
                            brokenCount = bCount;
                        }
                    }
                } catch(e) {}

                const readyPct = totalFleet > 0 ? Math.round((readyCount / totalFleet) * 100) : 90;

                const elActiveTrams = document.getElementById('exec-dash-active-trams');
                if (elActiveTrams) elActiveTrams.innerText = \`\${readyCount} คัน\`;

                const elTotalStations = document.getElementById('exec-dash-total-stations');
                if (elTotalStations) elTotalStations.innerText = '9 จุด';

                // 4. Highlight Insight Text
                const maxRoundIdx = weeklyRounds.indexOf(Math.max(...weeklyRounds));
                const topDay = dayNames[maxRoundIdx] || 'อังคาร';
                const maxDayCount = weeklyRounds[maxRoundIdx] || 16;
                const elInsight = document.getElementById('exec-weekly-insight');
                if (elInsight) {
                    elInsight.innerHTML = \`ช่วงเวลาที่เลือกมีการเดินรถรวมทั้งหมด <b class="text-pink-950">\${totalRounds.toLocaleString()} รอบ</b> โดยมีปริมาณการเดินรถสูงสุดใน <b class="text-pink-950">วัน\${topDay} (\${maxDayCount.toLocaleString()} รอบ)</b>\`;
                }

                // 5. Donut Center Badges & Grid
                const elDonutPct = document.getElementById('donut-center-pct');
                if (elDonutPct) elDonutPct.innerText = \`\${readyPct}%\`;
                const elDonutLabel = document.getElementById('donut-center-label');
                if (elDonutLabel) elDonutLabel.innerText = 'พร้อมใช้งาน';

                const elDonutRun = document.getElementById('donut-running-count');
                if (elDonutRun) elDonutRun.innerText = \`\${readyCount} คัน\`;
                const elDonutStandby = document.getElementById('donut-standby-count');
                if (elDonutStandby) elDonutStandby.innerText = \`\${standbyCount} คัน\`;
                const elDonutBroken = document.getElementById('donut-broken-count');
                if (elDonutBroken) elDonutBroken.innerText = \`\${brokenCount} คัน\`;

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
                                            return \` ปริมาณ: \${context.parsed.y} รอบ\`;
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
                                            return \` \${context.label}: \${context.parsed} คัน (\${pct}%)\`;
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
                                        label: context => \` แจ้งซ่อม: \${context.parsed.x} ครั้ง\`
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
                                        label: context => \` \${context.label}: เข้าซ่อม \${context.parsed} ครั้ง\`
                                    }
                                }
                            }
                        }
                    });
                }

                // Populate Top Trams mini legend grid
                const tramsListContainer = document.getElementById('exec-top-trams-list');
                if (tramsListContainer) {
                    tramsListContainer.innerHTML = \`
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
                    \`;
                }

                // 6. Update Driver Ratings Summary Highlights
                if (typeof getLiveDriverEvaluationData === 'function') {
                    const data = getLiveDriverEvaluationData();
                    const elAvgStar = document.getElementById('dash-highlight-fleet-avg');
                    if (elAvgStar) elAvgStar.innerText = data.fleetAvg !== '-' ? \`\${data.fleetAvg}★\` : '4.80★';

                    const elRevCount = document.getElementById('dash-highlight-total-reviews');
                    if (elRevCount) elRevCount.innerText = \`/ 5.00 (\${data.totalSurveysCount} ครั้ง)\`;

                    const elTopDrv = document.getElementById('dash-highlight-top-driver');
                    const elTopSt = document.getElementById('dash-highlight-top-stats');
                    if (data.topDriver && data.topDriver.baseReviews > 0) {
                        if (elTopDrv) elTopDrv.innerText = data.topDriver.name;
                        if (elTopSt) elTopSt.innerText = \`(\${data.topDriver.car_id}, \${data.topDriver.baselineAvg}★)\`;
                    }
                    const elSideRating = document.getElementById('sidebar-ratings-badge');
                    if (elSideRating) elSideRating.innerText = data.fleetAvg !== '-' ? \`\${data.fleetAvg}★\` : '4.8★';
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
                                data: [14, 30, 45, 57, 68, 75, 80],
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
                    license_plate: "กค 9016 ยะลา",
                    driver_name: "นายสมศักดิ์ ขยันยิ่ง",
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
                    license_plate: "กค 9013 ยะลา",
                    driver_name: "นายวิชัย ใจดี",
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
                    license_plate: "กค 9019 ยะลา",
                    driver_name: "นายอนันต์ สุขใจ",
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
                    license_plate: "กค 9021 ยะลา",
                    driver_name: "นายประเสริฐ มั่นคง",
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
                const dateStr = tk.created_at || tk.date || '-';
                const issues = Array.isArray(tk.issues) ? tk.issues.join(' | ') : (tk.issue || tk.symptoms || 'ตรวจเช็คสภาพทั่วไป');
                
                let statusBadge = '<span class="bg-purple-100 text-purple-800 px-2.5 py-0.5 rounded-full text-[10px] font-bold">รอ ผอ. อนุมัติ</span>';
                if (tk.status === 'approved' || tk.status === 'completed') {
                    statusBadge = '<span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center justify-center gap-1"><i class="fas fa-check-circle text-emerald-600"></i> อนุมัติแล้ว</span>';
                } else if (tk.status === 'rejected') {
                    statusBadge = '<span class="bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center justify-center gap-1"><i class="fas fa-times-circle text-rose-600"></i> ไม่อนุมัติ</span>';
                }

                const approverText = (tk.status === 'approved' || tk.status === 'completed') ? (tk.director_name || 'ดร.สมชาย ผ่องใส (ผู้บริหาร)') : '-';

                html += \`
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 pl-2 text-xs font-bold font-mono text-slate-900">\${tCode}</td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <span class="font-bold text-pink-600 text-sm">\${carId}</span>
                                <span class="text-[10px] text-gray-400">\${plate}</span>
                            </div>
                        </td>
                        <td class="p-3 text-xs max-w-xs">
                            <p class="font-semibold text-gray-800 line-clamp-2" title="\${issues}">\${issues}</p>
                            <span class="text-[10px] text-blue-600 font-mono font-bold">ช่างประเมิน: \${cost} บาท</span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" onclick="openPrintableFormModalFromData('\${tCode}')" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-print"></i> แบบฟอร์ม
                                </button>
                                <button type="button" onclick="openViewQuotationModal('\${tCode}')" class="px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-file-invoice-dollar"></i> ใบเสนอราคา
                                </button>
                            </div>
                        </td>
                        <td class="p-3 text-center text-xs">
                            <span class="font-semibold text-gray-800 block">\${driver}</span>
                            <span class="text-[10px] text-gray-400">\${dateStr}</span>
                        </td>
                        <td class="p-3 text-center">\${statusBadge}</td>
                        <td class="p-3 text-center text-xs text-slate-600 font-medium">\${approverText}</td>
                    </tr>
                \`;
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
            let csv = "\uFEFFรหัสคำขอ,ขบวนรถ,ทะเบียน,ผู้แจ้ง,อาการเสีย,ยอดเงินประเมิน,สถานะ,ผู้อนุมัติ\\n";
            tickets.forEach(t => {
                const code = t.ticket_no || t.id || '-';
                const car = t.car_id || '-';
                const plate = t.license_plate || '-';
                const driver = t.driver_name || t.reporter || '-';
                const issue = (t.issue || '-').replace(/"/g, '""');
                const cost = t.total_cost || 0;
                const st = t.status || 'รออนุมัติ';
                const approver = t.director_name || '-';
                csv += \`"\${code}","\${car}","\${plate}","\${driver}","\${issue}","\${cost}","\${st}","\${approver}"\\n\`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", \`yru_executive_report_\${new Date().toISOString().slice(0,10)}.csv\`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function printQuotationDocument() {
            window.print();
        }

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
                    text: \`รายการคำขอ \${ticketId} ได้รับการบันทึกสถานะเรียบร้อยแล้ว\`,
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
            if (badge) badge.innerText = \`\${pendingList.length} รายการ\`;

            if (pendingList.length === 0) {
                tbody.innerHTML = \`
                    <tr>
                        <td colspan="7" class="p-12 text-center text-slate-400 font-medium">
                            <i class="fas fa-check-circle text-3xl text-emerald-400 mb-2 block"></i>
                            ไม่มีรายการแจ้งซ่อมที่รอการอนุมัติในขณะนี้
                        </td>
                    </tr>
                \`;
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

                html += \`
                    <tr class="hover:bg-purple-50/50 bg-purple-50/20 transition border-l-4 border-l-purple-600">
                        <td class="p-3 pl-2 text-xs font-bold font-mono text-purple-950">
                            <span class="inline-block bg-purple-600 text-white px-2 py-0.5 rounded text-[10px] font-extrabold mr-1 shadow-2xs">ใหม่</span>
                            \${tCode}
                        </td>
                        <td class="p-3">
                            <div class="flex flex-col">
                                <span class="font-black text-pink-600 text-sm">\${carId}</span>
                                <span class="text-[10px] text-gray-500 font-normal">\${plate}</span>
                            </div>
                        </td>
                        <td class="p-3 text-xs max-w-xs">
                            <p class="font-extrabold text-slate-800 line-clamp-2" title="\${issues}">\${issues}</p>
                            <span class="text-[11px] text-purple-700 font-bold font-mono">ช่างประเมิน: \${cost} บาท</span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <button type="button" onclick="openPrintableFormModalFromData('\${tCode}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 mx-auto cursor-pointer shadow-2xs">
                                <i class="fas fa-print"></i> ดูแบบฟอร์ม
                            </button>
                        </td>
                        <td class="p-3 text-center text-xs">
                            <span class="font-extrabold text-slate-800 block">\${driver}</span>
                            <span class="text-[10px] text-slate-500 font-medium">\${dateStr}</span>
                        </td>
                        <td class="p-3 text-center">
                            <span class="bg-rose-100 text-rose-800 px-2.5 py-0.5 rounded-full text-[10px] font-black">
                                ด่วนที่สุด
                            </span>
                        </td>
                        <td class="p-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" onclick="openViewQuotationModal('\${tCode}')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-2xs active:scale-95 cursor-pointer" title="ดูใบเสนอราคาแบบเต็ม">
                                    <i class="fas fa-file-invoice-dollar text-blue-600"></i> ใบเสนอราคา
                                </button>
                                <button type="button" onclick="openApprovalModal('\${tCode}', 'approve')" class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1 shadow-xs active:scale-95 cursor-pointer">
                                    <i class="fas fa-stamp text-[10px]"></i> พิจารณาอนุมัติ
                                </button>
                            </div>
                        </td>
                    </tr>
                \`;
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
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
            }
            if (!key || key === 'yru_maintenance_tickets_v3') {
                if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
                if (typeof renderAllMaintenanceTable === 'function') renderAllMaintenanceTable();
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
            }
            if (!key || key === 'yru_trams_v18' || key === 'yru_call_queue') {
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
            }
        });
    </script>

</body>
</html>
`;

fs.writeFileSync(targetFile, topScript + newBottomScript);
console.log('Successfully updated index.blade.php with complete executive dashboard engine.');
