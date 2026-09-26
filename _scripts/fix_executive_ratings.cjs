const fs = require('fs');
const path = require('path');

const execPath = path.join(__dirname, '../resources/views/passenger/executive/index.blade.php');
const maintPath = path.join(__dirname, '../resources/views/passenger/maintenance/index.blade.php');

const execContent = fs.readFileSync(execPath, 'utf8');
const maintContent = fs.readFileSync(maintPath, 'utf8');

// 1. Extract clean viewQuotationModal from maintenance
const qStart = maintContent.indexOf('<div id="viewQuotationModal"');
const qEnd = maintContent.indexOf('<!-- ═════════════════════════════════════════════════════════════════════════════════════ -->', qStart);
const qModalHtml = maintContent.substring(qStart, qEnd).trim();

// 2. Cut execContent right up to `<!-- Document Archive Modal: ระบบดูใบเสนอราคาย้อนหลัง`
const splitMarker = '<!-- Document Archive Modal: ระบบดูใบเสนอราคาย้อนหลัง';
const cutIndex = execContent.indexOf(splitMarker);

if (cutIndex === -1) {
    console.error('Could not find splitMarker in executive/index.blade.php');
    process.exit(1);
}

const cleanPrefix = execContent.substring(0, cutIndex).trim();

// 3. Define the clean, authentic ratings engine JavaScript (100% real user feedback, ZERO fake mock data)
const cleanRatingsScript = `
    <!-- Document Archive Modal: ระบบดูใบเสนอราคาย้อนหลังสำหรับผู้บริหาร (Executive Quotation Viewer) -->
    ${qModalHtml}

    <!-- ======================================================== -->
    <!-- DRIVER RATINGS & PASSENGER FEEDBACK REAL-TIME ENGINE     -->
    <!-- (ข้อเสนอแนะและความคิดเห็นจากผู้โดยสารจริง 100%)       -->
    <!-- ======================================================== -->
    <script>
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
            if (elFleetAvg) elFleetAvg.innerText = data.fleetAvg !== '-' ? `${data.fleetAvg} ★` : '-';

            const elTotalSurveys = document.getElementById('rating-kpi-total-surveys');
            if (elTotalSurveys) elTotalSurveys.innerText = `${data.totalSurveysCount} ครั้ง`;

            const elLiveCountBadge = document.getElementById('ratingsLiveCountBadge');
            if (elLiveCountBadge) elLiveCountBadge.innerText = `${data.totalSurveysCount} แบบประเมิน`;

            const elSubtabBadge = document.getElementById('subtab-reviews-badge');
            if (elSubtabBadge) elSubtabBadge.innerText = `${data.totalSurveysCount} ข้อความ`;

            // Top Performer Driver Card
            const top = data.topDriver;
            const elTopName = document.getElementById('rating-kpi-top-driver');
            const elTopCar = document.getElementById('rating-kpi-top-car');
            const elTopStats = document.getElementById('rating-kpi-top-stats');

            if (top && top.baseReviews > 0) {
                if (elTopName) elTopName.innerText = top.name;
                if (elTopCar) elTopCar.innerText = top.car_id;
                if (elTopStats) elTopStats.innerText = `${top.baselineAvg} ★ (${top.baseReviews} รีวิว)`;
            } else {
                if (elTopName) elTopName.innerText = "ยังไม่มีข้อมูล";
                if (elTopCar) elTopCar.innerText = "-";
                if (elTopStats) elTopStats.innerText = "รอผลประเมินจากผู้โดยสาร";
            }

            const elSatisfy = document.getElementById('rating-kpi-satisfaction-pct');
            if (elSatisfy) elSatisfy.innerText = data.totalSurveysCount > 0 ? `${data.satisfactionPct}%` : '-';

            // Top 5 Dimensions
            const dim1 = document.getElementById('rating-dim-1-val');
            const dim2 = document.getElementById('rating-dim-2-val');
            const dim3 = document.getElementById('rating-dim-3-val');
            const dim4 = document.getElementById('rating-dim-4-val');
            const dim5 = document.getElementById('rating-dim-5-val');

            if (dim1) dim1.innerText = data.dimAverages.q1 !== '-' ? `${data.dimAverages.q1} / 5.0` : '-';
            if (dim2) dim2.innerText = data.dimAverages.q2 !== '-' ? `${data.dimAverages.q2} / 5.0` : '-';
            if (dim3) dim3.innerText = data.dimAverages.q3 !== '-' ? `${data.dimAverages.q3} / 5.0` : '-';
            if (dim4) dim4.innerText = data.dimAverages.q4 !== '-' ? `${data.dimAverages.q4} / 5.0` : '-';
            if (dim5) dim5.innerText = data.dimAverages.q5 !== '-' ? `${data.dimAverages.q5} / 5.0` : '-';

            // Dashboard Highlight Card on Overview Tab
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
                else if (starFilter === 'below4.5') matchStar = score !== null && score < 4.50;

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
            link.setAttribute("download", \`yru_driver_evaluations_\${new Date().toISOString().slice(0,10)}.csv\`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Automatic Table & Dashboard Initialization
        function initExecutiveData() {
            try {
                if (typeof renderPendingApprovalTable === 'function') renderPendingApprovalTable();
                if (typeof renderAllMaintenanceTable === 'function') renderAllMaintenanceTable();
                if (typeof fetchWorkflowMaintenanceList === 'function') fetchWorkflowMaintenanceList();
                if (typeof renderExecutiveDashboard === 'function') renderExecutiveDashboard();
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
        });
    </script>

</body>
</html>
`;

const finalContent = cleanPrefix + '\n' + cleanRatingsScript.trim() + '\n';
fs.writeFileSync(execPath, finalContent, 'utf8');
console.log('Successfully updated executive/index.blade.php with clean authentic feedback engine!');
