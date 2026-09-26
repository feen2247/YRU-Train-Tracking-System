    // ===== 🎨 Vehicle Color Palette — 10 สีสำหรับรถแต่ละคัน (ตรงกับหน้าผู้ใช้) =====
    const vehicleColors = [
        { bg: '#3B82F6', text: '#ffffff', name: 'Blue',    light: '#DBEAFE', border: '#93C5FD' },  // EV-01
        { bg: '#EC4899', text: '#ffffff', name: 'Pink',    light: '#FCE7F3', border: '#F9A8D4' },  // EV-02
        { bg: '#EA580C', text: '#ffffff', name: 'Orange',  light: '#FFEDD5', border: '#FB923C' },  // EV-03
        { bg: '#8B5CF6', text: '#ffffff', name: 'Purple',  light: '#EDE9FE', border: '#C4B5FD' },  // EV-04
        { bg: '#14B8A6', text: '#ffffff', name: 'Teal',    light: '#CCFBF1', border: '#5EEAD4' },  // EV-05
        { bg: '#EF4444', text: '#ffffff', name: 'Red',     light: '#FEE2E2', border: '#FCA5A5' },  // EV-06
        { bg: '#10B981', text: '#ffffff', name: 'Emerald', light: '#D1FAE5', border: '#A7F3D0' },  // EV-07
        { bg: '#6366F1', text: '#ffffff', name: 'Indigo',  light: '#E0E7FF', border: '#C7D2FE' },  // EV-08
        { bg: '#06B6D4', text: '#ffffff', name: 'Cyan',    light: '#CFFAFE', border: '#67E8F9' },  // EV-09
        { bg: '#F43F5E', text: '#ffffff', name: 'Rose',    light: '#FFE4E6', border: '#FDA4AF' }   // EV-10
    ];

    function getVehicleColor(idx) {
        return vehicleColors[idx % vehicleColors.length];
    }

    var GARAGE_LAT = 6.548900;
    var GARAGE_LNG = 101.291700;
    var GARAGE_STATION_ID = "GARAGE";
    let globalCarStatus = {};

    function isVehicleInGarage(tram) {
        if (!tram) return false;
        var carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
        var rawStatus = (tram.status || carData.status || '').toString();
        var stationId = (tram.current_station_id || '').toString();
        
        var isMaintenanceOrSuspended = 
            rawStatus.includes('ขัดข้อง') || 
            rawStatus.includes('ปรับปรุง') || 
            rawStatus.includes('หยุดพัก') || 
            rawStatus.includes('ระงับ') || 
            rawStatus.includes('สิ้นสุด') ||
            rawStatus === 'MAINTENANCE' || 
            rawStatus === 'SUSPENDED' ||
            rawStatus === 'Inactive' ||
            rawStatus === 'ended';

        var isAtGarage = (stationId === 'GARAGE' || stationId === 'GARAGE_STATION');
        if (!isAtGarage && tram.coords) {
            var parts = tram.coords.split(',').map(Number);
            if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                var dist = Math.hypot(parts[0] - GARAGE_LAT, parts[1] - GARAGE_LNG);
                if (dist < 0.001) {
                    isAtGarage = true;
                }
            }
        }

        return isMaintenanceOrSuspended || isAtGarage;
    }

    const urlParams = new URLSearchParams(window.location.search);
    let paramCar = urlParams.get('car_id') || urlParams.get('car') || '';
    let defaultCarCode = "{{ $mappedCar }}";

    const currentCarCode = paramCar ? (paramCar.startsWith('EV-') ? paramCar : (parseInt(paramCar) < 10 ? 'EV-0' + parseInt(paramCar) : 'EV-' + parseInt(paramCar))) : defaultCarCode;
    const currentCarId = parseInt(currentCarCode.replace('EV-', '')) || 1;

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
        { user_id: "USR010", name: "นายสมใจ ใจดี", email: "somjai@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR011", name: "นายกิตติ ตั้งใจ", email: "kitti@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR012", name: "นายรุสลัน สอเฮาะ", email: "ruslan@yru.ac.th", role: "driver", status: "ใช้งาน" }
    ];

    const defaultTrams = [
        { id: "EV-01", name: "รถไฟฟ้าคันที่ 1", plate: "กค 1234 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV01-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายอัสมี มูเล็ง", driver_id: "USR003", battery: 85, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-12", warranty: "5 ปี (สิ้นสุด 12 มีนาคม 2573)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)", maintenance: [{ date: "05/07/2569", detail: "เช็คระยะระบบขับเคลื่อน และทดสอบไฟชาร์จแบตเตอรี่ (ผลการทดสอบ: ปกติ)", technician: "ช่างประสาน" }, { date: "28/06/2569", detail: "เปลี่ยนผ้าเบรกหน้า-หลัง และเปลี่ยนยางรถไฟฟ้าใหม่ 4 ล้อ", technician: "ช่างสมคิด" }] },
        { id: "EV-02", name: "รถไฟฟ้าคันที่ 2", plate: "กค 5678 ยะลา", capacity_sit: 16, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV02-YRU", route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", driver: "นายอัรฟาน มะเระ", driver_id: "USR004", battery: 92, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-01-01", warranty: "5 ปี (สิ้นสุด 1 มกราคม 2573)", supplier: "บริษัท อันดามัน เทคโนโลยี จำกัด (โทร. 076-987654)", maintenance: [{ date: "01/07/2569", detail: "ตรวจเช็คระดับน้ำกลั่นแบตเตอรี่สำรอง และทำความสะอาดขั้วต่อกระแสไฟ", technician: "ช่างประสาน" }, { date: "15/06/2569", detail: "เปลี่ยนน้ำมันเกียร์ไฟฟ้า และขันน็อตช่วงล่างทุกตัวเพื่อความปลอดภัย", technician: "ช่างสมคิด" }] },
        { id: "EV-03", name: "รถไฟฟ้าคันที่ 3", plate: "กค 9012 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV03-YRU", route: "สาย 3: ประตูหลังมอ-หอประชุม", driver: "นายซูเฟียน มะโอะ", driver_id: "USR005", battery: 88, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2024-10-15", warranty: "3 ปี (สิ้นสุด 15 ตุลาคม 2570)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด (โทร. 081-2345678)", maintenance: [{ date: "06/07/2569", detail: "ทำความสะอาดตัวกรองระบายความร้อนบอร์ดควบคุมหม้อแปลงไฟฟ้า", technician: "ช่างวิรัช" }, { date: "24/06/2569", detail: "เปลี่ยนยางหน้าขวา 1 เส้น เนื่องจากขับเบียดขอบทางเดินเท้า", technician: "ช่างสมคิด" }] },
        { id: "EV-04", name: "รถไฟฟ้าคันที่ 4", plate: "กค 3456 ยะลา", capacity_sit: 18, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV04-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายอุสมาน สาและ", driver_id: "USR006", battery: 78, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2024-05-20", warranty: "3 ปี (สิ้นสุด 20 พฤษภาคม 2570)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด (โทร. 081-2345678)", maintenance: [{ date: "04/07/2569", detail: "ตรวจเช็คระบบไฟฟ้า สัญญานแตร และไฟหน้า-ไฟเลี้ยวรอบคัน (ผ่านเกณฑ์)", technician: "ช่างวิรัช" }, { date: "20/06/2569", detail: "เปลี่ยนสปริงโช้คอัพหลังซ้าย-ขวา เพื่อรองรับน้ำหนักผู้โดยสารได้ดีขึ้น", technician: "ช่างสมคิด" }] },
        { id: "EV-05", name: "รถไฟฟ้าคันที่ 5", plate: "กค 7890 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV05-YRU", route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", driver: "นายบัดรี สาและ", driver_id: "USR007", battery: 95, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-01-10", warranty: "3 ปี (สิ้นสุด 10 มกราคม 2571)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด", maintenance: [] },
        { id: "EV-06", name: "รถไฟฟ้าคันที่ 6", plate: "กค 1122 ยะลา", capacity_sit: 16, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV06-YRU", route: "สาย 3: ประตูหลังมอ-หอประชุม", driver: "นายตอรริก ลือแมะ", driver_id: "USR008", battery: 89, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-02-15", warranty: "3 ปี (สิ้นสุด 15 กุมภาพันธ์ 2571)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด", maintenance: [] },
        { id: "EV-07", name: "รถไฟฟ้าคันที่ 7", plate: "กค 3344 ยะลา", capacity_sit: 18, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV07-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายสมหวัง ใจดี", driver_id: "USR009", battery: 82, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-01", warranty: "3 ปี (สิ้นสุด 1 มีนาคม 2571)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด", maintenance: [] },
        { id: "EV-08", name: "รถไฟฟ้าคันที่ 8", plate: "กค 5566 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV08-YRU", route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", driver: "นายสมใจ ใจดี", driver_id: "USR010", battery: 90, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-20", warranty: "3 ปี (สิ้นสุด 20 มีนาคม 2571)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด", maintenance: [] },
        { id: "EV-09", name: "รถไฟฟ้าคันที่ 9", plate: "กค 7788 ยะลา", capacity_sit: 16, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV09-YRU", route: "สาย 3: ประตูหลังมอ-หอประชุม", driver: "นายกิตติ ตั้งใจ", driver_id: "USR011", battery: 87, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-04-05", warranty: "3 ปี (สิ้นสุด 5 เมษายน 2571)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด", maintenance: [] },
        { id: "EV-10", name: "รถไฟฟ้าคันที่ 10", plate: "กค 9900 ยะลา", capacity_sit: 18, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV10-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายรุสลัน สอเฮาะ", driver_id: "USR012", battery: 91, image: "", coords: "6.548900, 101.291700", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-05-10", warranty: "3 ปี (สิ้นสุด 10 พฤษภาคม 2571)", supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด", maintenance: [] }
    ];

    function getStorage(key, defaultData) {
        if (key.startsWith("yru_trams")) {
            const v16 = localStorage.getItem("yru_trams_v16");
            if (v16) return JSON.parse(v16);
            const v15 = localStorage.getItem("yru_trams_v15");
            if (v15) return JSON.parse(v15);
        }
        if (!localStorage.getItem(key)) localStorage.setItem(key, JSON.stringify(defaultData));
        return JSON.parse(localStorage.getItem(key));
    }

    let trams = getStorage("yru_trams_v16", defaultTrams);
    if (!trams || trams.length < 10) {
        trams = defaultTrams;
    }

    const baseVehicleIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
    const defaultTramCoords = {
        "EV-01": "6.549929, 101.291254", // จุดจอด 1
        "EV-02": "6.549100, 101.290467", // จุดจอด 2
        "EV-03": "6.547835, 101.289502", // จุดจอด 3
        "EV-04": "6.547224, 101.289471", // จุดจอด 4
        "EV-05": "6.547311, 101.288880", // จุดจอด 5
        "EV-06": "6.548822, 101.288523", // จุดจอด 6
        "EV-07": "6.549225, 101.289286", // จุดจอด 7
        "EV-08": "6.549929, 101.291254", // จุดจอด 1 (คันที่ 8)
        "EV-09": "6.547835, 101.289502", // จุดจอด 3 (คันที่ 9)
        "EV-10": "6.547311, 101.288880"  // จุดจอด 5 (คันที่ 10)
    };
    const GARAGE_COORDS = "6.548900, 101.291700";

    trams.forEach(t => {
        const isBase = baseVehicleIds.includes(t.id);
        const isSuspended = (t.status === "รถขัดข้อง" || t.status === "ระงับการใช้งาน" || t.status === "MAINTENANCE" || t.status === "SUSPENDED");

        if (isSuspended) {
            t.coords = GARAGE_COORDS;
            t.current_station_id = "GARAGE";
        } else if (!isBase) {
            if (!t.coords || t.coords === GARAGE_COORDS) {
                t.coords = GARAGE_COORDS;
            }
            t.current_station_id = "GARAGE";
        } else {
            if (!t.coords || t.coords === GARAGE_COORDS || t.coords === "6.548729, 101.290583" || t.coords === "6.549418, 101.291186" || t.coords === "6.549663, 101.291412") {
                t.coords = defaultTramCoords[t.id] || GARAGE_COORDS;
            }
        }
    });
    localStorage.setItem("yru_trams_v16", JSON.stringify(trams));

    // 🗺️ Leaflet Map Initialization centered at YRU campus (identical to Passenger Home Screen)
    let map = null;
    try {
        if (typeof L !== 'undefined') {
            map = L.map('map', {
                zoomControl: false,
                zoomSnap: 0.1,
                minZoom: 15,
                maxZoom: 20,
                maxBounds: [
                    [6.535, 101.275],  // SW bound
                    [6.563, 101.305]   // NE bound
                ],
                maxBoundsViscosity: 0.8
            }).setView([6.548600, 101.289700], 16.7);

            // Define base layers
            const googleRoadmap = L.tileLayer('https://mt1.google.com/vt/lyrs=r&apistyle=s.t:0|s.e:l|p.v:off,s.t:21|p.v:off,s.t:20|p.v:off&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps'
            });

            const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps'
            });

            const googleHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps'
            });

            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 20,
                attribution: '&copy; OpenStreetMap'
            });

            // Add default layer to map
            googleRoadmap.addTo(map);

            const baseMaps = {
                "แผนที่ปกติ (Google)": googleRoadmap,
                "ดาวเทียม": googleSatellite,
                "ดาวเทียม (มีชื่อถนน)": googleHybrid,
                "แผนที่ OSM": osmLayer
            };

            // Zoom controls
            L.control.zoom({ position: 'topright' }).addTo(map);

            // Add layer control
            L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);

            // 🏫 University label control (top-left) - Modern glassmorphism
            const uniLabel = L.control({ position: 'topleft' });
            uniLabel.onAdd = function() {
                const div = L.DomUtil.create('div', '');
                div.innerHTML = `
                    <div style="
                        background: linear-gradient(135deg, rgba(244,114,182,0.9), rgba(236,72,153,0.9));
                        backdrop-filter: blur(10px);
                        color: white;
                        padding: 7px 14px;
                        border-radius: 24px;
                        font-family: 'Kanit', sans-serif;
                        font-size: 11px;
                        font-weight: 600;
                        box-shadow: 0 4px 15px rgba(236,72,153,0.25);
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        white-space: nowrap;
                        user-select: none;
                        pointer-events: none;
                        margin: 10px;
                        border: 1px solid rgba(255,255,255,0.1);
                    ">
                        <span style="font-size:13px;">🏫</span>
                        <span>มหาวิทยาลัยราชภัฏยะลา</span>
                    </div>
                `;
                return div;
            };
            uniLabel.addTo(map);
        }
    } catch(e) {
        console.warn("Leaflet map initialization warning:", e);
    }

    const defaultStationData = [
        { id: 1, name: "ประตูหลังมอ.", lat: 6.549929, lng: 101.291254, type: "P", status: "จอดอยู่", time: "Now" },
        { id: 2, name: "ตึกศิลปะ", lat: 6.549100, lng: 101.290467, type: "P", status: "รอถัดไป", time: "5 นาที" },
        { id: 3, name: "ศูนย์วิทยาศาสตร์", lat: 6.547835, lng: 101.289502, type: "P", status: "ถัดไป", time: "10 นาที" },
        { id: 4, name: "คณะวิทยาศาสตร์", lat: 6.547224, lng: 101.289471, type: "P", status: "ถัดไป", time: "15 นาที" },
        { id: 5, name: "สังคมศาสตร์", lat: 6.547311, lng: 101.288880, type: "P", status: "จอดอยู่", time: "Now" },
        { id: 6, name: "อาคารเรียน20", lat: 6.548822, lng: 101.288523, type: "P", status: "รอถัดไป", time: "5 นาที" },
        { id: 7, name: "วิทยาการจัดการ", lat: 6.549225, lng: 101.289286, type: "P", status: "ถัดไป", time: "10 นาที" }
    ];

    // Auto-fit map bounds so all 7 stations & garage are fully visible, centered & expanded nicely
    function fitMapBounds() {
        if (!map || typeof L === 'undefined') return;
        const dataToUse = (typeof stationData !== 'undefined' && stationData && stationData.length > 0) ? stationData : defaultStationData;
        const points = dataToUse.map(s => [s.lat, s.lng]);
        points.push([6.548900, 101.291700]); // Garage coords
        const bounds = L.latLngBounds(points);
        map.fitBounds(bounds, { 
            paddingTopLeft: [40, 40],
            paddingBottomRight: [40, 40],
            maxZoom: 17.6
        });
    }

    // Force map to render correctly and auto-fit
    setTimeout(() => { 
        if (map) {
            map.invalidateSize(); 
            fitMapBounds();
        }
    }, 400);

    let stationData = defaultStationData;
    let stationMarkers = {};

    function loadDynamicStationData() {
        const localStops = JSON.parse(localStorage.getItem("yru_stops_v2") || "[]");
        if (localStops.length === 0) {
            stationData = defaultStationData;
            return;
        }

        stationData = localStops.map((stop, index) => {
            const seq = stop.sequence || (index + 1);
            let timeStr = "";
            let statusStr = "";
            if (seq === 1 || seq === 5) {
                timeStr = "Now";
                statusStr = "จอดอยู่";
            } else if (seq === 2 || seq === 6) {
                timeStr = "5 นาที";
                statusStr = "รอถัดไป";
            } else {
                timeStr = `${(seq % 3 + 1) * 5} นาที`;
                statusStr = "ถัดไป";
            }

            return {
                id: seq,
                name: stop.name,
                lat: parseFloat(stop.lat),
                lng: parseFloat(stop.lng),
                type: "P",
                status: statusStr,
                time: timeStr
            };
        });
    }

    // Add Station markers — Concentric dot pin with จุดจอด label matching Passenger Home
    function drawStationMarkers() {
        if (!map || typeof L === 'undefined') return;
        stationData.forEach(st => {
            const markerHtml = `
                <div class="flex flex-col items-center" style="transform: translate(-20px, -20px);">
                    <div class="station-dot bg-white border-2 border-pink-600 w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                        <div class="w-2 h-2 bg-pink-600 rounded-full"></div>
                    </div>
                    <div class="bg-white/95 backdrop-blur-sm text-[9px] font-bold px-2 py-0.5 rounded-full shadow border border-pink-100 text-pink-700 mt-0.5 whitespace-nowrap">
                        <i class="fas fa-parking text-pink-600 mr-0.5"></i>จุดจอด ${st.id}
                    </div>
                </div>
            `;

            const icon = L.divIcon({
                className: 'custom-leaflet-icon',
                html: markerHtml,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            const popupHtml = `
                <div class="p-2 font-kanit">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">จุดจอดรถไฟฟ้าที่ ${st.id}</h4>
                    <p class="text-xs text-slate-700 font-semibold mb-1">${st.name}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-xs px-2 py-0.5 rounded-full ${st.status === 'จอดอยู่' ? 'bg-green-100 text-green-700' : st.status === 'กำลังมา' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'} font-bold">
                            ${st.status}
                        </span>
                        <span class="text-xs text-slate-500 font-medium">
                            เวลารอ: ${st.time}
                        </span>
                    </div>
                </div>
            `;

            if (!stationMarkers[st.id]) {
                stationMarkers[st.id] = L.marker([st.lat, st.lng], { icon: icon, zIndexOffset: 2500 }).addTo(map)
                    .bindPopup(popupHtml);
            } else {
                stationMarkers[st.id].getPopup().setContent(popupHtml);
            }
        });
    }

    loadDynamicStationData();
    drawStationMarkers();

    // Fallback static polyline route loop connecting stations
    const fallbackRouteCoords = [
        [6.549929, 101.291254],
        [6.549100, 101.290467],
        [6.547835, 101.289502],
        [6.547568, 101.289698],
        [6.547224, 101.289471],
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

    let activeRouteLines = [];

    function drawRouteLine(coords, color) {
        if (!map || typeof L === 'undefined' || !coords || coords.length < 2) return;
        const line = L.polyline(coords, {
            color: color || '#ec4899',
            weight: 5,
            opacity: 0.75,
            dashArray: '10, 10'
        }).addTo(map);
        activeRouteLines.push(line);
    }

    function clearAllRouteLines() {
        activeRouteLines.forEach(line => {
            if (map && line) map.removeLayer(line);
        });
        activeRouteLines = [];
    }

    // ⚡ Instant Route Line Renderer (0ms delay)
    function renderRouteInstant() {
        clearAllRouteLines();
        const rawStorage = localStorage.getItem("yru_routes_v1");
        if (rawStorage === null) {
            // Only draw fallback route if system has NEVER initialized routes before
            drawRouteLine(fallbackRouteCoords, '#ec4899');
            return;
        }

        try {
            const localRoutes = JSON.parse(rawStorage || "[]");
            if (Array.isArray(localRoutes) && localRoutes.length > 0) {
                localRoutes.forEach(route => {
                    let coords = route.polyline_data;
                    if (typeof coords === 'string') {
                        try { coords = JSON.parse(coords); } catch(e) { coords = []; }
                    }
                    if (coords && coords.length >= 2) {
                        drawRouteLine(coords, route.color || route.route_color || '#ec4899');
                    }
                });
            }
        } catch (e) {}
    }

    // Dynamic Route Loader (Instant 0ms render + Background API Sync + Realtime Popup)
    let lastKnownRouteSignature = null;
    let isInitialRouteLoad = true;

    function notifyRouteUpdate(actionText) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: '🚌 อัปเดตเส้นทางเดินรถ',
                text: actionText || 'แอดมินได้ทำการเพิ่ม/แก้ไข/ลบเส้นทางเดินรถใหม่ในระบบแล้ว',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        }
    }

    async function loadDynamicRoute(forceNotifyAction) {
        renderRouteInstant();

        try {
            const response = await fetch('/api/routes?v=' + Date.now());
            if (response.ok) {
                const apiRoutes = await response.json();
                if (apiRoutes && Array.isArray(apiRoutes)) {
                    // Sync latest routes to localStorage
                    localStorage.setItem("yru_routes_v1", JSON.stringify(apiRoutes));

                    const currentSig = JSON.stringify(apiRoutes.map(r => ({ c: r.route_code, color: r.route_color || r.color, pts: (r.polyline_data || []).length })));
                    
                    if (currentSig !== lastKnownRouteSignature) {
                        clearAllRouteLines();
                        let allRouteCoords = [];
                        apiRoutes.forEach(route => {
                            let coords = route.polyline_data;
                            if (typeof coords === 'string') {
                                try { coords = JSON.parse(coords); } catch(e) { coords = []; }
                            }
                            if (coords && coords.length >= 2) {
                                drawRouteLine(coords, route.color || route.route_color || '#ec4899');
                                coords.forEach(pt => allRouteCoords.push(pt));
                            }
                        });

                        // Auto-bounce & fit bounds to updated route map if routes exist
                        if (!isInitialRouteLoad && allRouteCoords.length > 0 && typeof map !== 'undefined' && map && typeof map.flyToBounds === 'function') {
                            try {
                                const routeBounds = L.latLngBounds(allRouteCoords);
                                map.flyToBounds(routeBounds, { padding: [50, 50], maxZoom: 16.5, duration: 1.2 });
                            } catch(e) {}
                        }

                        if (!isInitialRouteLoad || forceNotifyAction) {
                            notifyRouteUpdate(forceNotifyAction);
                        }

                        lastKnownRouteSignature = currentSig;
                    }
                }
            }
        } catch (e) {
            console.error("API route sync error:", e);
        } finally {
            isInitialRouteLoad = false;
        }
    }

    loadDynamicRoute();

    // Real-time BroadcastChannel sync across open tabs
    try {
        const routeSyncChannel = new BroadcastChannel('yru_routes_realtime_sync');
        routeSyncChannel.onmessage = function(ev) {
            if (ev.data && ev.data.type === 'route_updated') {
                const actMsg = ev.data.action === 'delete' ? 'แอดมินได้ลบเส้นทางเดินรถออกจากระบบ' : (ev.data.action === 'add' ? 'แอดมินได้เพิ่มเส้นทางเดินรถใหม่ในระบบ' : 'แอดมินได้แก้ไขเส้นทางเดินรถในระบบ');
                loadDynamicRoute(actMsg);
            }
        };
    } catch(e) {}

    // Listen to real-time storage & broadcast updates from Admin tab
    try {
        const tramSyncChannel = new BroadcastChannel('yru_trams_realtime_sync');
        tramSyncChannel.onmessage = (event) => {
            if (typeof syncDriverVehicleData === 'function') syncDriverVehicleData();
            if (typeof initTramMarkers === 'function') initTramMarkers();
            if (typeof checkAdminVehicleLockStatus === 'function') checkAdminVehicleLockStatus();
        };
    } catch(e) {}

    // ✅ Debounced storage listener — ป้องกัน rapid-fire จาก home.blade.php
    let _storageDebounceTimer = null;
    window.addEventListener('storage', (event) => {
        const key = event ? event.key : null;
        // กรองเฉพาะ key ที่เกี่ยวข้อง
        const isRelevant = !key || key === 'yru_stops_v2' || key === 'yru_trams_v16' ||
            key === 'yru_latest_call' || key === 'yru_call_queue' ||
            (key && key.startsWith('yru_car_status_'));
        const isRouteKey = key === 'yru_routes_v1' || key === 'yru_routes_last_updated';

        if (!isRelevant && !isRouteKey) return; // ข้าม key ที่ไม่เกี่ยวข้อง

        // Debounce: รอให้ events หยุดแล้วค่อย fire 1 ครั้ง
        clearTimeout(_storageDebounceTimer);
        _storageDebounceTimer = setTimeout(() => {
            if (isRelevant) {
                // เรียกเฉพาะ lightweight functions ใน storage listener
                if (typeof syncDriverVehicleData === 'function') syncDriverVehicleData();
                if (typeof checkPassengerCalls === 'function') checkPassengerCalls();
                if (typeof checkAdminVehicleLockStatus === 'function') checkAdminVehicleLockStatus();
                // Heavy DOM ops (initTramMarkers, drawStationMarkers) ทำงานจาก setInterval แทน
                debouncedInitTramMarkers();
            }
            if (isRouteKey) {
                if (typeof loadDynamicRoute === 'function') loadDynamicRoute();
            }
        }, 500); // รอ 500ms หลังจาก storage event สุดท้าย
    });

    // Real-time polling to ensure Admin status and vehicle changes sync immediately to driver UI
    // ⚡ เพิ่มจาก 2000ms → 5000ms เพื่อลด CPU usage และลด DOM update ที่ไม่จำเป็น
    setInterval(() => {
        if (typeof syncDriverVehicleData === 'function') {
            syncDriverVehicleData();
        }
        if (typeof checkAdminVehicleLockStatus === 'function') {
            checkAdminVehicleLockStatus();
        }
    }, 5000);

    // ===== 🏠 Garage / Depot Clustering Helpers =====
    let garageClusterMarker = null;

    function getGarageClusterIcon(count) {
        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `
                <div class="relative group cursor-pointer" style="transform: translate(-24px, -24px);">
                    <div class="absolute inset-0 rounded-2xl bg-amber-500/35 animate-ping opacity-75"></div>
                    <div class="relative flex items-center gap-2 bg-slate-900/95 hover:bg-slate-900 backdrop-blur-md text-white border-2 border-amber-400/90 px-2.5 py-1.5 rounded-2xl shadow-2xl transition-all duration-300 transform group-hover:scale-105">
                        <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-900 font-bold text-sm shadow-md flex-shrink-0">
                            🏠
                        </div>
                        <div class="flex flex-col pr-1 whitespace-nowrap">
                            <span class="text-xs font-bold text-black leading-tight" style="color: black;">จุดจอดเก็บรถ</span>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="bg-gradient-to-r from-red-500 to-rose-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">
                                    ${count} คัน
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            iconSize: [48, 48],
            iconAnchor: [24, 24]
        });
    }

    function updateGarageCluster(garageTrams) {
        if (!map || typeof L === 'undefined') return;
        if (!garageTrams || garageTrams.length === 0) {
            if (garageClusterMarker) {
                map.removeLayer(garageClusterMarker);
                garageClusterMarker = null;
            }
            return;
        }

        const count = garageTrams.length;
        const icon = getGarageClusterIcon(count);
        const latLng = [GARAGE_LAT, GARAGE_LNG];

        const vehicleListItems = garageTrams.map(tram => {
            const tramIndex = trams.findIndex(t => t.id === tram.id);
            const color = getVehicleColor(tramIndex !== -1 ? tramIndex : 0);
            const carData = globalCarStatus[tram.id] || {};
            const rawStatus = (carData.status || tram.status || 'ซ่อมบำรุง / หยุดพัก').toString().trim();
            const isBroken = rawStatus.includes('ขัดข้อง') || rawStatus === 'MAINTENANCE' || rawStatus.includes('ระงับ');
            const isEnded = rawStatus === 'ended' || rawStatus === 'สิ้นสุด' || rawStatus === 'เลิกงาน';
            
            let statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            let statusIcon = '⏸️';
            let displayStatus = 'พักเบรก / จุดจอด';
            
            if (isBroken) {
                statusBadgeClass = 'bg-red-100 text-red-700 border-red-200';
                statusIcon = '🛑';
                displayStatus = rawStatus;
            } else if (isEnded) {
                statusBadgeClass = 'bg-gray-100 text-gray-700 border-gray-200';
                statusIcon = '🏁';
                displayStatus = 'เลิกงาน / สิ้นสุดรอบ';
            }

            return `
                <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-xl hover:bg-slate-100/80 transition shadow-sm">
                    <div class="flex items-center justify-between gap-2 mb-1 flex-nowrap">
                        <div class="flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            <span class="font-bold text-slate-800 text-xs whitespace-nowrap">${tram.id}</span>
                            <span class="text-[10px] text-slate-500 font-mono whitespace-nowrap">(${tram.plate || 'N/A'})</span>
                        </div>
                        <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-full border ${statusBadgeClass} whitespace-nowrap shrink-0">
                            ${statusIcon} ${displayStatus}
                        </span>
                    </div>
                    <div class="text-[10.5px] text-slate-600">
                        <p><span class="text-slate-400">ผู้ขับ:</span> <span class="font-semibold text-slate-700">${tram.driver || 'ไม่มีผู้ปฏิบัติงาน'}</span></p>
                    </div>
                </div>
            `;
        }).join('');

        const popupHtml = `
            <div class="p-3 font-kanit min-w-[290px] max-w-[340px]">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base shadow-sm">
                            🏠
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm leading-tight">จุดจอดเก็บรถ</h4>
                        </div>
                    </div>
                    <span class="bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-black px-2.5 py-1 rounded-full shadow-sm">
                        ${count} คัน
                    </span>
                </div>
                <p class="text-xs font-semibold text-slate-700 mb-2 flex items-center gap-1">
                    <span>📋</span> รายชื่อรถประจำจุดจอดเก็บรถ:
                </p>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    ${vehicleListItems}
                </div>
            </div>
        `;

        if (!garageClusterMarker) {
            garageClusterMarker = L.marker(latLng, {
                icon: icon,
                zIndexOffset: 300
            }).addTo(map);
        } else {
            garageClusterMarker.setLatLng(latLng);
            garageClusterMarker.setIcon(icon);
        }
        garageClusterMarker.bindPopup(popupHtml);
    }

    // ===== 🚌 Shuttle Markers — Active Route vehicles shown, Garage vehicles clustered =====
    const tramMarkers = {};

    function getShuttleIcon(tramId, status, vehicleIdx) {
        const color = getVehicleColor(vehicleIdx);
        const number = vehicleIdx + 1;
        const isCurrentCar = (tramId === currentCarCode || tramId === `EV-${currentCarId}` || tramId === `EV-0${currentCarId}`);

        let bgColor = color.bg;
        let textColor = color.text;
        let pingHtml = '';
        let opacityClass = '';
        let statusIcon = '';

        if (status === 'pause') {
            bgColor = '#EAB308';
            textColor = '#451A03';
            pingHtml = '';
            statusIcon = '<i class="fas fa-pause text-[8px] absolute -bottom-0.5 -right-0.5 bg-amber-600 text-white w-4 h-4 flex items-center justify-center rounded-full border-2 border-white shadow-md"></i>';
        } else if (status === 'broken') {
            pingHtml = '';
            bgColor = '#EF4444';
            statusIcon = '<i class="fas fa-wrench text-[8px] absolute -bottom-0.5 -right-0.5 bg-red-100 text-red-600 w-3.5 h-3.5 flex items-center justify-center rounded-full border border-white"></i>';
        } else if (status === 'ended') {
            pingHtml = '';
            opacityClass = 'opacity-50';
            bgColor = '#94A3B8';
            statusIcon = '<i class="fas fa-stop text-[7px] absolute -bottom-0.5 -right-0.5 bg-slate-200 text-slate-500 w-3.5 h-3.5 flex items-center justify-center rounded-full border border-white"></i>';
        }

        let topStatusBadge = '';
        if (status === 'pause') {
            topStatusBadge = `<div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-md whitespace-nowrap border border-white flex items-center gap-1"><i class="fas fa-pause text-[8px]"></i> หยุดพัก</div>`;
        } else if (status === 'broken') {
            topStatusBadge = `<div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-red-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-md whitespace-nowrap border border-white flex items-center gap-1"><i class="fas fa-wrench text-[8px]"></i> รถเสีย</div>`;
        }

        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `
                <div class="relative shuttle-float ${opacityClass}" style="transform: translate(-20px, -20px);">
                    ${topStatusBadge}
                    ${pingHtml}
                    <div class="relative w-10 h-10 rounded-full flex items-center justify-center border-[3px] ${isCurrentCar ? 'border-amber-300 scale-110 shadow-2xl' : 'border-white shadow-xl'}"
                         style="background: ${bgColor};">
                        <span class="text-sm font-black" style="color: ${textColor}; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">${number}</span>
                        ${statusIcon}
                    </div>
                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-sm text-[9px] font-bold px-2 py-0.5 rounded-full shadow whitespace-nowrap"
                          style="color: ${color.bg}; border: 1.5px solid ${color.border};">
                        ${tramId} ${isCurrentCar ? '(รถของคุณ)' : ''}
                    </span>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
    }

    // ===== Anti-Overlap: Smart offset to prevent vehicles from obscuring station markers or each other =====
    function applyAntiOverlap() {
        if (!map || typeof tramMarkers === 'undefined') return;
        const tramEntries = Object.entries(tramMarkers);
        if (tramEntries.length === 0) return;

        // 1. Get pixel positions for station markers
        const stationPoints = (typeof stationMarkers !== 'undefined' ? Object.entries(stationMarkers) : []).map(([stId, stMarker]) => {
            if (!stMarker || !stMarker.getLatLng) return null;
            const latlng = stMarker.getLatLng();
            const point = map.latLngToContainerPoint(latlng);
            return { id: stId, latlng, point };
        }).filter(Boolean);

        // 2. Get base pixel positions for vehicle markers using _originalLatLng
        const tramPositions = tramEntries.map(([id, marker]) => {
            if (!marker || !marker.getLatLng) return null;
            const baseLatLng = marker._originalLatLng || marker.getLatLng();
            const point = map.latLngToContainerPoint(baseLatLng);
            return { id, marker, baseLatLng, point };
        }).filter(Boolean);

        const ST_THRESHOLD_PX = 50;  // distance threshold to a station
        const VEH_THRESHOLD_PX = 42; // distance threshold between vehicles

        const vehiclesAtStations = {};
        const routeVehicles = [];

        // Classify vehicles: at a station vs on open route
        tramPositions.forEach(v => {
            let nearestStation = null;
            let minDist = Infinity;

            stationPoints.forEach(st => {
                const dx = v.point.x - st.point.x;
                const dy = v.point.y - st.point.y;
                const dist = Math.hypot(dx, dy);
                if (dist < ST_THRESHOLD_PX && dist < minDist) {
                    minDist = dist;
                    nearestStation = st;
                }
            });

            if (nearestStation) {
                if (!vehiclesAtStations[nearestStation.id]) {
                    vehiclesAtStations[nearestStation.id] = { station: nearestStation, vehicles: [] };
                }
                vehiclesAtStations[nearestStation.id].vehicles.push(v);
            } else {
                routeVehicles.push(v);
            }
        });

        // Helper to update marker position ONLY if it actually changed (prevents flicker!)
        function setMarkerLatLngSmooth(marker, targetLatLng) {
            const current = marker.getLatLng();
            if (!current || Math.abs(current.lat - targetLatLng.lat) > 0.000001 || Math.abs(current.lng - targetLatLng.lng) > 0.000001) {
                marker.setLatLng(targetLatLng);
            }
        }

        // Offset vehicles at stations
        Object.values(vehiclesAtStations).forEach(({ station, vehicles }) => {
            const count = vehicles.length;
            if (count === 1) {
                const newPoint = L.point(station.point.x, station.point.y - 44);
                const newLatLng = map.containerPointToLatLng(newPoint);
                setMarkerLatLngSmooth(vehicles[0].marker, newLatLng);
            } else {
                const spreadAngle = Math.PI / 3;
                const startAngle = -Math.PI / 2 - (spreadAngle * (count - 1)) / 2;
                const radius = 46;

                vehicles.forEach((v, i) => {
                    const angle = startAngle + i * spreadAngle;
                    const offsetX = Math.cos(angle) * radius;
                    const offsetY = Math.sin(angle) * radius;
                    const newPoint = L.point(station.point.x + offsetX, station.point.y + offsetY);
                    const newLatLng = map.containerPointToLatLng(newPoint);
                    setMarkerLatLngSmooth(v.marker, newLatLng);
                });
            }
        });

        // Vehicle-to-vehicle anti-overlap for open route vehicles
        const visited = new Set();
        const groups = [];

        for (let i = 0; i < routeVehicles.length; i++) {
            if (visited.has(i)) continue;
            const group = [i];
            visited.add(i);

            for (let j = i + 1; j < routeVehicles.length; j++) {
                if (visited.has(j)) continue;
                const dx = routeVehicles[i].point.x - routeVehicles[j].point.x;
                const dy = routeVehicles[i].point.y - routeVehicles[j].point.y;
                const dist = Math.hypot(dx, dy);
                if (dist < VEH_THRESHOLD_PX) {
                    group.push(j);
                    visited.add(j);
                }
            }

            if (group.length > 1) {
                groups.push(group);
            } else {
                setMarkerLatLngSmooth(routeVehicles[i].marker, routeVehicles[i].baseLatLng);
            }
        }

        groups.forEach(group => {
            const count = group.length;
            const angleStep = (2 * Math.PI) / count;
            const OFFSET_PX = 36;

            group.forEach((posIdx, i) => {
                const angle = angleStep * i - Math.PI / 2;
                const offsetX = Math.cos(angle) * OFFSET_PX;
                const offsetY = Math.sin(angle) * OFFSET_PX;

                const pos = routeVehicles[posIdx];
                const newPoint = L.point(pos.point.x + offsetX, pos.point.y + offsetY);
                const newLatLng = map.containerPointToLatLng(newPoint);
                setMarkerLatLngSmooth(pos.marker, newLatLng);
            });
        });
    }

    // ⚠️ ลบ 'layeradd' ออก เพราะ setLatLng() ใน applyAntiOverlap() trigger layeradd → infinite loop
    map.on('zoomend moveend', () => {
        applyAntiOverlap();
    });

    function initTramMarkers() {
        if (!map || typeof L === 'undefined') return;
        const garageTrams = [];

        trams.forEach((tram, idx) => {
            if (isVehicleInGarage(tram)) {
                if (tramMarkers[tram.id]) {
                    map.removeLayer(tramMarkers[tram.id]);
                    delete tramMarkers[tram.id];
                }
                garageTrams.push(tram);
                return;
            }

            let latLng = null;
            if (tram.coords) {
                const parts = tram.coords.split(',').map(Number);
                if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                    latLng = parts;
                }
            }
            if (!latLng) {
                const defCoord = defaultTramCoords[tram.id];
                if (defCoord) {
                    latLng = defCoord.split(',').map(Number);
                } else {
                    latLng = [GARAGE_LAT, GARAGE_LNG];
                }
            }

            let status = 'normal';
            if (tram.status === 'รถขัดข้อง') status = 'broken';
            else if (tram.status === 'พักเบรค' || tram.status === 'กำลังปรับปรุง') status = 'pause';

            const isCurrentCar = (tram.id === currentCarCode || tram.id === `EV-${currentCarId}` || tram.id === `EV-0${currentCarId}`);

            if (!tramMarkers[tram.id]) {
                const marker = L.marker(latLng, {
                    icon: getShuttleIcon(tram.id, status, idx),
                    zIndexOffset: isCurrentCar ? 3000 : 500
                }).addTo(map);
                marker._originalLatLng = L.latLng(latLng[0], latLng[1]);
                tramMarkers[tram.id] = marker;
            } else {
                tramMarkers[tram.id]._originalLatLng = L.latLng(latLng[0], latLng[1]);
            }

            const carCap = (tram.capacity_sit !== undefined && !isNaN(parseInt(tram.capacity_sit))) ? parseInt(tram.capacity_sit) : 10;
            if (!globalCarStatus[tram.id]) {
                globalCarStatus[tram.id] = {
                    status: tram.status || 'พร้อมใช้งาน',
                    occupied: 0,
                    max: carCap
                };
            } else {
                globalCarStatus[tram.id].max = carCap;
            }

            const carData = globalCarStatus[tram.id] || {};
            const displayMax = carData.max || carCap;

            let statusDisplay = '<span class="font-bold text-emerald-600">พร้อมใช้งาน</span>';
            const rawStatus = (tram.status || '').toString().trim();
            if (rawStatus === 'ระงับการใช้งาน' || rawStatus === 'SUSPENDED' || rawStatus === 'MAINTENANCE' || rawStatus === 'ระงับใช้งาน' || rawStatus.includes('ขัดข้อง')) {
                statusDisplay = `<span class="font-bold text-red-600">${rawStatus}</span>`;
            } else if (rawStatus === 'พักเบรก' || rawStatus === 'พัก') {
                statusDisplay = `<span class="font-bold text-amber-500">พักเบรก</span>`;
            } else if (rawStatus === 'ended' || rawStatus === 'สิ้นสุด' || rawStatus === 'เลิกงาน') {
                statusDisplay = `<span class="font-bold text-gray-500">เลิกงาน/สิ้นสุดรอบ</span>`;
            }

            tramMarkers[tram.id].bindPopup(`
                <div class="p-2 font-kanit">
                    <div class="flex items-center gap-2 mb-2">
                        <h4 class="font-bold text-slate-800 text-sm">${tram.id} ${isCurrentCar ? '<span class="text-xs text-pink-600 font-bold">(รถที่คุณขับ)</span>' : ''}</h4>
                    </div>
                    <p class="text-xs text-slate-500">ทะเบียน: <span class="font-bold text-slate-700">${tram.plate || 'N/A'}</span></p>
                    <p class="text-xs text-slate-500 mt-1">สถานะ: ${statusDisplay}</p>
                    <p class="text-xs text-slate-500 mt-1">ผู้โดยสาร: <span class="font-bold text-slate-700">${carData.occupied || 0}/${displayMax} คน</span></p>
                </div>
            `);
        });

        updateGarageCluster(garageTrams);
        applyAntiOverlap();
    }

    // Debounced wrapper: ป้องกัน initTramMarkers ถูกเรียกซ้ำถี่เกิน (เช่น จาก storage event)
    let _initTramMarkersDebounceTimer = null;
    function debouncedInitTramMarkers() {
        clearTimeout(_initTramMarkersDebounceTimer);
        _initTramMarkersDebounceTimer = setTimeout(() => {
            if (typeof initTramMarkers === 'function') initTramMarkers();
        }, 300);
    }

    initTramMarkers();

    let currentOccupied = 0;
    const currentCar = trams.find(t => t.id === currentCarCode || t.id === `EV-${currentCarId}` || t.id === `EV-0${currentCarId}`);
    let maxCapacity = (currentCar && currentCar.capacity_sit !== undefined) ? (parseInt(currentCar.capacity_sit) || 10) : 10;

    document.addEventListener('DOMContentLoaded', () => {
        const capacityEl = document.getElementById('seats-max-capacity');
        if (capacityEl) capacityEl.innerText = maxCapacity;
        
        const codeEl = document.getElementById('car-display-code');
        if (codeEl) codeEl.innerText = currentCarCode;

        // Resolve driver, vehicle, and route details dynamically
        const trams = getStorage("yru_trams_v16", defaultTrams);
        const users = getStorage("yru_users_v6", defaultUsers);
        
        let currentCarIdx = trams.findIndex(t => t.id === currentCarCode || t.id === `EV-${currentCarId}` || t.id === `EV-0${currentCarId}`);
        if (currentCarIdx === -1) currentCarIdx = 0;
        const currentCarObj = trams[currentCarIdx];
        if (currentCarObj && currentCarObj.capacity_sit !== undefined) {
            maxCapacity = parseInt(currentCarObj.capacity_sit) || 10;
            const capEl = document.getElementById('seats-max-capacity');
            if (capEl) capEl.innerText = maxCapacity;
        }

        const carNum = currentCarIdx + 1;
        const carCode = currentCarObj ? currentCarObj.id : currentCarCode;
        const carPlate = currentCarObj ? (currentCarObj.plate || '') : '';
        const carRoute = currentCarObj ? (currentCarObj.route || 'สาย 1: เนินขาม-หอพัก') : 'สาย 1';
        const color = getVehicleColor(currentCarIdx);

        const emailMapByCar = {
            'EV-01': 'asmee@yru.ac.th',
            'EV-02': 'arfan@yru.ac.th',
            'EV-03': 'sufiyan@yru.ac.th',
            'EV-04': 'usman@yru.ac.th',
            'EV-05': 'badri@yru.ac.th',
            'EV-06': 'torik@yru.ac.th',
            'EV-07': 'somwang@yru.ac.th',
            'EV-08': 'somjai@yru.ac.th',
            'EV-09': 'kitti@yru.ac.th',
            'EV-10': 'ruslan@yru.ac.th'
        };

        const emailMapByName = {
            'นายอัสมี มูเล็ง': 'asmee@yru.ac.th',
            'นายอัรฟาน มะเระ': 'arfan@yru.ac.th',
            'นายซูเฟียน มะโอะ': 'sufiyan@yru.ac.th',
            'นายอุสมาน สาและ': 'usman@yru.ac.th',
            'นายบัดรี สาและ': 'badri@yru.ac.th',
            'นายตอรริก ลือแมะ': 'torik@yru.ac.th',
            'นายสมหวัง ใจดี': 'somwang@yru.ac.th',
            'นายสมใจ ใจดี': 'somjai@yru.ac.th',
            'นายกิตติ ตั้งใจ': 'kitti@yru.ac.th',
            'นายรุสลัน สอเฮาะ': 'ruslan@yru.ac.th'
        };

        let driverName = currentCarObj ? (currentCarObj.driver || 'นายอัสมี มูเล็ง') : 'นายอัสมี มูเล็ง';
        let driverEmail = emailMapByCar[carCode] || emailMapByName[driverName] || 'asmee@yru.ac.th';
        
        if (currentCarObj && currentCarObj.driver_id) {
            const foundUser = users.find(u => u.user_id === currentCarObj.driver_id);
            if (foundUser && foundUser.email) {
                driverName = foundUser.name;
                driverEmail = foundUser.email;
            }
        }

        // 1. Update Header Badge
        const hNum = document.getElementById('header-car-num-badge');
        if (hNum) { hNum.innerText = carNum; hNum.style.background = color.bg; }
        
        const hCode = document.getElementById('header-car-code-text');
        if (hCode) hCode.innerText = carCode;
        
        const hPlate = document.getElementById('header-car-plate-text');
        if (hPlate) hPlate.innerText = carPlate ? `(${carPlate})` : '';
        
        const hDriver = document.getElementById('header-driver-name-text');
        if (hDriver) hDriver.innerText = driverName;

        // 2. Update Logged-in Profile & Dropdown
        const loggedName = document.getElementById('driver-logged-name');
        if (loggedName) loggedName.innerText = driverEmail || 'asmee@yru.ac.th';
        
        const avatarCircle = document.getElementById('driver-avatar-circle');
        if (avatarCircle) {
            avatarCircle.innerText = carNum;
            avatarCircle.style.background = color.bg;
            avatarCircle.style.color = '#ffffff';
        }
        
        const dropEmail = document.getElementById('dropdown-user-email');
        if (dropEmail) dropEmail.innerText = driverEmail;
        
        const dropDriver = document.getElementById('dropdown-driver-name');
        if (dropDriver) dropDriver.innerText = `👤 พนักงานขับรถ: ${driverName}`;
        
        const dropCar = document.getElementById('dropdown-car-info');
        if (dropCar) dropCar.innerText = `🚍 รถไฟฟ้าประจำตัว: ${carCode} ${carPlate ? '(' + carPlate + ')' : ''}`;

        // 3. Update Side Control Panel Card
        const cBadge = document.getElementById('card-vehicle-badge-circle');
        if (cBadge) { cBadge.innerText = carNum; cBadge.style.background = color.bg; }
        
        const cCode = document.getElementById('card-car-code');
        if (cCode) cCode.innerText = carCode;
        
        const cPlate = document.getElementById('card-car-plate');
        if (cPlate) cPlate.innerText = carPlate ? `(${carPlate})` : '';
        
        const cDriver = document.getElementById('card-driver-name');
        if (cDriver) cDriver.innerText = driverName;
        
        const cRoute = document.getElementById('card-route-name');
        if (cRoute) cRoute.innerText = carRoute;

        const cRound = document.getElementById('card-round-number');
        if (cRound) {
            cRound.innerText = currentRoundNumber;
        }

        const driverDisp = document.getElementById('driver-name-display');
        if (driverDisp) driverDisp.innerText = `พนักงานขับรถ: ${driverName}`;

        const routeDisp = document.getElementById('route-name-display');
        if (routeDisp) routeDisp.innerText = carRoute;

        updateNextStopUI();
    });

    function syncDriverVehicleData() {
        const freshTrams = getStorage("yru_trams_v16", defaultTrams);
        if (!freshTrams || !Array.isArray(freshTrams)) return;

        let curIdx = freshTrams.findIndex(t => t.id === currentCarCode || t.id === `EV-${currentCarId}` || t.id === `EV-0${currentCarId}`);
        if (curIdx === -1) curIdx = 0;
        const curObj = freshTrams[curIdx];

        if (curObj) {
            if (curObj.capacity_sit !== undefined && !isNaN(parseInt(curObj.capacity_sit))) {
                const newMax = parseInt(curObj.capacity_sit) || 10;
                if (newMax !== maxCapacity) {
                    maxCapacity = newMax;
                    const capEl = document.getElementById('seats-max-capacity');
                    if (capEl) capEl.innerText = maxCapacity;
                }
            }

            const carNum = curIdx + 1;
            const carCode = curObj.id || currentCarCode;
            const carPlate = curObj.plate || '';
            const carRoute = curObj.route || 'สาย 1: เนินขาม-หอพัก';
            const driverName = curObj.driver || 'พนักงานขับรถ';
            const color = getVehicleColor(curIdx);

            const cBadge = document.getElementById('card-vehicle-badge-circle');
            if (cBadge) { cBadge.innerText = carNum; cBadge.style.background = color.bg; }

            const cCode = document.getElementById('card-car-code');
            if (cCode) cCode.innerText = carCode;
            
            const cPlate = document.getElementById('card-car-plate');
            if (cPlate) cPlate.innerText = carPlate ? `(${carPlate})` : '';
            
            const cRoute = document.getElementById('card-route-name');
            if (cRoute) cRoute.innerText = carRoute;

            const cDriver = document.getElementById('card-driver-name');
            if (cDriver) cDriver.innerText = driverName;

            const hNum = document.getElementById('header-car-num-badge');
            if (hNum) { hNum.innerText = carNum; hNum.style.background = color.bg; }

            const hPlate = document.getElementById('header-car-plate-text');
            if (hPlate) hPlate.innerText = carPlate ? `(${carPlate})` : '';
            
            const hDriver = document.getElementById('header-driver-name-text');
            if (hDriver) hDriver.innerText = driverName;

            const routeDisp = document.getElementById('route-name-display');
            if (routeDisp) routeDisp.innerText = carRoute;

            const driverDisp = document.getElementById('driver-name-display');
            if (driverDisp) driverDisp.innerText = `พนักงานขับรถ: ${driverName}`;

            if (typeof updateSeatsUI === 'function') updateSeatsUI();
        }
    }

    window.resetSeatsOccupied = function() {
        currentOccupied = 0;
        updateSeatsUI();
    }

    window.changeSeatsOccupied = function(change) {
        if (change === 'reset' || change === 0) {
            currentOccupied = 0;
        } else {
            if (change > 0 && (currentOccupied + change) > maxCapacity) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '⚠️ ที่นั่งไม่พอ',
                        text: 'รถคันนี้ที่นั่งไม่พอ กรุณารอเที่ยวถัดไป หรือเลือกรถคันอื่น',
                        icon: 'warning',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#EF4444'
                    });
                } else {
                    alert('รถคันนี้ที่นั่งไม่พอ กรุณารอเที่ยวถัดไป หรือเลือกรถคันอื่น');
                }
                return;
            }
            currentOccupied = Math.max(0, Math.min(maxCapacity, currentOccupied + change));
        }
        updateSeatsUI();
    }

    let lastSentOccupied = -1;

    function updateSeatsUI() {
        const disp = document.getElementById('seats-occupied-display');
        if (disp) disp.innerText = currentOccupied;

        const pct = Math.round((currentOccupied / maxCapacity) * 100);
        const pctEl = document.getElementById('seats-percentage');
        if (pctEl) pctEl.innerText = pct + '%';

        const bar = document.getElementById('seats-progress-bar');
        if (bar) {
            bar.style.width = pct + '%';
            if (pct >= 100) {
                bar.className = 'bg-rose-600 h-full rounded-full transition-all duration-300';
            } else if (pct >= 75) {
                bar.className = 'bg-amber-500 h-full rounded-full transition-all duration-300';
            } else {
                bar.className = 'bg-blue-600 h-full rounded-full transition-all duration-300';
            }
        }

        if (typeof globalCarStatus !== 'undefined' && typeof currentCarCode !== 'undefined') {
            const carInfo = globalCarStatus[currentCarCode] || {};
            carInfo.occupied = currentOccupied;
            globalCarStatus[currentCarCode] = carInfo;
            try { localStorage.setItem('yru_car_status_' + currentCarCode, JSON.stringify(carInfo)); } catch(e) {}
        }
        // ⚠️ ลบ Throttle storage dispatch เพื่อป้องกัน infinite loop
        // Native localStorage item modification already dispatches to other tabs natively.

        if (lastSentOccupied !== currentOccupied) {
            lastSentOccupied = currentOccupied;
            fetch('/api/driver/update-seats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ car_id: currentCarId, occupied: currentOccupied })
            })
            .then(res => res.json())
            .then(data => console.log('Seats updated real-time:', data))
            .catch(err => console.error('Error updating seats occupied:', err));
        }
    }

    window.openIssueReportPrompt = function() {
        if (typeof Swal === 'undefined') {
            const issue = prompt("กรุณาระบุปัญหาที่พบ (เช่น ไฟชาร์จไม่เข้า, ลมยางอ่อน):");
            if (!issue) return;
            fetch('/api/maintenance/report', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ car_id: currentCarCode, issue: issue, details: '' })
            });
            return;
        }

        Swal.fire({
            title: '<div class="flex items-center justify-center gap-2 text-slate-800 font-extrabold text-base md:text-lg"><i class="fas fa-wrench text-amber-500"></i> แจ้งเหตุ / แจ้งซ่อมรถไฟฟ้า</div>',
            html: `
                <div class="text-left font-kanit space-y-3 pt-2">
                    <p class="text-xs text-slate-500 font-medium">ระบุรายละเอียดปัญหาที่พบ เพื่อส่งรายงานไปยังศูนย์ควบคุมและฝ่ายช่าง</p>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            หัวข้อ/ปัญหาที่พบ <span class="text-pink-600">*</span>
                        </label>
                        <input type="text" id="swal-issue-title" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" placeholder="เช่น ไฟชาร์จไม่เข้า, ลมยางอ่อน, เบรกมีเสียงดัง">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            รายละเอียดเพิ่มเติม (ถ้ามี)
                        </label>
                        <textarea id="swal-issue-details" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition resize-none" placeholder="อธิบายอาการเพิ่มเติม สถานที่พบ หรือหมายเหตุ..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane mr-1.5"></i> ส่งรายงานแจ้งซ่อม',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#F59E0B',
            cancelButtonColor: '#94A3B8',
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-amber-100/80 p-5',
                confirmButton: 'rounded-xl font-bold text-xs py-2.5 px-4 shadow-md shadow-amber-500/20 active:scale-95 transition',
                cancelButton: 'rounded-xl font-bold text-xs py-2.5 px-4 active:scale-95 transition'
            },
            preConfirm: () => {
                const issueTitle = document.getElementById('swal-issue-title').value.trim();
                const issueDetails = document.getElementById('swal-issue-details').value.trim();
                if (!issueTitle) {
                    Swal.showValidationMessage('กรุณาระบุปัญหาที่พบก่อนส่งรายงาน!');
                    return false;
                }
                return { issue: issueTitle, details: issueDetails };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const { issue, details } = result.value;

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
                    Swal.fire({
                        title: 'บันทึกการแจ้งซ่อมสำเร็จ',
                        text: 'ส่งรายงานแจ้งชำรุดไปยังศูนย์ควบคุมเรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                        confirmButtonText: 'ตกลง',
                        customClass: { popup: 'rounded-3xl' }
                    });
                })
                .catch(err => {
                    console.error('Error reporting issue:', err);
                    Swal.fire({
                        title: 'บันทึกการแจ้งซ่อมสำเร็จ',
                        text: 'ระบบได้บันทึกรายงานการแจ้งซ่อมเรียบร้อยแล้ว',
                        icon: 'success',
                        confirmButtonColor: '#10B981',
                        confirmButtonText: 'ตกลง',
                        customClass: { popup: 'rounded-3xl' }
                    });
                });
            }
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

    window.incrementCarRoundNumber = function() {
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        currentRoundNumber += 1;
        
        try {
            localStorage.setItem('yru_round_' + activeCar, currentRoundNumber.toString());
        } catch(e) {}
        
        updateRoundDisplay();
        
        try {
            let accumulatedTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
            accumulatedTrips += 1;
            localStorage.setItem('yru_today_trips_accumulated', accumulatedTrips.toString());
        } catch(e) {}
        
        return newVal;
    };

    window.isVehicleAdminLocked = false;

    window.checkAdminVehicleLockStatus = function() {
        let isLocked = false;
        
        let freshTrams = [];
        try {
            freshTrams = JSON.parse(localStorage.getItem('yru_trams_v16') || '[]');
        } catch(e) {}

        const tram = freshTrams.find(t => t.id === currentCarCode) || (typeof trams !== 'undefined' ? trams.find(t => t.id === currentCarCode) : null);

        if (tram) {
            const statusStr = (tram.status || '').toString().trim();
            // A vehicle is ONLY locked if status is explicitly marked by Admin as suspended or maintenance
            if (statusStr === 'ระงับการใช้งาน' || statusStr === 'SUSPENDED' || statusStr === 'MAINTENANCE' || statusStr === 'ระงับใช้งาน' || statusStr.includes('ขัดข้อง')) {
                isLocked = true;
            } else {
                isLocked = false;
            }
        }

        window.isVehicleAdminLocked = isLocked;

        const btnContainer = document.getElementById('driver-status-buttons');
        const startWorkContainer = document.getElementById('start-work-container');
        const breakActionContainer = document.getElementById('break-action-container');
        const roundActionContainer = document.getElementById('round-action-container');
        const lockBadge = document.getElementById('admin-lock-badge');
        const lockMsg = document.getElementById('admin-lock-msg');

        if (isLocked) {
            if (btnContainer) btnContainer.classList.add('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (startWorkContainer) startWorkContainer.classList.add('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (breakActionContainer) breakActionContainer.classList.add('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (roundActionContainer) roundActionContainer.classList.add('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (lockBadge) lockBadge.classList.remove('hidden');
            if (lockMsg) lockMsg.classList.remove('hidden');
        } else {
            if (btnContainer) btnContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (startWorkContainer) startWorkContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (breakActionContainer) breakActionContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (roundActionContainer) roundActionContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
            if (lockBadge) lockBadge.classList.add('hidden');
            if (lockMsg) lockMsg.classList.add('hidden');
        }
    }

    // 🌟 ฟังก์ชันจัดการสถานะคนขับ (รวมการเปลี่ยนคลาส UI และยิง API)
    window.syncDriverStatus = function(statusType) {
        checkAdminVehicleLockStatus();
        if (window.isVehicleAdminLocked) {
            return;
        }

        const items = document.querySelectorAll('.status-item');
        items.forEach(i => {
            if(i.getAttribute('data-type') === statusType) {
                i.classList.add('active');
            } else {
                i.classList.remove('active');
            }
        });

        // 1. Update tram object in trams array & localStorage
        const curTram = trams.find(t => t.id === currentCarCode || t.id === `EV-${currentCarId}` || t.id === `EV-0${currentCarId}`);
        if (curTram) {
            if (statusType === 'normal') curTram.status = 'พร้อมใช้งาน';
            else if (statusType === 'pause') curTram.status = 'พักเบรค';
            else if (statusType === 'broken') curTram.status = 'รถขัดข้อง';
            
            try { localStorage.setItem('yru_trams_v16', JSON.stringify(trams)); } catch(e) {}
        }

        // 2. Update globalCarStatus for this car
        if (typeof globalCarStatus !== 'undefined') {
            const carInfo = globalCarStatus[currentCarCode] || {};
            if (statusType === 'normal') carInfo.status = 'พร้อมใช้งาน';
            else if (statusType === 'pause') carInfo.status = 'พักเบรค';
            else if (statusType === 'broken') carInfo.status = 'รถขัดข้อง';
            globalCarStatus[currentCarCode] = carInfo;
            try { localStorage.setItem('yru_car_status_' + currentCarCode, JSON.stringify(carInfo)); } catch(e) {}
        }

        // 3. Re-draw vehicle markers on map
        if (typeof initTramMarkers === 'function') {
            initTramMarkers();
        }

        // Native localStorage item modification already dispatches to other tabs natively.

        const gpsStatus = document.getElementById('gps-status-text');

        // อัปเดตข้อความแถบแสดงผลฝั่งคนขับ
        if(statusType === 'normal') {
            if (gpsStatus) { gpsStatus.innerHTML = `● ออนไลน์`; gpsStatus.style.color = "#10b981"; }
        } else if(statusType === 'pause') {
            if (gpsStatus) { gpsStatus.innerHTML = `● หยุดพักชั่วคราว`; gpsStatus.style.color = "#f59e0b"; }
        } else if(statusType === 'broken') {
            if (gpsStatus) { gpsStatus.innerHTML = `● ออฟไลน์ (รถเสีย)`; gpsStatus.style.color = "#ef4444"; }
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

        // อัปเดตการ์ดจัดการช่วงพัก (Break Status Card UI)
        const breakBadge = document.getElementById('break-status-badge');
        const breakActionContainer = document.getElementById('break-action-container');
        if (breakBadge && breakActionContainer) {
            if (statusType === 'pause') {
                breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-200';
                breakBadge.innerText = 'กำลังพักเบรก';
                breakActionContainer.innerHTML = `
                    <button id="btn-resume-work" onclick="toggleBreakStatus(false)" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-3.5 px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-emerald-600/20 font-kanit">
                        <i class="fas fa-play-circle text-sm"></i> กลับมาให้บริการ
                    </button>
                `;
            } else {
                breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100';
                breakBadge.innerText = 'ให้บริการปกติ';
                breakActionContainer.innerHTML = `
                    <button id="btn-take-break" onclick="toggleBreakStatus(true)" class="w-full bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold py-3.5 px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-amber-500/20 font-kanit">
                        พักเบรก
                    </button>
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
                car_id: currentCarCode,
                start_time: '-'
            })
        })
        .then(response => response.json())
        .then(data => console.log("Driver status synchronized:", data))
        .catch(error => console.error('Error synchronizing driver status:', error));
    } // End of window.syncDriverStatus

    window.toggleBreakStatus = function(isBreak) {
        checkAdminVehicleLockStatus();
        if (window.isVehicleAdminLocked) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ไม่สามารถทำรายการได้',
                    text: 'รถถูกระงับการใช้งานโดยผู้ดูแลระบบ',
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }

        const targetStatus = isBreak ? 'pause' : 'normal';
        syncDriverStatus(targetStatus);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: isBreak ? 'เข้าสู่ช่วงพักเบรก' : 'กลับมาให้บริการ',
                text: isBreak ? 'ปรับสถานะเป็นหยุดพักชั่วคราวเรียบร้อยแล้ว' : 'ปรับสถานะเป็นพร้อมให้บริการตามปกติแล้ว',
                icon: isBreak ? 'warning' : 'success',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
        }
    };

    // ⏱️ --- ส่วนงานจัดการรอบการเดินรถ (Round Management) ---
    let currentRoundNumber = parseInt(localStorage.getItem(`yru_round_${typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01'}`) || '1');
    let roundTotalPaxServed = 0;

    function updateRoundDisplay() {
        const roundEl = document.getElementById('card-round-number');
        if (roundEl) roundEl.innerText = Math.max(1, currentRoundNumber);
    }

    function renderRoundButtonState(state) {
        const container = document.getElementById('round-action-container');
        if (!container) return;

        if (state === 'start') {
            container.innerHTML = `
                <button id="btn-start-round" onclick="startNewRound()" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold py-3.5 px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-emerald-600/20 font-kanit">
                    <i class="fas fa-play-circle text-sm"></i> เริ่มรอบที่ ${currentRoundNumber + 1}
                </button>
            `;
        } else {
            container.innerHTML = `
                <button id="btn-end-round" onclick="endCurrentRound()" class="w-full bg-red-600 hover:bg-red-700 active:scale-95 text-white font-bold py-3.5 px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-red-600/20 font-kanit">
                    <i class="fas fa-stopwatch text-sm"></i> สิ้นสุด
                </button>
            `;
        }
    }

    // 🏆 หน้าต่างสรุปภาพรวมประจำวัน (Daily Summary)
    window.showDailySummaryModal = function() {
        const todayKey = new Date().toISOString().slice(0, 10);
        const carIdStr = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-05';
        const dailyRounds = currentRoundNumber;
        const dailyPax = parseInt(localStorage.getItem(`yru_daily_pax_${carIdStr}_${todayKey}`) || '0');

        let dailyStartTime = '-';
        try {
            dailyStartTime = localStorage.getItem(`yru_daily_start_time_${carIdStr}_${todayKey}`) || '-';
        } catch(e) {}

        const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const now = new Date();
        const finishTimeStr = `${now.getDate()} ${thaiMonths[now.getMonth()]} ${now.getFullYear() + 543} | ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;
        const endTimeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;

        let durationText = '-';
        if (dailyStartTime !== '-') {
            try {
                const [startH, startM] = dailyStartTime.split(' ')[0].split(':').map(Number);
                const [endH, endM] = endTimeStr.split(' ')[0].split(':').map(Number);
                let diffMin = (endH * 60 + endM) - (startH * 60 + startM);
                if (diffMin < 0) diffMin += 1440; // over midnight
                const h = Math.floor(diffMin / 60);
                const m = diffMin % 60;
                durationText = `${h} ชั่วโมง ${m} นาที`;
            } catch(e) {}
        }

        Swal.fire({
            title: `สรุปผลการปฏิบัติงานประจำวัน<div class="text-xs font-normal text-slate-500 mt-1.5 flex items-center justify-center gap-1"><i class="fas fa-calendar-check text-emerald-600"></i> ${finishTimeStr}</div>`,
            html: `
                <div class="p-4 bg-gradient-to-b from-slate-50 to-emerald-50/50 rounded-2xl border border-emerald-200 text-left space-y-3 font-kanit shadow-sm">
                    <div class="flex items-center justify-between border-b border-emerald-200 pb-2.5">
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-bus text-emerald-600"></i> รถไฟฟ้า: <span class="text-emerald-700 font-extrabold">${carIdStr}</span>
                        </span>
                        <span class="text-xs bg-emerald-600 text-white font-extrabold px-3 py-0.5 rounded-full shadow-xs">สิ้นสุดการปฏิบัติงาน</span>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-2xs mb-2.5">
                        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
                            <span class="font-bold flex items-center gap-1.5"><i class="fas fa-clock text-blue-500"></i> เวลาเริ่ม - สิ้นสุด:</span>
                            <span class="font-bold text-slate-800">${dailyStartTime} - ${endTimeStr}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600 border-t border-slate-100 pt-1.5">
                            <span class="font-bold flex items-center gap-1.5"><i class="fas fa-hourglass-half text-amber-500"></i> ระยะเวลาทำงานรวม:</span>
                            <span class="font-bold text-emerald-600 text-sm">${durationText}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-center pt-1">
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                            <div class="text-xs text-slate-500 font-semibold mb-1"><i class="fas fa-route text-blue-500 mr-1"></i>จำนวนรอบทั้งหมด</div>
                            <div class="text-2xl font-black text-blue-600">${dailyRounds} <span class="text-xs font-normal text-slate-500">รอบ</span></div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                            <div class="text-xs text-slate-500 font-semibold mb-1"><i class="fas fa-users text-emerald-500 mr-1"></i>ผู้โดยสารรวมวันนี้</div>
                            <div class="text-2xl font-black text-emerald-600">${dailyPax} <span class="text-xs font-normal text-slate-500">คน</span></div>
                        </div>
                    </div>

                    <div class="border-t border-emerald-200/80 pt-2.5 text-[11px] text-slate-600 text-center flex items-center justify-center gap-1.5 font-medium">
                        <i class="fas fa-heart text-pink-500"></i> ขอบคุณสำหรับการปฏิบัติหน้าที่ในวันนี้อย่างปลอดภัย!
                    </div>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'สิ้นสุดวัน',
            confirmButtonColor: '#059669',
            customClass: { popup: 'font-kanit rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.backdrop || result.dismiss === Swal.DismissReason.close) {
                currentRoundNumber = 0;
                try {
                    localStorage.setItem('yru_round_' + carIdStr, '0');
                    localStorage.setItem(`yru_daily_pax_${carIdStr}_${todayKey}`, '0');
                } catch (e) {}
                updateRoundDisplay();
                renderRoundButtonState('start');
                
                Swal.fire({
                    title: 'รีเซ็ตรอบใหม่',
                    text: 'ระบบได้รีเซ็ตจำนวนรอบกลับเป็นเริ่มต้น (รอเริ่มรอบที่ 1) สำหรับวันใหม่แล้ว',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
        });
    };

    window.startWorkAction = function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันการเริ่มงาน',
                text: 'คุณต้องการเริ่มปฏิบัติงานใช่หรือไม่?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'เริ่มงาน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#64748b',
                customClass: { popup: 'font-kanit rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    const carIdStr = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
                    const todayKey = new Date().toISOString().slice(0, 10);
                    const now = new Date();
                    const startTimeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;
                    
                    try {
                        localStorage.setItem(`yru_daily_start_time_${carIdStr}_${todayKey}`, startTimeStr);
                    } catch(e) {}

                    // ส่ง API อัปเดตสถานะเป็นปกติ ฝั่ง Server
                    const nowTimeStart = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
                    fetch('/api/end-driver-round', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            car_id: carIdStr,
                            status: 'normal',
                            ended_at: nowTimeStart
                        })
                    }).catch(e => {});

                    Swal.fire({
                        title: 'เริ่มงานสำเร็จ',
                        html: `
                            <div class="text-sm text-slate-600 mb-2">เริ่มงานเวลา <span class="font-bold text-slate-800">${startTimeStr}</span> ขอให้การปฏิบัติงานราบรื่นครับ!</div>
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-left space-y-2 mt-3">
                                <div class="text-xs flex items-center"><i class="fas fa-id-card text-emerald-500 w-5"></i><span class="text-slate-500 mr-1.5">ผู้ขับขี่:</span> <span class="font-semibold text-slate-800">{{ $initialDriverEmail ?? 'Unknown' }}</span></div>
                                <div class="text-xs flex items-center"><i class="fas fa-bus text-pink-500 w-5"></i><span class="text-slate-500 mr-1.5">หมายเลขรถ:</span> <span class="font-semibold text-slate-800">${carIdStr}</span></div>
                            </div>
                        `,
                        icon: 'success',
                        timer: 4000,
                        showConfirmButton: true,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#10B981',
                        customClass: { popup: 'font-kanit rounded-2xl' }
                    });
                }
            });
        }
    };

    // 🛑 [กดปุ่ม สิ้นสุดรอบ] ➔ Pop-up ยืนยันการจบรอบ ➔ สรุปผลรอบนี้ ➔ รีเซ็ตเป็น 0 ➔ เปลี่ยนปุ่มเป็น "เริ่มรอบใหม่"
    window.endCurrentRound = function() {
        if (typeof Swal === 'undefined') {
            if (!confirm('ยืนยันการสิ้นสุดรอบการเดินรถ?')) return;
            finishRoundAction();
            return;
        }

        Swal.fire({
            title: '<div class="flex justify-center mb-4 mt-2"><div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-600 text-3xl"><i class="fas fa-flag-checkered"></i></div></div>ยืนยันการจบรอบการเดินรถ',
            text: 'คุณต้องการบันทึกและสิ้นสุดรอบการเดินรถนี้ใช่หรือไม่',
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'สิ้นสุดรอบ',
            cancelButtonText: 'ยกเลิก',
            customClass: { popup: 'font-kanit rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                finishRoundAction();
            }
        });
    };

    // 📊 ฟังก์ชันคำนวณสรุปผู้โดยสารรวมจากการเรียกรถจริงและจุดจอดในรอบนี้
    function getRealRoundPaxTotal() {
        let total = 0;
        let callCount = 0;

        // 1. ตรวจสอบจากคิวการเรียกรถจริงที่เข้ามาของรถคันนี้ (yru_call_queue)
        try {
            const callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
            callQueue.forEach(item => {
                if (item && isCallForThisCar(item)) {
                    const pax = parseInt(item.pax) || 1;
                    total += pax;
                    callCount++;
                }
            });
        } catch(e) {}

        // 2. ตรวจสอบจากประวัติการรับผู้โดยสารในรอบนี้ (yru_round_calls_...)
        try {
            const roundKey = `yru_round_calls_${typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01'}_r${currentRoundNumber}`;
            const callHistory = JSON.parse(localStorage.getItem(roundKey) || '[]');
            callHistory.forEach(item => {
                const pax = parseInt(item.pax) || 1;
                total += pax;
                callCount++;
            });
        } catch(e) {}

        // 3. นำค่ายอดสูงสุดจาก (การเรียกรถจริง, ยอดสะสมในรอบ, ยอดผู้โดยสารบนรถ)
        const finalTotal = Math.max(total, roundTotalPaxServed || 0, currentOccupied || 0);
        return { totalPax: finalTotal, totalCalls: callCount };
    }

    function finishRoundAction() {
        const realStats = getRealRoundPaxTotal();
        const paxServed = realStats.totalPax;
        const callCount = realStats.totalCalls;

        const carIdStr = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-05';
        const todayKey = new Date().toISOString().slice(0, 10);
        let dailyRounds = parseInt(localStorage.getItem(`yru_daily_rounds_${carIdStr}_${todayKey}`) || '0') + 1;
        let dailyPax = parseInt(localStorage.getItem(`yru_daily_pax_${carIdStr}_${todayKey}`) || '0') + paxServed;
        localStorage.setItem(`yru_daily_rounds_${carIdStr}_${todayKey}`, dailyRounds.toString());
        localStorage.setItem(`yru_daily_pax_${carIdStr}_${todayKey}`, dailyPax.toString());

        const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const now = new Date();
        const finishTimeStr = `${now.getDate()} ${thaiMonths[now.getMonth()]} ${now.getFullYear() + 543} | ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;
        const endTimeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;
        
        let startTimeStr = '-';
        try {
            startTimeStr = localStorage.getItem(`yru_round_start_${carIdStr}`) || '-';
        } catch(e) {}

        // 1. สรุปผลรอบนี้ (เช่น ผู้โดยสารรวม X คน จากการเรียกรถจริง)
        Swal.fire({
            title: `สรุปผลการเดินรถ<div class="text-xs font-normal text-slate-500 mt-1.5 flex items-center justify-center gap-1"><i class="fas fa-calendar-alt text-pink-500"></i> ${finishTimeStr}</div>`,
            html: `
                <div class="p-4 bg-pink-50/90 rounded-2xl border border-pink-200 text-left space-y-3 font-kanit shadow-sm">
                    <div class="flex items-center justify-between border-b border-pink-200/80 pb-2">
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-bus text-pink-600"></i> รถไฟฟ้า: <span class="text-pink-700 font-extrabold">${carIdStr}</span>
                        </span>
                        <span class="text-xs bg-pink-500 text-white font-extrabold px-3 py-0.5 rounded-full shadow-xs">รอบที่ ${currentRoundNumber}</span>
                    </div>

                    <div class="space-y-2 text-xs text-slate-700">
                        <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-pink-100 shadow-2xs">
                            <span class="font-semibold text-slate-600 flex items-center gap-1.5">
                                <i class="fas fa-clock text-blue-500"></i> เวลาเริ่ม - สิ้นสุด:
                            </span>
                            <span class="font-bold text-slate-800">${startTimeStr} - ${endTimeStr}</span>
                        </div>
                        <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-pink-100 shadow-2xs">
                            <span class="font-semibold text-slate-600 flex items-center gap-1.5">
                                <i class="fas fa-users text-pink-500"></i> ผู้โดยสารรวมในรอบนี้:
                            </span>
                            <span class="font-black text-pink-600 text-lg">${paxServed} คน</span>
                        </div>
                    </div>

                    <div class="border-t border-pink-200/80 pt-2 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-emerald-500"></i> สรุปผลจากข้อมูลการเดินรถและการเรียกรถจริงเรียบร้อยแล้ว
                    </div>
                </div>
            `,
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'เลิกงาน',
            confirmButtonColor: '#D81B60',
            cancelButtonColor: '#334155',
            reverseButtons: true,
            customClass: { popup: 'font-kanit rounded-2xl' }
        }).then((result) => {
            // 2. รีเซ็ตข้อมูลเป็น 0 คน
            currentOccupied = 0;
            roundTotalPaxServed = 0;
            if (typeof updateSeatsUI === 'function') updateSeatsUI();

            // ล้างคิวการเรียกรถที่ค้างอยู่ของรถคันนี้
            try {
                let callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
                callQueue = callQueue.filter(item => !isCallForThisCar(item));
                localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));
            } catch(e) {}
            if (typeof updateCallAlertUI === 'function') updateCallAlertUI();

            // ส่ง API สิ้นสุดรอบฝั่ง Server
            const nowTime = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
            fetch('/api/end-driver-round', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    car_id: carIdStr,
                    status: 'ended',
                    ended_at: nowTime
                })
            }).catch(e => {});

            // 3. เปลี่ยนปุ่มเป็น "เริ่มรอบใหม่" (เพื่อพร้อมวิ่งรอบถัดไป)
            renderRoundButtonState('start');

            // หากกดเลือกปุ่ม "สรุปประจำวัน (เลิกงาน)" ให้เปิด Daily Summary Modal
            if (result.dismiss === Swal.DismissReason.cancel) {
                showDailySummaryModal();
            }
        });
    }

    // 🚀 ฟังก์ชันปุ่ม "เริ่มรอบใหม่" สำหรับเตรียมพร้อมวิ่งรอบถัดไป
    window.startNewRound = function() {
        currentRoundNumber += 1;
        const startTimeStr = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
        try {
            const carIdStr = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
            localStorage.setItem(`yru_round_${carIdStr}`, currentRoundNumber.toString());
            localStorage.setItem(`yru_round_start_${carIdStr}`, startTimeStr);
        } catch(e) {}

        updateRoundDisplay();

        // ส่ง API อัปเดตสถานะเป็นปกติ ฝั่ง Server
        const nowTimeNormal = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
        const carIdStrNew = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        fetch('/api/end-driver-round', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                car_id: carIdStrNew,
                status: 'normal',
                ended_at: nowTimeNormal
            })
        }).catch(e => {});

        // รีเซ็ตดัชนีจุดจอดกลับไปจุดแรก (STOP 1/7)
        currentStopIndex = 0;
        if (typeof updateNextStopUI === 'function') updateNextStopUI();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `เริ่มเดินรถรอบที่ ${currentRoundNumber}`,
                text: 'ระบบพร้อมรับผู้โดยสารสำหรับรอบใหม่เรียบร้อยแล้ว',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
        }

        // เปลี่ยนปุ่มกลับเป็น "สิ้นสุดรอบการเดินรถ"
        renderRoundButtonState('end');
    };

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

    function isCallForThisCar(item) {
        if (!item || !item.car_id) return false;
        
        let callCar = String(item.car_id).trim();
        if (!callCar.startsWith('EV-')) {
            const num = parseInt(callCar.replace(/[^0-9]/g, '')) || 0;
            callCar = num < 10 ? 'EV-0' + num : 'EV-' + num;
        }

        let myCar = String(typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01').trim();
        if (!myCar.startsWith('EV-')) {
            const num = parseInt(myCar.replace(/[^0-9]/g, '')) || 1;
            myCar = num < 10 ? 'EV-0' + num : 'EV-' + num;
        }

        return callCar === myCar;
    }

    window.updateCallAlertUI = function() {
        const alertEl = document.getElementById('next-stop-alert');
        const noAlertEl = document.getElementById('next-stop-no-pax');
        const totalPaxEl = document.getElementById('call-alert-total-pax');
        const stationListEl = document.getElementById('call-alert-station-list');
        const nextStop = stationData[currentStopIndex];

        let callQueue = [];
        try {
            callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
        } catch(e) {}

        let latestCall = window.latestPassengerCallData;
        if (!latestCall || !latestCall.station) {
            try {
                latestCall = JSON.parse(localStorage.getItem('yru_latest_call') || 'null');
            } catch(e) {}
        }

        let activeCalls = [];
        if (callQueue && callQueue.length > 0) {
            activeCalls = callQueue.filter(item => {
                if (!item || (!item.station && !item.destination)) return false;
                if (item.status !== 'waiting' && item.status !== 'pending' && item.status !== 'accepted' && item.status !== 'onboard') return false;
                return isCallForThisCar(item);
            });
        }
        
        if (activeCalls.length === 0 && latestCall && (latestCall.station || latestCall.destination) && (latestCall.status === 'waiting' || latestCall.status === 'pending' || latestCall.status === 'accepted' || latestCall.status === 'onboard')) {
            if (isCallForThisCar(latestCall)) {
                activeCalls = [latestCall];
            }
        }

        const callDetailsList = [];
        const seenKeys = new Set();

        activeCalls.forEach(item => {
            const key = item.id || ((item.station || '') + '_' + (item.destination || '') + '_' + item.pax + '_' + (item.time || ''));
            if (!seenKeys.has(key)) {
                seenKeys.add(key);
                callDetailsList.push(item);
            }
        });

        let grandTotalPax = 0;

        if (callDetailsList.length > 0) {
            let listHtml = '';
            
            callDetailsList.forEach(item => {
                const paxCount = parseInt(item.pax) || 1;
                grandTotalPax += paxCount;
                
                const pickupName = item.station || 'ไม่ระบุจุดรับ';
                const destName = item.destination || 'ไม่ได้ระบุปลายทาง';
                const isOnboard = (item.status === 'onboard');

                const targetStation = isOnboard ? destName : pickupName;
                const isNextStop = (nextStop && (nextStop.name === targetStation || targetStation.includes(nextStop.name) || nextStop.name.includes(targetStation)));
                
                const nextTag = isNextStop 
                    ? `<span class="bg-pink-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full animate-pulse shadow-sm">(ถัดไป)</span>` 
                    : ``;

                if (isOnboard) {
                    if (isNextStop) {
                        // 🛑 DROPOFF ALERT CARD (เมื่อถึงจุดส่ง เช่น จุดจอด 7) — ดูง่าย สั้น กระชับ
                        listHtml += `
                            <div class="bg-amber-500 border-2 border-amber-300 text-white rounded-xl p-3 shadow-md flex flex-col gap-1 text-left">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-black flex items-center gap-2 text-yellow-100">
                                        <i class="fas fa-sign-out-alt text-lg"></i> 🛑 ผู้โดยสารลงจุดนี้ ${paxCount} คน
                                    </span>
                                    <span class="bg-amber-600/80 text-white text-[10px] font-bold px-2 py-0.5 rounded">จุดส่ง</span>
                                </div>
                                <p class="text-xs text-amber-100 font-bold pl-7">
                                    รับมาจาก: <span class="text-white font-extrabold">${pickupName}</span>
                                </p>
                            </div>
                        `;
                    } else {
                        // Regular Onboard status (ยังไม่ถึงจุดส่ง)
                        listHtml += `
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-2.5 shadow-xs flex items-center justify-between text-xs font-semibold">
                                <span class="text-blue-900 font-bold flex items-center gap-1.5"><i class="fas fa-bus-alt text-blue-600"></i> บนรถ ➔ ไปส่งที่ ${destName}</span>
                                <span class="text-blue-700 font-extrabold bg-blue-100 border border-blue-200 px-2 py-0.5 rounded">${paxCount} คน</span>
                            </div>
                        `;
                    }
                } else {
                    if (isNextStop) {
                        // 🟢 PICKUP ALERT CARD (เมื่อถึงจุดรับ เช่น จุดจอด 5) — ดูง่าย สั้น กระชับ
                        listHtml += `
                            <div class="bg-emerald-600 border-2 border-emerald-300 text-white rounded-xl p-3 shadow-md flex flex-col gap-1 text-left">
                                <div class="flex items-center justify-between">
                                    <span class="text-base font-black flex items-center gap-2 text-emerald-100">
                                        <i class="fas fa-user-plus text-lg"></i> มีผู้โดยสารรอขึ้น ${paxCount} คน
                                    </span>
                                    <span class="bg-emerald-700/80 text-white text-[10px] font-bold px-2 py-0.5 rounded">จุดรับ</span>
                                </div>
                                <p class="text-xs text-emerald-100 font-bold pl-7">
                                    ไปส่งที่: <span class="text-white font-extrabold">${destName}</span>
                                </p>
                            </div>
                        `;
                    } else {
                        // Regular Pickup status (ยังไม่ถึงจุดรับ)
                        listHtml += `
                            <div class="bg-white border border-pink-200 rounded-xl p-2.5 shadow-xs flex items-center justify-between text-xs font-semibold">
                                <span class="text-slate-800 font-bold flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-pink-500"></i> จุดรับ ${pickupName}</span>
                                <span class="text-pink-600 font-extrabold bg-pink-50 border border-pink-100 px-2 py-0.5 rounded">รออยู่ ${paxCount} คน</span>
                            </div>
                        `;
                    }
                }
            });

            if (totalPaxEl) totalPaxEl.innerText = `(รวม ${grandTotalPax} คน)`;
            if (stationListEl) stationListEl.innerHTML = listHtml;

            if (alertEl) alertEl.classList.remove('hidden');
            if (noAlertEl) noAlertEl.classList.add('hidden');
        } else {
            if (alertEl) alertEl.classList.add('hidden');
            if (noAlertEl) alertEl ? noAlertEl.classList.add('hidden') : null;
        }

        // 🔗 ผูกจำนวนผู้โดยสารในกล่องนับจำนวนผู้โดยสารบนรถ (กล่องวงกลม) ให้แสดงตามจำนวนคนเรียกรถ (ลูกศรชี้)
        // เมื่อมีคนกดเรียกรถ ยอดจะเด้งแสดงที่กล่องทันที และเมื่อถึงจุดจอดเสร็จสิ้นจะลบยอดออกโดยอัตโนมัติ
        currentOccupied = Math.min(maxCapacity, grandTotalPax);
        updateSeatsUI();
    }

    window.acceptPassengerCall = function() {
        if (window.latestPassengerCallData) {
            window.latestPassengerCallData.status = 'accepted';
            try {
                localStorage.setItem('yru_latest_call', JSON.stringify(window.latestPassengerCallData));
                window.dispatchEvent(new Event('storage'));
            } catch(e) {}
        }

        fetch('/api/accept-ev-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (window.latestPassengerCallData) {
                window.latestPassengerCallData.status = 'accepted';
            }
            updateCallAlertUI();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '✅ ตอบรับเรียบร้อย',
                    text: 'ระบบกำลังแจ้งเตือนผู้โดยสารว่ากำลังเดินทางไปรับ',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
        .catch(err => console.error('Error accepting call:', err));
    }

    window.handleArrivedAtStop = function() {
        const nextStop = stationData[currentStopIndex];
        const currentStopName = nextStop ? nextStop.name : '';

        let totalPickedUp = 0;
        let totalDroppedOff = 0;

        function isStationMatch(st1, st2) {
            if (!st1 || !st2) return false;
            const s1 = st1.toString().trim();
            const s2 = st2.toString().trim();
            if (s1 === s2 || s1.includes(s2) || s2.includes(s1)) return true;
            const num1 = s1.replace(/[^0-9]/g, '');
            const num2 = s2.replace(/[^0-9]/g, '');
            return (num1 !== '' && num1 === num2);
        }

        // 1. Filter out & update status in yru_call_queue for both pickups and destination dropoffs
        try {
            let callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
            let hasChanges = false;

            callQueue.forEach(item => {
                if (!item) return;
                if (!isCallForThisCar(item)) return;

                const pax = parseInt(item.pax) || 1;

                // A. Passengers GETTING ON at current station (Pickup)
                if (item.station && (item.status === 'waiting' || item.status === 'pending' || item.status === 'accepted')) {
                    if (isStationMatch(item.station, currentStopName)) {
                        totalPickedUp += pax;
                        hasChanges = true;

                        // If destination is specified & different from pickup, set status to 'onboard' so they auto-dropoff at destination
                        if (item.destination && item.destination.trim() !== '' && !isStationMatch(item.station, item.destination)) {
                            item.status = 'onboard';
                        } else {
                            item.status = 'completed';
                        }
                    }
                }
                // B. Passengers GETTING OFF at destination station (Automatic Deduction)
                else if (item.destination && (item.status === 'onboard' || item.status === 'waiting' || item.status === 'pending')) {
                    if (isStationMatch(item.destination, currentStopName)) {
                        totalDroppedOff += pax;
                        item.status = 'completed';
                        hasChanges = true;
                    }
                }
            });

            if (hasChanges) {
                localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));
            }
        } catch(e) {}

        // 2. Check latestPassengerCallData
        if (window.latestPassengerCallData && window.latestPassengerCallData.station) {
            if (isCallForThisCar(window.latestPassengerCallData)) {
                if (isStationMatch(window.latestPassengerCallData.station, currentStopName)) {
                    if (totalPickedUp === 0) {
                        totalPickedUp += (parseInt(window.latestPassengerCallData.pax) || 1);
                    }
                    window.latestPassengerCallData = null;
                    try { localStorage.removeItem('yru_latest_call'); } catch(e) {}
                    fetch('/api/clear-ev-request', { 
                        method: 'POST', 
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                        } 
                    }).catch(e => {});
                }
            }
        }

        // 3. Accumulate pax for round summary & update vehicle seats count
        if (totalPickedUp > 0) {
            roundTotalPaxServed += totalPickedUp;
        }
        const netChange = totalPickedUp - totalDroppedOff;
        if (netChange !== 0 && typeof changeSeatsOccupied === 'function') {
            changeSeatsOccupied(netChange);
        }

        // Show feedback notification to driver (Centered Modal Alert)
        if (typeof Swal !== 'undefined') {
            if (totalPickedUp > 0 && totalDroppedOff > 0) {
                Swal.fire({
                    title: '🚉 ถึงจุดจอด: ' + currentStopName,
                    html: `รับผู้โดยสารขึ้นรถ <b>${totalPickedUp}</b> คน<br>ตัดยอดผู้โดยสารลงรถอัตโนมัติ <b>${totalDroppedOff}</b> คน`,
                    icon: 'info',
                    timer: 2500,
                    showConfirmButton: true,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#3B82F6'
                });
            } else if (totalDroppedOff > 0) {
                Swal.fire({
                    title: 'ถึงจุดจอดปลายทาง',
                    html: `ตัดยอดผู้โดยสารลงรถอัตโนมัติ (<b>${currentStopName}</b>) <b>${totalDroppedOff}</b> คน`,
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: true,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#EC4899'
                });
            } else if (totalPickedUp > 0) {
                Swal.fire({
                    title: '🚌 ถึงจุดรับผู้โดยสาร',
                    html: `รับผู้โดยสารขึ้นรถ (<b>${currentStopName}</b>) <b>${totalPickedUp}</b> คน`,
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: true,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10B981'
                });
            }
        }

        // 4. Notify storage & update UI
        try { window.dispatchEvent(new Event('storage')); } catch(e) {}

        // 5. Advance to next station & auto-increment round number when completing a full loop
        const prevStopIndex = currentStopIndex;
        currentStopIndex = (currentStopIndex + 1) % stationData.length;

        if (prevStopIndex === stationData.length - 1 && currentStopIndex === 0) {
            incrementCarRoundNumber();
        }

        updateNextStopUI();
    }

    window.handleSkipStop = function() {
        const prevStopIndex = currentStopIndex;
        currentStopIndex = (currentStopIndex + 1) % stationData.length;

        if (prevStopIndex === stationData.length - 1 && currentStopIndex === 0) {
            incrementCarRoundNumber();
        }

        updateNextStopUI();
    }

    let lastNotifiedCallId = null; 

    window.checkPassengerCalls = function() {
        let localCall = null;
        try {
            localCall = JSON.parse(localStorage.getItem('yru_latest_call') || 'null');
            if (!localCall) {
                const queue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
                if (queue.length > 0 && queue[0].status === 'waiting') {
                    localCall = queue[0];
                }
            }
        } catch(e) {}

        // Use local storage data instantly if available (0ms delay)
        if (localCall && localCall.station) {
            let activeCall = localCall;
            if (!isCallForThisCar(activeCall)) {
                activeCall = null;
            }

            window.latestPassengerCallData = activeCall;
            if (activeCall && activeCall.station) {
                window.pendingPax = parseInt(activeCall.pax) || 0;
                const callId = (activeCall.time || activeCall.timestamp || '') + '_' + activeCall.station + '_' + activeCall.pax + '_' + (activeCall.car_id || '');
                if (lastNotifiedCallId !== callId && activeCall.status !== 'accepted') {
                    lastNotifiedCallId = callId;
                    playAlertSound();
                }
            }
            updateCallAlertUI();
            return;
        }

        // Background API sync fallback
        fetch('/api/check-ev-request?t=' + new Date().getTime())
        .then(response => {
            if (!response.ok) throw new Error('API server unavailable');
            return response.json();
        })
        .then(data => {
            let activeCall = data;
            if (activeCall && !isCallForThisCar(activeCall)) {
                activeCall = null;
            }

            window.latestPassengerCallData = activeCall;
            if (activeCall && activeCall.station) {
                window.pendingPax = parseInt(activeCall.pax) || 0;
                const callId = (activeCall.time || activeCall.timestamp || '') + '_' + activeCall.station + '_' + activeCall.pax + '_' + (activeCall.car_id || '');
                if (lastNotifiedCallId !== callId && activeCall.status !== 'accepted') {
                    lastNotifiedCallId = callId;
                    playAlertSound();
                }
            }
            updateCallAlertUI();
        })
        .catch(error => {
            window.latestPassengerCallData = null;
            updateCallAlertUI();
        });
    }

    window.clearPassengerCall = function() {
        if (window.pendingPax) {
            changeSeatsOccupied(window.pendingPax);
            window.pendingPax = 0;
        }

        try {
            localStorage.removeItem('yru_latest_call');
        } catch(e) {}

        window.latestPassengerCallData = null;

        fetch('/api/clear-ev-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            window.latestPassengerCallData = null;
            updateCallAlertUI();
        })
        .catch(error => {
            window.latestPassengerCallData = null;
            updateCallAlertUI();
        });
    }

    function playAlertSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } catch(e) {}
    }

    // ตั้ง Polling ตรวจสอบสายเรียกเข้าจากผู้ใช้และเช็คการล็อกสิทธิ์จากแอดมิน (หยุดทำงานชั่วคราวเมื่อสลับแท็บไปที่อื่น)
    setInterval(() => {
        if (document.hidden) return;
        checkPassengerCalls();
        checkAdminVehicleLockStatus();
    }, 4000);
    checkPassengerCalls();
    checkAdminVehicleLockStatus();

    // ⚠️ ลบ storage listener ตัวที่ 2 ออก (ซ้ำซ้อน+ไม่มี debounce) — ใช้ตัว debounced ด้านบนแทน

    // เริ่มต้นทำงานด้วยการตั้งค่าสถานะแรกเริ่มให้กับรถและแสดงข้อมูลทันที
    if (typeof syncDriverVehicleData === 'function') syncDriverVehicleData();
    if (typeof loadDynamicStationData === 'function') loadDynamicStationData();
    if (typeof drawStationMarkers === 'function') drawStationMarkers();
    if (typeof initTramMarkers === 'function') initTramMarkers();
    updateRoundDisplay();
    // ✅ ใช้ setTimeout เพื่อไม่บล็อก JS thread ในช่วง init
    setTimeout(() => { syncDriverStatus('normal'); }, 100);

    function toggleProfileDropdown() {
        const menu = document.getElementById('profileDropdownMenu');
        const icon = document.getElementById('profileDropdownIcon');
        if (!menu) return;
        
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

    document.addEventListener('click', function(e) {
        const container = document.getElementById('profileDropdownContainer');
        const menu = document.getElementById('profileDropdownMenu');
        const icon = document.getElementById('profileDropdownIcon');
        if (container && menu && !container.contains(e.target) && !menu.classList.contains('hidden')) {
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                menu.classList.add('hidden');
            }, 200);
            if (icon) icon.classList.remove('rotate-180');
        }
    });
