<?php
$content = <<<'HTML'
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YRU Tram Driver System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        :root {
            --primary-color: #ec4899;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f4f7f6;
            --info-color: #3498db;
            --dark-gray: #7f8c8d;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: row;
            height: 100vh;
            overflow: hidden;
        }

        .driver-container {
            width: 400px;
            min-width: 300px;
            max-width: 100%;
            height: 100%;
            background: white;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .notif-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .status-bar {
            background: #ecf0f1;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 0.9em;
        }

        .content { padding: 20px; flex: 1; }

        .passenger-alert {
            background: #fff9db;
            border: 2px solid var(--warning-color);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            display: none;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.15);
        }

        .alert-title {
            color: #d35400;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .waiting-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .waiting-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .count-tag {
            background: var(--danger-color);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .btn {
            width: 100%; padding: 16px; margin-bottom: 15px; border: none;
            border-radius: 12px; font-size: 1.1rem; font-weight: bold;
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; gap: 10px; transition: all 0.1s;
        }
        .btn:active { transform: scale(0.98); }
        
        .btn-gps { background: var(--info-color); color: white; }
        .btn-round { background: var(--danger-color); color: white; }
        .btn-logout { background: var(--dark-gray); color: white; margin-top: 20px; }

        .status-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .status-item { padding: 15px 5px; border: 2px solid #ddd; border-radius: 10px; text-align: center; font-size: 0.85rem; cursor: pointer; background: white; transition: all 0.2s; }
        
        .status-item.active[data-type="normal"] { background: #d4edda; border-color: var(--success-color); color: #155724; font-weight: bold; }
        .status-item.active[data-type="pause"] { background: #fff3cd; border-color: var(--warning-color); color: #856404; font-weight: bold; }
        .status-item.active[data-type="broken"] { background: #f8d7da; border-color: var(--danger-color); color: #721c24; font-weight: bold; }

        .info-card { background: #f8f9fa; border-left: 5px solid var(--primary-color); padding: 15px; margin-bottom: 20px; border-radius: 5px; }

        /* Map Styles */
        .custom-leaflet-icon { background: none !important; border: none !important; box-shadow: none !important; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
        .route-path { stroke-dasharray: 2500; stroke-dashoffset: 2500; animation: drawRoute 2.5s ease forwards; }
        @keyframes drawRoute { to { stroke-dashoffset: 0; } }
        
        @keyframes stationPulseRed {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50% { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
        }
        .station-dot-red { animation: stationPulseRed 2s infinite; }
        
        @keyframes stationPulseGreen {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
            50% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        }
        .station-dot-green { animation: stationPulseGreen 2s infinite; }
    </style>
</head>
<body>

<div class="driver-container">
    <div class="header">
        <div id="notif-count" class="notif-badge" style="display: none;">0</div>
        <h2><i class="fas fa-bus"></i> พนักงานขับรถ (YRU)</h2>
        <p>ยินดีต้อนรับ: คุณสมชาย ใจดี</p>
    </div>

    <div class="status-bar">
        <span>สถานะ GPS: <span id="gps-status-text" style="color:var(--success-color)">● ออนไลน์</span></span>
        <span>รอบที่: 5</span>
    </div>

    <div class="content">
        
        <div id="passenger-alert-box" class="passenger-alert">
            <div class="alert-title">
                <i class="fas fa-users-rays fa-beat"></i> มีผู้โดยสารกำลังรอรถ!
            </div>
            <div class="waiting-list" id="waiting-list"></div>
            
            <button onclick="clearPassengerCall()" class="btn" style="background: var(--success-color); color: white; margin-top: 12px; padding: 12px; font-size: 1rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <i class="fas fa-check-circle"></i> รับทราบ (ไปรับเรียบร้อยแล้ว)
            </button>
        </div>

        <label style="display:block; margin-bottom:10px; font-weight:bold;">แจ้งสถานะรถปัจจุบัน:</label>
        <div class="status-grid">
            <div class="status-item active" data-type="normal" onclick="syncDriverStatus('normal')">
                <i class="fas fa-check-circle"></i><br>ปกติ/กำลังขับ
            </div>
            <div class="status-item" data-type="pause" onclick="syncDriverStatus('pause')">
                <i class="fas fa-pause-circle"></i><br>หยุดพัก
            </div>
            <div class="status-item" data-type="broken" onclick="syncDriverStatus('broken')">
                <i class="fas fa-tools"></i><br>รถขัดข้อง
            </div>
        </div>

        <button class="btn btn-round" onclick="endCurrentRound()">
            <i class="fas fa-stopwatch"></i> สิ้นสุดรอบการเดินรถ
        </button>
        
        <button class="btn" style="background: var(--warning-color); color: white; margin-bottom: 15px;" onclick="openIssueReportPrompt()">
            <i class="fas fa-exclamation-triangle"></i> แจ้งซ่อมบำรุง / แจ้งปัญหาตัวรถ
        </button>

        <button class="btn btn-gps" onclick="alert('รีเฟรชตำแหน่ง GPS เรียบร้อย')">
            <i class="fas fa-location-arrow"></i> รีเฟรชตำแหน่ง GPS (Auto)
        </button>

        <hr>

        <button class="btn btn-logout" onclick="if(confirm('ต้องการออกจากระบบใช่หรือไม่?')) alert('ออกจากระบบสำเร็จ')">
            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
        </button>
    </div>
</div>

<div id="map" style="flex: 1; height: 100%; position: relative; z-index: 1;"></div>

<script>
    // 🗺️ Leaflet Map Initialization
    const map = L.map('map', {
        zoomControl: false,
        minZoom: 16,
        maxZoom: 20,
        maxBounds: [
            [6.535, 101.275],
            [6.563, 101.305]
        ]
    }).setView([6.548850, 101.289800], 17.0);

    L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: '&copy; Google Maps'
    }).addTo(map);

    L.control.zoom({ position: 'topright' }).addTo(map);

    // จำลองข้อมูลผู้โดยสารรอรถที่แต่ละจุด (สามารถดึงจาก API ได้ในอนาคต)
    const waitingPassengers = {
        1: 8, // ประตูหลังมอ. - คนเยอะ (แดง)
        2: 2, // ตึกศิลปะ - คนน้อย (เขียว)
        3: 12, // ศูนย์วิทย์ - คนเยอะ (แดง)
        4: 0, // คณะวิทย์ - คนน้อย (เขียว)
        5: 5, // สังคม - คนน้อย (เขียว)
        6: 15, // อาคาร 20 - คนเยอะ (แดง)
        7: 1 // วิทยาการจัดการ - คนน้อย (เขียว)
    };

    const stationData = [
        { id: 1, name: "จุดจอด 1 ประตูหลังมอ.", lat: 6.549929, lng: 101.291254, type: "P" },
        { id: 2, name: "จุดจอด 2 ตึกศิลปะ", lat: 6.549100, lng: 101.290467, type: "P" },
        { id: 3, name: "จุดจอด 3 ศูนย์วิทยาศาสตร์", lat: 6.547835, lng: 101.289502, type: "P" },
        { id: 4, name: "จุดจอด 4 คณะวิทยาศาสตร์", lat: 6.547224, lng: 101.289471, type: "P" },
        { id: 5, name: "จุดจอด 5 สังคมศาสตร์", lat: 6.547311, lng: 101.288880, type: "P" },
        { id: 6, name: "จุดจอด 6 อาคารเรียน20", lat: 6.548822, lng: 101.288523, type: "P" },
        { id: 7, name: "จุดจอด 7 วิทยาการจัดการ", lat: 6.549225, lng: 101.289286, type: "P" }
    ];

    stationData.forEach(st => {
        const pax = waitingPassengers[st.id] || 0;
        const isCrowded = pax > 5;
        const dotColorClass = isCrowded ? 'bg-red-500' : 'bg-green-500';
        const borderColorClass = isCrowded ? 'border-red-500' : 'border-green-500';
        const pulseClass = isCrowded ? 'station-dot-red' : 'station-dot-green';
        const textColorClass = isCrowded ? 'text-red-700' : 'text-green-700';
        const badgeBgClass = isCrowded ? 'border-red-100 bg-red-50' : 'border-green-100 bg-green-50';

        const markerHtml = `
            <div class="flex flex-col items-center animate-fade-in-up" style="transform: translate(-10px, -20px);">
                <div class="${pulseClass} bg-white border-2 ${borderColorClass} w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                    <div class="w-2 h-2 ${dotColorClass} rounded-full"></div>
                </div>
                <div class="${badgeBgClass} backdrop-blur-sm text-[10px] font-bold px-2 py-0.5 rounded-full shadow border ${textColorClass} mt-0.5 whitespace-nowrap">
                    <i class="fas fa-users mr-1"></i>รอ ${pax} คน
                </div>
            </div>
        `;

        const icon = L.divIcon({
            className: 'custom-leaflet-icon',
            html: markerHtml,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        const popupHtml = `
            <div class="p-2 font-kanit">
                <h4 class="font-bold text-slate-800 text-sm mb-1">${st.name}</h4>
                <p class="text-xs font-semibold ${textColorClass}">ผู้โดยสารรอรถ: ${pax} คน</p>
            </div>
        `;

        L.marker([st.lat, st.lng], { icon: icon }).addTo(map).bindPopup(popupHtml);
    });

    const routeCoords = [
        [6.549929, 101.291254],
        [6.549100, 101.290467],
        [6.547835, 101.289502],
        [6.547568, 101.289698],
        [6.547224, 101.289471],
        [6.547048, 101.289261],
        [6.547311, 101.288880],
        [6.547781, 101.288230],
        [6.548313, 101.288650],
        [6.548542, 101.288859],
        [6.548822, 101.288523],
        [6.548575, 101.288877],
        [6.549225, 101.289286],
        [6.550430, 101.290106],
        [6.549929, 101.291254]
    ];

    L.polyline(routeCoords, {
        color: '#3b82f6',
        weight: 4,
        opacity: 0.8,
        dashArray: '10, 10'
    }).addTo(map);

    setTimeout(() => { map.invalidateSize(); }, 500);

    const urlParams = new URLSearchParams(window.location.search);
    const currentCarId = urlParams.get('car_id') || '1';
    const currentCarCode = `EV-00${currentCarId}`;

    let currentOccupied = 0;
    const maxCapacity = currentCarId === '1' ? 8 : 5; // EV 01 ความจุสูงสุด 8 คน, EV 02 ความจุ 5

    document.addEventListener('DOMContentLoaded', () => {
        const capacityEl = document.getElementById('seats-max-capacity');
        if (capacityEl) capacityEl.innerText = maxCapacity;
        const codeEl = document.getElementById('car-display-code');
        if (codeEl) codeEl.innerText = currentCarCode;
    });

    function changeSeatsOccupied(change) {
        currentOccupied = Math.max(0, Math.min(maxCapacity, currentOccupied + change));
        const disp = document.getElementById('seats-occupied-display');
        if(disp) disp.innerText = currentOccupied;

        fetch('/api/driver/update-seats', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ car_id: currentCarId, occupied: currentOccupied })
        })
        .then(res => res.json())
        .then(data => console.log('Seats updated:', data))
        .catch(err => console.error('Error updating seats occupied:', err));
    }

    function openIssueReportPrompt() {
        const issue = prompt("กรุณาระบุปัญหาที่พบ (เช่น แอร์ไม่เย็น, ไฟชาร์จไม่เข้า, ลมยางอ่อน):");
        if (!issue) return;
        const details = prompt("รายละเอียดเพิ่มเติม (ถ้ามี):");

        fetch('/api/maintenance/report', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                car_id: currentCarCode,
                issue: issue,
                details: details || ''
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert('ส่งรายงานแจ้งชำรุดไปยังระบบแจ้งซ่อมเรียบร้อยแล้ว!');
            }
        })
        .catch(err => {
            console.error('Error reporting issue:', err);
            alert('เกิดข้อผิดพลาดในการส่งข้อมูล');
        });
    }

    // 🌟 ฟังก์ชันจัดการสถานะคนขับ (รวมการเปลี่ยนคลาส UI และยิง API)
    function syncDriverStatus(statusType) {
        // เปลี่ยนคลาส active บนหน้าจอ UI ให้ตรงกับปุ่มที่กด
        const items = document.querySelectorAll('.status-item');
        items.forEach(i => {
            if(i.getAttribute('data-type') === statusType) {
                i.classList.add('active');
            } else {
                i.classList.remove('active');
            }
        });

        const timerElement = document.getElementById('round-timer');
        const timeValue = timerElement ? timerElement.innerText.replace('เริ่ม: ', '') : '-';
        const gpsStatus = document.getElementById('gps-status-text');

        // อัปเดตข้อความแถบแสดงผลฝั่งคนขับ
        if(statusType === 'normal') {
            gpsStatus.innerHTML = `● ออนไลน์`;
            gpsStatus.style.color = "var(--success-color)";
        } else if(statusType === 'pause') {
            gpsStatus.innerHTML = `● หยุดพักชั่วคราว`;
            gpsStatus.style.color = "var(--warning-color)";
        } else if(statusType === 'broken') {
            gpsStatus.innerHTML = `● ออฟไลน์ (รถเสีย)`;
            gpsStatus.style.color = "var(--danger-color)";
        }

        // ยิง HTTP POST ไปยัง API 
        fetch('/api/update-driver-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: statusType,
                start_time: timeValue
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response status error');
            return response.json();
        })
        .then(data => console.log("Driver status synchronized:", data))
        .catch(error => console.error('Error synchronizing driver status:', error));
    }

    // 🛑 ฟังก์ชันส่งข้อมูลเมื่อพนักงานกดสิ้นสุดรอบเดินรถ เพื่อแจ้งเตือนไปยังแอปฝั่งผู้ใช้
    function endCurrentRound() {
        if (confirm('คุณต้องการสิ้นสุดรอบการเดินรถนี้ใช่หรือไม่?\n(ระบบจะส่งสัญญาณแจ้งไปยังหน้าจอผู้โดยสารทันที)')) {
            
            const nowTime = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';

            fetch('/api/end-driver-round', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: 'ended',
                    ended_at: nowTime
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response status error');
                return response.json();
            })
            .then(data => {
                alert('สิ้นสุดรอบการเดินรถเรียบร้อย! ระบบแจ้งเตือนผู้โดยสารแล้ว');
                
                // อัปเดต UI หน้าจอคนขับให้แสดงสถานะจบรอบวิ่ง
                const gpsStatus = document.getElementById('gps-status-text');
                if (gpsStatus) {
                    gpsStatus.innerHTML = `● สิ้นสุดรอบ (รอเปิดรอบใหม่)`;
                    gpsStatus.style.color = "var(--dark-gray)";
                }
                
                // นำคลาส active ออกจากกลุ่มปุ่มสถานะทั้งหมด
                document.querySelectorAll('.status-item').forEach(i => i.classList.remove('active'));
            })
            .catch(error => {
                console.error('Error ending round:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อระบบ ไม่สามารถสิ้นสุดรอบได้');
            });
        }
    }

    // 📡 --- ส่วนงาน Logic และการตรวจสอบสัญญาณแบบ Real-time Polling ---
    let lastRequestTime = null; 

    function checkPassengerCalls() {
        fetch('/api/check-ev-request?t=' + new Date().getTime())
        .then(response => {
            if (!response.ok) throw new Error('API server unavailable');
            return response.json();
        })
        .then(data => {
            const alertBox = document.getElementById('passenger-alert-box');
            const listContainer = document.getElementById('waiting-list');
            const badge = document.getElementById('notif-count');

            if (data && data.station) {
                window.pendingPax = parseInt(data.pax) || 0;
                alertBox.style.display = 'block';
                listContainer.innerHTML = `
                    <div class="waiting-item">
                        <span><i class="fas fa-map-marker-alt" style="color: var(--danger-color)"></i> <b>${data.station}</b> <br><small style="color:#888; font-size:11px;">เวลาเรียก: ${data.time}</small></span>
                        <span class="count-tag">${data.pax} คน</span>
                    </div>
                `;
                
                badge.innerText = "!";
                badge.style.display = 'flex';

                // เสียงแจ้งเตือนจะดังก็ต่อเมื่อเป็น Request ใหม่ (เวลาเปลี่ยน)
                if (lastRequestTime !== data.time) {
                    lastRequestTime = data.time;
                    playAlertSound();
                }
            } else {
                alertBox.style.display = 'none';
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching driver tracking data:', error));
    }

    function clearPassengerCall() {
        if (window.pendingPax) {
            changeSeatsOccupied(window.pendingPax);
            window.pendingPax = 0;
        }

        fetch('/api/clear-ev-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('API response error');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('passenger-alert-box').style.display = 'none';
                document.getElementById('notif-count').style.display = 'none';
                // ดึงอัปเดตใหม่ทันทีหลังจากล้างค่าแล้ว
                checkPassengerCalls();
            }
        })
        .catch(error => console.error('Error clearing data:', error));
    }

    function playAlertSound() {
        try {
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            audio.play();
        } catch(e) { 
            console.log("Audio playback was blocked by browser autoplay rules"); 
        }
    }

    // ตั้ง Polling ตรวจสอบสายเรียกเข้าจากผู้ใช้ทุกๆ 4 วินาที
    setInterval(checkPassengerCalls, 4000);
    checkPassengerCalls();

    // เริ่มต้นทำงานด้วยการตั้งค่าสถานะแรกเริ่มให้กับรถ
    syncDriverStatus('normal');
</script>

</body>
</html>
HTML;
file_put_contents('c:/Users/ASUS/Downloads/YRU-Train-Tracking-System/resources/views/passenger/tracking.blade.php', $content);
echo "File restored and fixed successfully!";
