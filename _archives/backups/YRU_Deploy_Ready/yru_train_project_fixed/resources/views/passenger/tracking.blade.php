<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - พนักงานขับรถ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th {
            font-family: 'Kanit', sans-serif !important;
        }
        /* Protect FontAwesome Icons from being overridden by Kanit */
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class*="fa-"] {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', sans-serif !important;
        }
        
        .status-item {
            background: rgba(30, 41, 59, 0.4);
            border-color: rgba(51, 65, 85, 0.6);
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-item.active[data-type="normal"] {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #34d399;
            font-weight: bold;
        }
        .status-item.active[data-type="pause"] {
            background: rgba(245, 158, 11, 0.1);
            border-color: #f59e0b;
            color: #fbbf24;
            font-weight: bold;
        }
        .status-item.active[data-type="broken"] {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #f87171;
            font-weight: bold;
        }

        .custom-leaflet-icon { background: none !important; border: none !important; box-shadow: none !important; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
        
        @keyframes stationPulseRed {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.5); }
            50% { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
        }
        .station-dot-red { animation: stationPulseRed 2s infinite; }
        
        @keyframes stationPulseGreen {
            0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
            50% { box-shadow: 0 0 0 6px rgba(34,197,94,0); }
        }
        .station-dot-green { animation: stationPulseGreen 2s infinite; }

        .notif-badge {
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 900;
            border: 3px solid #0f172a;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-slate-950 text-white overflow-hidden">

<div class="flex flex-col h-screen overflow-hidden">
    <!-- Top Bar -->
    <header class="h-[75px] bg-slate-900 border-b border-slate-800 px-6 flex items-center justify-between z-50 shadow-md flex-shrink-0">
        <div class="flex items-center gap-4">
            <img src="{{ asset('img/logo-yru.png') }}" alt="YRU Logo" class="h-10 bg-white rounded-full p-0.5 shadow-md">
            <div class="flex items-center gap-2 text-sm md:text-base font-semibold tracking-wide">
                <span class="text-slate-300"><i class="fas fa-user-circle text-lg mr-1.5 text-slate-400"></i><span id="driver-name-display">พนักงานขับรถ</span></span>
                <span class="text-slate-700">|</span>
                <span class="text-pink-400 font-bold bg-pink-500/10 border border-pink-500/20 px-2.5 py-0.5 rounded-full text-xs" id="car-display-code">EV-01</span>
                <span class="text-slate-700">|</span>
                <span class="text-slate-400 font-medium text-xs md:text-sm" id="route-name-display">สาย 1 (เนินขาม-หอพัก)</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs font-bold uppercase tracking-wider">
            <div class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-xl">
                <span class="text-slate-500 text-[10px]">GPS:</span>
                <span id="gps-status-text" class="text-emerald-400">● ออนไลน์</span>
            </div>
            <div class="flex items-center gap-1.5 bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-xl">
                <span class="text-slate-500 text-[10px]">รอบที่:</span>
                <span class="text-white text-sm" id="round-number">5</span>
            </div>
            <div id="status-badge-container">
                <span id="status-badge" class="bg-green-500/10 border border-green-500/20 text-green-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                    <i class="fas fa-circle text-green-500 text-[8px] animate-pulse"></i> กำลังเดินรถ
                </span>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Left Column: Navigation Map -->
        <div class="flex-1 relative h-full">
            <div id="map" class="w-full h-full z-10"></div>
            <!-- Alert Badge inside Map corner -->
            <div id="notif-count" class="absolute top-4 left-4 z-20 notif-badge hidden">0</div>
        </div>

        <!-- Right Column: Driver Controls -->
        <div class="w-[420px] h-full bg-slate-900 border-l border-slate-800 flex flex-col p-5 overflow-y-auto space-y-4 justify-between flex-shrink-0">
            
            <div class="space-y-4">
                <!-- Next Stop & Call Alert Card -->
                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2.5">
                        <span class="text-xs font-semibold text-slate-400 tracking-wide uppercase flex items-center gap-1.5"><i class="fas fa-map-marked-alt text-pink-500"></i>จุดจอดถัดไป</span>
                        <span class="text-[10px] text-pink-400/80 font-bold bg-pink-500/10 px-2 py-0.5 rounded" id="next-stop-sequence">STOP 1/7</span>
                    </div>
                    
                    <h3 id="next-stop-name" class="text-xl font-extrabold text-white tracking-wide">จุดจอด 1 ประตูหลังมอ.</h3>
                    
                    <!-- Call Alert Banner (เด้งขึ้นตรงนี้เมื่อมีคนเรียก) -->
                    <div id="next-stop-alert" class="hidden bg-gradient-to-r from-pink-900/40 to-purple-900/40 border border-pink-500/30 text-white rounded-xl p-3.5 flex flex-col gap-2.5 animate-pulse">
                        <div class="flex items-center justify-between border-b border-pink-500/10 pb-1.5">
                            <span class="text-xs font-bold text-pink-400 flex items-center gap-1.5"><i class="fas fa-bell text-pink-400 fa-bounce"></i>มีสัญญาณเรียกรถใหม่!</span>
                            <span class="text-[9px] text-pink-300 font-mono" id="call-alert-time">23:31 น.</span>
                        </div>
                        <div class="text-xs space-y-1.5 text-slate-200">
                            <div><i class="fas fa-map-marker-alt text-pink-400 mr-1.5"></i><b>จุดเริ่มต้น (รับที่):</b> <span id="call-alert-station" class="text-white font-bold">ศูนย์วิทยาศาสตร์</span></div>
                            <div><i class="fas fa-flag text-purple-400 mr-1.5"></i><b>ปลายทาง (ไปที่):</b> <span id="call-alert-destination" class="text-white font-bold">คณะสังคมศาสตร์</span></div>
                            <div><i class="fas fa-users text-blue-400 mr-1.5"></i><b>จำนวนผู้โดยสาร:</b> <span id="call-alert-pax" class="font-extrabold text-pink-400 text-sm bg-pink-500/20 px-1.5 py-0.5 rounded">5 คน</span></div>
                        </div>
                        <!-- Call Alert Button Container -->
                        <div id="call-alert-btn-container" class="mt-1"></div>
                    </div>
                    
                    <div id="next-stop-no-pax" class="bg-slate-900/50 text-slate-400 rounded-xl p-3 flex items-center gap-2 text-xs">
                        <i class="fas fa-check-circle text-emerald-500"></i> ไม่มีผู้โดยสารกดเรียกรถในขณะนี้
                    </div>
                    
                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <button onclick="handleArrivedAtStop()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition active:scale-95 text-sm shadow-lg shadow-emerald-950/20"><i class="fas fa-check-circle"></i> ถึงจุดจอดแล้ว</button>
                        <button onclick="handleSkipStop()" class="bg-slate-700 hover:bg-slate-600 text-white/80 font-bold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition active:scale-95 text-sm"><i class="fas fa-forward"></i> ข้ามจุดจอด</button>
                    </div>
                </div>

                <!-- Passenger Counter Card -->
                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2.5">
                        <span class="text-xs font-semibold text-slate-400 tracking-wide uppercase flex items-center gap-1.5"><i class="fas fa-users text-blue-500"></i>นับจำนวนผู้โดยสาร</span>
                        <span class="text-[10px] text-blue-400/80 font-bold bg-blue-500/10 px-2 py-0.5 rounded" id="seats-percentage">0%</span>
                    </div>
                    
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-400">ผู้โดยสารบนรถ</span>
                        <span class="text-3xl font-extrabold text-white"><span id="seats-occupied-display" class="text-blue-400">0</span> <span class="text-sm font-normal text-slate-400">/ <span id="seats-max-capacity">8</span> คน</span></span>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <button onclick="changeSeatsOccupied(-1)" class="bg-slate-700 hover:bg-slate-600 text-white font-bold py-2.5 rounded-xl text-sm transition active:scale-95">- 1</button>
                        <button onclick="changeSeatsOccupied(1)" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2.5 rounded-xl text-sm transition active:scale-95">+ 1</button>
                        <button onclick="changeSeatsOccupied(5)" class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2.5 rounded-xl text-sm transition active:scale-95">+ 5</button>
                    </div>
                </div>

                <!-- Emergency / Repair Card -->
                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 shadow-xl space-y-4">
                    <div class="flex items-center border-b border-slate-700 pb-2.5">
                        <span class="text-xs font-semibold text-slate-400 tracking-wide uppercase flex items-center gap-1.5"><i class="fas fa-exclamation-triangle text-amber-500"></i>แจ้งเหตุ / แจ้งซ่อม</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <button onclick="openIssueReportPrompt()" class="bg-amber-600/10 hover:bg-amber-600/20 text-amber-400 border border-amber-500/20 font-bold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition active:scale-95 text-sm"><i class="fas fa-wrench"></i> แจ้งซ่อม</button>
                        <button onclick="triggerSOSAlert()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition active:scale-95 text-sm shadow-lg shadow-red-950/20 animate-pulse"><i class="fas fa-exclamation-circle"></i> SOS (ด่วน)</button>
                    </div>
                </div>

                <!-- Driver Status Selector -->
                <div class="bg-slate-800/80 border border-slate-700/60 rounded-2xl p-4 shadow-xl space-y-3">
                    <span class="text-xs font-semibold text-slate-400 tracking-wide uppercase block flex items-center gap-1.5"><i class="fas fa-cog text-slate-400"></i>ปรับสถานะรถปัจจุบัน</span>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="status-item cursor-pointer border rounded-xl py-2.5 text-center transition hover:bg-slate-700/50 border-slate-700" data-type="normal" onclick="syncDriverStatus('normal')">
                            <i class="fas fa-check-circle text-emerald-500 mb-1 block"></i> ปกติ
                        </div>
                        <div class="status-item cursor-pointer border rounded-xl py-2.5 text-center transition hover:bg-slate-700/50 border-slate-700" data-type="pause" onclick="syncDriverStatus('pause')">
                            <i class="fas fa-pause-circle text-amber-500 mb-1 block"></i> หยุดพัก
                        </div>
                        <div class="status-item cursor-pointer border rounded-xl py-2.5 text-center transition hover:bg-slate-700/50 border-slate-700" data-type="broken" onclick="syncDriverStatus('broken')">
                            <i class="fas fa-tools text-red-500 mb-1 block"></i> รถเสีย
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer: End Round -->
            <div class="pt-3 border-t border-slate-800 flex flex-col gap-2.5">
                <button onclick="endCurrentRound()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition active:scale-95 text-base shadow-lg shadow-red-950/40 border border-red-500/20"><i class="fas fa-stopwatch text-lg"></i> สิ้นสุดรอบการเดินรถ</button>
                <button onclick="if(confirm('ต้องการออกจากระบบใช่หรือไม่?')) window.location.href='{{ url('/') }}'" class="w-full text-center text-xs text-slate-600 hover:text-slate-400 transition py-0.5"><i class="fas fa-sign-out-alt mr-1"></i> ออกจากระบบ</button>
            </div>
        </div>
    </div>
</div>

<!-- Legacy alert box container hidden for compatibility -->
<div id="passenger-alert-box" style="display:none;"><div id="waiting-list"></div></div>

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

    // ===== MAP ENHANCEMENTS: Stops & Buildings =====
    
    // 1. Draw Buildings (Landmarks)
    const buildings = [
        { name: "อาคาร 20", bounds: [[6.548600, 101.288300], [6.549000, 101.288750]], color: "#fbcfe8" }, // Pink-200
        { name: "คณะวิทยาการจัดการ", bounds: [[6.549050, 101.289050], [6.549400, 101.289550]], color: "#fbcfe8" },
        { name: "ศูนย์วิทยาศาสตร์", bounds: [[6.547600, 101.289300], [6.548050, 101.289700]], color: "#fbcfe8" },
        { name: "ตึกศิลปะ", bounds: [[6.548900, 101.290200], [6.549300, 101.290700]], color: "#fbcfe8" },
    ];
    
    buildings.forEach(b => {
        L.rectangle(b.bounds, {
            color: '#ec4899', // Pink-500 border
            weight: 2,
            fillColor: b.color,
            fillOpacity: 0.4
        }).addTo(map)
        .bindTooltip(`<div class="font-bold text-pink-700 text-[10px] whitespace-nowrap" style="text-shadow: 1px 1px 2px white;">${b.name}</div>`, {
            permanent: true,
            direction: 'center',
            className: 'bg-transparent border-none shadow-none'
        });
    });

    // 2. Draw Stop Markers
    const fixedStops = [
        { id: 1, name: "ประตูหลังมอ", lat: 6.549929, lng: 101.291254 },
        { id: 2, name: "ตึกศิลปะ", lat: 6.549100, lng: 101.290467 },
        { id: 3, name: "ศูนย์วิทยาศาสตร์", lat: 6.547835, lng: 101.289502 },
        { id: 4, name: "วิทยาศาสตร์", lat: 6.547224, lng: 101.289471 },
        { id: 5, name: "สังคมศาสตร์", lat: 6.547311, lng: 101.288880 },
        { id: 6, name: "อาคาร 20", lat: 6.548822, lng: 101.288523 },
        { id: 7, name: "วิทยาการจัดการ", lat: 6.549225, lng: 101.289286 }
    ];

    fixedStops.forEach(stop => {
        const stopIcon = L.divIcon({
            className: 'custom-stop-icon',
            html: `<div class="w-8 h-8 bg-white border-2 border-slate-500 rounded-full flex items-center justify-center shadow-md" style="z-index: 500;">
                       <i class="fas fa-map-marker-alt text-slate-600 text-[14px]"></i>
                   </div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const marker = L.marker([stop.lat, stop.lng], { icon: stopIcon, zIndexOffset: -100 }).addTo(map);
        
        marker.bindTooltip(
            `<div class="font-bold text-slate-700 text-[11px] font-kanit">${stop.id}. ${stop.name}</div>`,
            { permanent: true, direction: 'right', offset: [15, 0], className: 'bg-white/90 border border-slate-200 shadow-sm rounded px-2 py-1' }
        );
    });

    L.control.zoom({ position: 'topright' }).addTo(map);

    // จำลองข้อมูลผู้โดยสารรอรถที่แต่ละจุด
    const waitingPassengers = {
        1: 8,
        2: 2,
        3: 12,
        4: 0,
        5: 5,
        6: 15,
        7: 1
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
        color: '#ec4899',
        weight: 4,
        opacity: 0.8,
        dashArray: '10, 10'
    }).addTo(map);

    setTimeout(() => { map.invalidateSize(); }, 500);

    const urlParams = new URLSearchParams(window.location.search);
    const currentCarId = urlParams.get('car_id') || '1';
    const currentCarCode = currentCarId.length >= 2 ? `EV-${currentCarId}` : `EV-0${currentCarId}`;

    const defaultUsers = [
    { user_id: "USR001", name: "Admin YRU", email: "admin@yru.ac.th", role: "admin", status: "ใช้งาน" },
    { user_id: "USR002", name: "ดร.สมชาย เรียนดี", email: "somchai.r@yru.ac.th", role: "executive", status: "ใช้งาน" },
    { user_id: "USR003", name: "นายอัสมี มูเล็ง", email: "asmee@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR004", name: "นายอัรฟาน มะเระ", email: "arfan@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR005", name: "นายซูเฟียน มะโอะ", email: "sufiyan@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR006", name: "นายอุสมาน สาและ", email: "usman@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR007", name: "นายบัดรี สาและ", email: "badri@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR008", name: "นายตอรริก ลือแมะ", email: "torik@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR009", name: "นายสมหวัง ใจดี", email: "somwang@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR010", name: "นายสมใจ ใจดี", email: "somjal@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR011", name: "นายกิตติ ตั้งใจ", email: "kitti@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR012", name: "นายรุสลัน สอเฮาะ", email: "ruslan@yru.ac.th", role: "driver", status: "ใช้งาน" }
];
    const defaultTrams = [
        { id: "EV-01", name: "รถไฟฟ้าคันที่ 1", plate: "กค 1234 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-01-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายสมชาย ใจดี (เล่าปิง)", driver_id: "USR003", battery: 80, coords: "6.549929, 101.291254" },
        { id: "EV-02", name: "รถไฟฟ้าคันที่ 2", plate: "กค 5678 ยะลา", capacity_sit: 5, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-02-YRU", route: "สาย 2: อาคาร 20-คณะมนุษย์", driver: "นายรักสงบ มั่นคง (น้าสงบ)", driver_id: "USR004", battery: 80, coords: "6.549100, 101.290467" },
        { id: "EV-03", name: "รถไฟฟ้าคันที่ 3", plate: "กค 9012 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-03-YRU", route: "สาย 3: ประตูหลังมอ-หอประชุม", driver: "นายประสิทธิ์ เรียนรู้ (พี่สิทธิ์)", driver_id: "USR005", battery: 80, coords: "6.547835, 101.289502" },
        { id: "EV-04", name: "รถไฟฟ้าคันที่ 4", plate: "กค 3456 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-04-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายวิชัย เลิศล้ำ (พี่ชัย)", driver_id: "USR006", battery: 80, coords: "6.547224, 101.289471" },
        { id: "EV-05", name: "รถไฟฟ้าคันที่ 5", plate: "กค 7890 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-05-YRU", route: "สาย 2: อาคาร 20-คณะมนุษย์", driver: "นายสมเจต มีคุณ (พี่เจต)", driver_id: "USR007", battery: 80, coords: "6.547311, 101.288880" },
        { id: "EV-06", name: "รถไฟฟ้าคันที่ 6", plate: "กค 1122 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-06-YRU", route: "สาย 3: ประตูหลังมอ-หอประชุม", driver: "นายดนัย รักความสะอาด (พี่ดนัย)", driver_id: "USR008", battery: 80, coords: "6.548822, 101.288523" },
        { id: "EV-07", name: "รถไฟฟ้าคันที่ 7", plate: "กค 3344 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-07-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายณรงค์ รักงานบริการ (พี่ณรงค์)", driver_id: "USR009", battery: 80, coords: "6.549225, 101.289286" },
        { id: "EV-08", name: "รถไฟฟ้าคันที่ 8", plate: "กค 5566 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-08-YRU", route: "สาย 2: อาคาร 20-คณะมนุษย์", driver: "นายอนันต์ ขับปลอดภัย (พี่อนันต์)", driver_id: "USR010", battery: 80, coords: "6.549929, 101.291254" },
        { id: "EV-09", name: "รถไฟฟ้าคันที่ 9", plate: "กค 7788 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-09-YRU", route: "สาย 3: ประตูหลังมอ-หอประชุม", driver: "นายมานพ สุภาพชน (พี่มานพ)", driver_id: "USR011", battery: 80, coords: "6.547835, 101.289502" },
        { id: "EV-10", name: "รถไฟฟ้าคันที่ 10", plate: "กค 9900 ยะลา", capacity_sit: 8, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV-10-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายอำนาจ ขับคล่อง (พี่อำนาจ)", driver_id: "USR012", battery: 80, coords: "6.547311, 101.288880" }
    ];

    function getStorage(key, defaultData) {
        if (!localStorage.getItem(key)) localStorage.setItem(key, JSON.stringify(defaultData));
        return JSON.parse(localStorage.getItem(key));
    }

    let trams = getStorage("yru_trams_v15", defaultTrams);
    if (!trams || trams.length < 10) {
        trams = defaultTrams;
    }
    // ตั้งค่าพิกัดรถคันที่ 1-10 ให้อยู่ประจำจุดจอดตามที่ร้องขอ
    trams.forEach(t => {
        if (t.id === "EV-01") t.coords = "6.549929, 101.291254"; // จุดจอด 1
        if (t.id === "EV-02") t.coords = "6.549100, 101.290467"; // จุดจอด 2
        if (t.id === "EV-03") t.coords = "6.547835, 101.289502"; // จุดจอด 3
        if (t.id === "EV-04") t.coords = "6.547224, 101.289471"; // จุดจอด 4
        if (t.id === "EV-05") t.coords = "6.547311, 101.288880"; // จุดจอด 5
        if (t.id === "EV-06") t.coords = "6.548822, 101.288523"; // จุดจอด 6
        if (t.id === "EV-07") t.coords = "6.549225, 101.289286"; // จุดจอด 7
        if (t.id === "EV-08") t.coords = "6.549929, 101.291254"; // จุดจอด 1
        if (t.id === "EV-09") t.coords = "6.547835, 101.289502"; // จุดจอด 3
        if (t.id === "EV-10") t.coords = "6.547311, 101.288880"; // จุดจอด 5
    });
    localStorage.setItem("yru_trams_v15", JSON.stringify(trams));

    // แสดงรถทั้งหมด 10 คันบนแผนที่
    const tramPositions = {
        '1': [6.549929, 101.291254], // จุดจอด 1
        '2': [6.549100, 101.290467], // จุดจอด 2
        '3': [6.547835, 101.289502], // จุดจอด 3
        '4': [6.547224, 101.289471], // จุดจอด 4
        '5': [6.547311, 101.288880], // จุดจอด 5
        '6': [6.548822, 101.288523], // จุดจอด 6
        '7': [6.549225, 101.289286], // จุดจอด 7
        '8': [6.549929, 101.291254], // จุดจอด 1
        '9': [6.547835, 101.289502], // จุดจอด 3
        '10': [6.547311, 101.288880]  // จุดจอด 5
    };

    const tramColors = {
        '1': { bg: 'bg-yellow-400', text: 'text-[#002d5e]', name: 'Yellow', dot: 'text-yellow-300' },
        '2': { bg: 'bg-purple-500', text: 'text-white', name: 'Purple', dot: 'text-purple-300' },
        '3': { bg: 'bg-orange-500', text: 'text-white', name: 'Orange', dot: 'text-orange-300' },
        '4': { bg: 'bg-indigo-500', text: 'text-white', name: 'Deep Purple', dot: 'text-indigo-300' },
        '5': { bg: 'bg-teal-500', text: 'text-white', name: 'Teal', dot: 'text-teal-300' },
        '6': { bg: 'bg-pink-500', text: 'text-white', name: 'Pink', dot: 'text-pink-300' },
        '7': { bg: 'bg-emerald-500', text: 'text-white', name: 'Emerald', dot: 'text-emerald-300' },
        '8': { bg: 'bg-blue-500', text: 'text-white', name: 'Blue', dot: 'text-blue-300' },
        '9': { bg: 'bg-red-500', text: 'text-white', name: 'Red', dot: 'text-red-300' },
        '10': { bg: 'bg-indigo-600', text: 'text-white', name: 'Indigo', dot: 'text-indigo-200' }
    };

    trams.forEach((tram) => {
        const carNum = tram.id.replace('EV-', '').replace(/^0+/, '');
        const pos = tramPositions[carNum] || [6.549929, 101.291254];
        const isCurrent = (currentCarId === carNum);
        const color = tramColors[carNum] || { bg: 'bg-slate-500', text: 'text-white', dot: 'text-slate-300' };

        const iconHtml = `
            <div class="relative flex flex-col items-center animate-fade-in-up" style="transform: translate(20px, -45px);">
                <div class="${color.bg} ${color.text} w-10 h-10 rounded-full flex items-center justify-center shadow-[0_5px_15px_rgba(0,0,0,0.25)] border-2 border-white ${isCurrent ? 'z-30' : 'z-20'} opacity-${isCurrent ? '100' : '80'}">
                    <i class="fas fa-bus-alt text-xl"></i>
                </div>
                <div class="bg-slate-800 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow border border-slate-600 mt-1 whitespace-nowrap ${isCurrent ? 'z-30' : 'z-20'}">
                    <span class="${color.dot} mr-1">●</span>
                    ${isCurrent ? 'คุณอยู่ที่นี่ (EV ' + carNum + ')' : 'รถอีกคัน (EV ' + carNum + ')'}
                </div>
            </div>
        `;
        const icon = L.divIcon({ className: 'custom-leaflet-icon', html: iconHtml, iconSize: [30, 30], iconAnchor: [15, 15] });
        L.marker(pos, { icon: icon, zIndexOffset: isCurrent ? 1000 : 500 }).addTo(map);
    });

    const driverPos = tramPositions[currentCarId] || [6.549929, 101.291254];
    map.setView(driverPos, 16.5);

    let currentOccupied = 0;
    const currentCar = trams.find(t => t.id === currentCarCode || t.id === `EV-${currentCarId}` || t.id === `EV-0${currentCarId}`);
    let maxCapacity = 8;
    if (currentCar) {
        maxCapacity = currentCar.capacity_sit || 8;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const capacityEl = document.getElementById('seats-max-capacity');
        if (capacityEl) capacityEl.innerText = maxCapacity;
        
        const codeEl = document.getElementById('car-display-code');
        if (codeEl) codeEl.innerText = currentCarCode;

        // Resolve driver name and route dynamically
        const trams = getStorage("yru_trams_v15", defaultTrams);
        const users = getStorage("yru_users_v6", defaultUsers);
        const currentCar = trams.find(t => t.id === currentCarCode || t.id === `EV-${currentCarId}` || t.id === `EV-0${currentCarId}`);
        
        let driverName = currentCarId === '2' ? "นายรักสงบ มั่นคง (น้าสงบ)" : "นายสมชาย ใจดี (เล่าปิง)";
        if (currentCar) {
            if (currentCar.driver_id) {
                const foundU = users.find(u => u.user_id === currentCar.driver_id);
                if (foundU) driverName = foundU.name;
            } else if (currentCar.driver) {
                driverName = currentCar.driver;
            }
        }
        
        const driverDisp = document.getElementById('driver-name-display');
        if (driverDisp) driverDisp.innerText = driverName;

        const routeDisp = document.getElementById('route-name-display');
        if (routeDisp) {
            if (currentCar && currentCar.route) {
                routeDisp.innerText = currentCar.route;
            } else {
                routeDisp.innerText = currentCarId === '2' ? 'สาย 2 (อาคาร 20-คณะมนุษย์)' : 'สาย 1 (เนินขาม-หอพัก)';
            }
        }

        updateNextStopUI();
    });

    window.changeSeatsOccupied = function(change) {
        currentOccupied = Math.max(0, Math.min(maxCapacity, currentOccupied + change));
        const disp = document.getElementById('seats-occupied-display');
        if(disp) disp.innerText = currentOccupied;

        const pct = Math.round((currentOccupied / maxCapacity) * 100);
        const pctEl = document.getElementById('seats-percentage');
        if (pctEl) pctEl.innerText = pct + '%';

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

    window.openIssueReportPrompt = function() {
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

    // 🚨 ส่งสัญญาณฉุกเฉิน SOS
    window.triggerSOSAlert = function() {
        if (confirm('🚨 คำเตือน: คุณต้องการส่งสัญญาณแจ้งเหตุฉุกเฉิน (SOS) ไปยังศูนย์ควบคุมใช่หรือไม่?')) {
            fetch('/api/trigger-sos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ car_id: currentCarCode })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('🚨 ส่งสัญญาณ SOS และแจ้งเตือนเหตุฉุกเฉินไปยังแผงควบคุมแอดมินสำเร็จ!');
                }
            })
            .catch(err => {
                console.error('Error sending SOS:', err);
                alert('เกิดข้อผิดพลาดในการส่งข้อมูลฉุกเฉิน');
            });
        }
    }

    // 🌟 ฟังก์ชันจัดการสถานะคนขับ (รวมการเปลี่ยนคลาส UI และยิง API)
    window.syncDriverStatus = function(statusType) {
        const items = document.querySelectorAll('.status-item');
        items.forEach(i => {
            if(i.getAttribute('data-type') === statusType) {
                i.classList.add('active');
            } else {
                i.classList.remove('active');
            }
        });

        const gpsStatus = document.getElementById('gps-status-text');

        // อัปเดตข้อความแถบแสดงผลฝั่งคนขับ
        if(statusType === 'normal') {
            gpsStatus.innerHTML = `● ออนไลน์`;
            gpsStatus.style.color = "#10b981";
        } else if(statusType === 'pause') {
            gpsStatus.innerHTML = `● หยุดพักชั่วคราว`;
            gpsStatus.style.color = "#f59e0b";
        } else if(statusType === 'broken') {
            gpsStatus.innerHTML = `● ออฟไลน์ (รถเสีย)`;
            gpsStatus.style.color = "#ef4444";
        }

        // อัปเดตส่วนหัวของเว็บแบบไดนามิก
        const badgeContainer = document.getElementById('status-badge-container');
        if (badgeContainer) {
            if (statusType === 'normal') {
                badgeContainer.innerHTML = `
                    <span id="status-badge" class="bg-green-500/10 border border-green-500/20 text-green-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-circle text-green-500 text-[8px] animate-pulse"></i> กำลังเดินรถ
                    </span>
                `;
            } else if (statusType === 'pause') {
                badgeContainer.innerHTML = `
                    <span id="status-badge" class="bg-amber-500/10 border border-amber-500/20 text-amber-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-circle text-amber-500 text-[8px] animate-pulse"></i> หยุดพัก
                    </span>
                `;
            } else if (statusType === 'broken') {
                badgeContainer.innerHTML = `
                    <span id="status-badge" class="bg-red-500/10 border border-red-500/20 text-red-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-circle text-red-500 text-[8px] animate-pulse"></i> รถเสีย
                    </span>
                `;
            }
        }

        fetch('/api/update-driver-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: statusType,
                start_time: '-'
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
    window.endCurrentRound = function() {
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
                
                const gpsStatus = document.getElementById('gps-status-text');
                if (gpsStatus) {
                    gpsStatus.innerHTML = `● สิ้นสุดรอบ (รอเปิดรอบใหม่)`;
                    gpsStatus.style.color = "#475569";
                }
                
                const badgeContainer = document.getElementById('status-badge-container');
                if (badgeContainer) {
                    badgeContainer.innerHTML = `
                        <span id="status-badge" class="bg-slate-500/10 border border-slate-500/20 text-slate-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                            <i class="fas fa-circle text-slate-500 text-[8px]"></i> สิ้นสุดรอบวิ่ง
                        </span>
                    `;
                }

                document.querySelectorAll('.status-item').forEach(i => i.classList.remove('active'));
            })
            .catch(error => {
                console.error('Error ending round:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อระบบ ไม่สามารถสิ้นสุดรอบได้');
            });
        }
    }

    // 📡 --- ส่วนงาน Logic จุดจอดและ Polling สายเรียกเข้าแบบ Real-time ---
    let currentStopIndex = 0;
    window.lastNotifiedStopIndex = -1;
    window.latestPassengerCallData = null;

    window.updateNextStopUI = function() {
        const nextStop = stationData[currentStopIndex];
        document.getElementById('next-stop-name').innerText = nextStop.name;
        document.getElementById('next-stop-sequence').innerText = 'STOP ' + (currentStopIndex + 1) + '/' + stationData.length;
        
        // เลื่อนแผนที่ไปโฟกัสจุดจอดถัดไป
        map.setView([nextStop.lat, nextStop.lng], 17.5);
        
        updateCallAlertUI();
    }

    window.updateCallAlertUI = function() {
        const data = window.latestPassengerCallData;
        const alertEl = document.getElementById('next-stop-alert');
        const noAlertEl = document.getElementById('next-stop-no-pax');
        const nextStop = stationData[currentStopIndex];
        const localPax = waitingPassengers[nextStop.id] || 0;
        
        if (data && data.station) {
            // สำหรับเดโมการนำเสนอ ให้แสดงทุกคำขอเรียกรถของทุกคันรถ
            const isForThisCar = true;
            
            if (isForThisCar) {
                document.getElementById('call-alert-station').innerText = data.station;
                document.getElementById('call-alert-destination').innerText = data.destination || 'ไม่ได้ระบุ';
                document.getElementById('call-alert-pax').innerText = data.pax + ' คน';
                document.getElementById('call-alert-time').innerText = (data.time ? data.time.slice(0, 5) : '') + ' น.';
                
                // เรนเดอร์ปุ่มตามสถานะการตอบรับ
                const btnContainer = document.getElementById('call-alert-btn-container');
                if (btnContainer) {
                    if (data.status === 'accepted') {
                        btnContainer.innerHTML = `
                            <button onclick="clearPassengerCall()" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 rounded-lg text-xs transition active:scale-95 flex items-center justify-center gap-1.5 mt-1 shadow-md">
                                <i class="fas fa-check-circle"></i> จอดรับเรียบร้อย
                            </button>
                        `;
                    } else {
                        btnContainer.innerHTML = `
                            <button onclick="acceptPassengerCall()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg text-xs transition active:scale-95 flex items-center justify-center gap-1.5 mt-1 shadow-md animate-bounce">
                                <i class="fas fa-check"></i> ตอบรับ (กำลังไป)
                            </button>
                        `;
                    }
                }
                
                if (alertEl) alertEl.classList.remove('hidden');
                if (noAlertEl) noAlertEl.classList.add('hidden');
                return;
            }
        }
        
        if (alertEl) alertEl.classList.add('hidden');
        if (noAlertEl) {
            noAlertEl.classList.remove('hidden');
            if (localPax > 0) {
                // มีคนรอ: เด้งสีเหลือง/ส้มไฮไลต์เด่นชัดพร้อมไอคอนกระดิ่งสั่น
                noAlertEl.className = "bg-amber-500/10 border border-amber-500/30 text-amber-300 rounded-xl p-3 flex items-center gap-2 text-xs font-bold animate-pulse";
                noAlertEl.innerHTML = `<i class="fas fa-bell text-amber-400 fa-shake mr-1 text-[13px]"></i> มีผู้โดยสารรอ ${localPax} คน`;
            } else {
                // ไม่มีคนรอ: แสดงแถบสีเทาเรียบๆ
                noAlertEl.className = "bg-slate-800/40 border border-slate-700/60 text-slate-500 rounded-xl p-3 flex items-center gap-2 text-xs";
                noAlertEl.innerHTML = `<i class="fas fa-check-circle text-slate-600 mr-1"></i> ไม่มีผู้โดยสารรอรถที่จุดนี้`;
            }
        }
    }

    window.acceptPassengerCall = function() {
        fetch('/api/accept-ev-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (window.latestPassengerCallData) {
                    window.latestPassengerCallData.status = 'accepted';
                }
                updateCallAlertUI();
            }
        })
        .catch(err => console.error('Error accepting call:', err));
    }

    window.handleArrivedAtStop = function() {
        const nextStop = stationData[currentStopIndex];
        
        // หากผู้โดยสารกดเรียกรถที่จุดจอดนี้ ให้ถือว่ารับขึ้นรถ และล้างคิวแจ้งเตือน
        if (window.latestPassengerCallData && window.latestPassengerCallData.station === nextStop.name) {
            clearPassengerCall();
        }
        
        // เดินหน้าไปยังสถานีถัดไป
        currentStopIndex = (currentStopIndex + 1) % stationData.length;
        updateNextStopUI();
    }

    window.handleSkipStop = function() {
        currentStopIndex = (currentStopIndex + 1) % stationData.length;
        updateNextStopUI();
    }

    let lastRequestTime = null; 

    window.checkPassengerCalls = function() {
        fetch('/api/check-ev-request?t=' + new Date().getTime())
        .then(response => {
            if (!response.ok) throw new Error('API server unavailable');
            return response.json();
        })
        .then(data => {
            window.latestPassengerCallData = data;
            
            if (data && data.station) {
                window.pendingPax = parseInt(data.pax) || 0;
                
                // สำหรับเดโมการนำเสนอ ให้แสดงทุกคำขอเรียกรถของทุกคันรถ
                const isForThisCar = true;
                
                if (isForThisCar && lastRequestTime !== data.time) {
                    lastRequestTime = data.time;
                    playAlertSound();
                }
            }
            
            updateCallAlertUI();
        })
        .catch(error => console.error('Error fetching driver tracking data:', error));
    }

    window.clearPassengerCall = function() {
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
                window.latestPassengerCallData = null;
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