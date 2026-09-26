const fs = require('fs');
const path = require('path');

// 1. Home
const homePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'home', 'index.blade.php');
let home = fs.readFileSync(homePath, 'utf8');

const cleanupSnippet = `
<script>
    // Force client auto-cleanup of stale EV-01 test statuses
    (function() {
        try {
            ['yru_car_status_EV-01', 'yru_car_status_1'].forEach(k => {
                const raw = localStorage.getItem(k);
                if (raw) {
                    const p = JSON.parse(raw);
                    if (p.status === 'รถขัดข้อง' || p.status === 'pause' || p.status === 'พักเบรค' || p.status === 'พักเบรก' || p.driver_status === 'รถขัดข้อง') {
                        localStorage.removeItem(k);
                    }
                }
            });
            ['yru_trams_v18', 'yru_trams_v16', 'yru_trams_v15'].forEach(key => {
                let list = JSON.parse(localStorage.getItem(key) || '[]');
                if (Array.isArray(list)) {
                    let ev01 = list.find(t => t.id === 'EV-01');
                    if (ev01) {
                        if (ev01.status === 'รถขัดข้อง' || ev01.status === 'MAINTENANCE' || ev01.coords === '6.548900, 101.291700') {
                            ev01.status = 'พร้อมใช้งาน';
                            ev01.coords = '6.549929, 101.291254';
                            ev01.current_station_id = null;
                            localStorage.setItem(key, JSON.stringify(list));
                        }
                    }
                }
            });
        } catch(e) {}
    })();
`;

if (!home.includes('Force client auto-cleanup of stale EV-01')) {
    home = home.replace('<script>\n    // ===== 📌 Garage / Depot & Station Constants', cleanupSnippet + '\n    // ===== 📌 Garage / Depot & Station Constants');
    fs.writeFileSync(homePath, home, 'utf8');
    console.log('Added auto cleanup to home');
}

// 2. Tracking
const trackingPath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'tracking', 'index.blade.php');
let tracking = fs.readFileSync(trackingPath, 'utf8');

if (!tracking.includes('Force client auto-cleanup of stale EV-01')) {
    tracking = tracking.replace('<script>\n    // ===== 📌 Garage / Depot & Station Constants', cleanupSnippet + '\n    // ===== 📌 Garage / Depot & Station Constants');
    fs.writeFileSync(trackingPath, tracking, 'utf8');
    console.log('Added auto cleanup to tracking');
}
