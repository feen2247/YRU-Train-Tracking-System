<?php

$file = __DIR__.'/resources/views/passenger/tracking.blade.php';
$content = file_get_contents($file);

// Add tailwind and leaflet
$head_add = <<<HTML
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
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
HTML;

$content = str_replace('</head>', $head_add . "\n</head>", $content);

// Update CSS for body and driver-container
$content = str_replace(
    'body {
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 15px;
            display: flex;
            justify-content: center;
        }',
    'body {
            font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: row;
            height: 100vh;
            overflow: hidden;
        }',
    $content
);

$content = str_replace(
    '.driver-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }',
    '.driver-container {
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
        }',
    $content
);

// Inject map div
$map_html = <<<HTML
    <div id="map" style="flex: 1; height: 100%; position: relative; z-index: 1;"></div>
HTML;

$content = str_replace('</div>

<script>', '</div>' . "\n" . $map_html . "\n" . '<script>', $content);

// Inject map js logic inside <script>
$map_js = <<<JS
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
                <div class="\${pulseClass} bg-white border-2 \${borderColorClass} w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                    <div class="w-2 h-2 \${dotColorClass} rounded-full"></div>
                </div>
                <div class="\${badgeBgClass} backdrop-blur-sm text-[10px] font-bold px-2 py-0.5 rounded-full shadow border \${textColorClass} mt-0.5 whitespace-nowrap">
                    <i class="fas fa-users mr-1"></i>รอ \${pax} คน
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
                <h4 class="font-bold text-slate-800 text-sm mb-1">\${st.name}</h4>
                <p class="text-xs font-semibold \${textColorClass}">ผู้โดยสารรอรถ: \${pax} คน</p>
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
        color: '#3b82f6', // สีน้ำเงินสำหรับคนขับจะได้เห็นชัดๆ
        weight: 4,
        opacity: 0.8,
        dashArray: '10, 10'
    }).addTo(map);

    setTimeout(() => { map.invalidateSize(); }, 500);

JS;

$content = str_replace('const urlParams = new URLSearchParams(window.location.search);', $map_js . "\n\n    const urlParams = new URLSearchParams(window.location.search);", $content);

file_put_contents($file, $content);
echo "Done";

