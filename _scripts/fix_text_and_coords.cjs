const fs = require('fs');
const path = require('path');

const homePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'home', 'index.blade.php');
let homeContent = fs.readFileSync(homePath, 'utf8');

// Update isVehicleInGarage in home/index.blade.php
const isVehicleInGarageTarget = `    function isVehicleInGarage(tram) {
        if (!tram) return false;
        var carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
        var rawStatus = (tram.status || carData.status || '').toString();

        try {
            var rawLive = localStorage.getItem('yru_car_status_' + tram.id);
            if (rawLive) {
                var parsed = JSON.parse(rawLive);
                if (parsed.status) rawStatus = parsed.status.toString();
                else if (parsed.driver_status) rawStatus = parsed.driver_status.toString();
            }
        } catch(e) {}
        
        var isMaintenanceOrSuspended = 
            rawStatus.includes('ขัดข้อง') || 
            rawStatus.includes('ปรับปรุง') || 
            rawStatus.includes('ระงับ') || 
            rawStatus === 'MAINTENANCE' || 
            rawStatus === 'SUSPENDED' ||
            rawStatus === 'broken';

        if (isMaintenanceOrSuspended) return true;

        var baseIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
        var isBase = baseIds.includes(tram.id);

        if (isBase && (rawStatus === 'พร้อมใช้งาน' || rawStatus === 'ปกติกำลังขับ' || rawStatus === 'ACTIVE' || rawStatus === 'normal' || rawStatus === 'pause' || rawStatus === 'พักเบรค' || rawStatus === 'พักเบรก' || rawStatus.includes('พัก'))) {
            return false;
        }

        var stationId = (tram.current_station_id || '').toString();
        var isAtGarage = (stationId === 'GARAGE' || stationId === 'GARAGE_STATION');
        return isAtGarage;
    }`;

const isVehicleInGarageReplacement = `    // ล้างสถานะตกค้างของ EV-01 ให้อยู่จุดจอดที่ 1 เสมอ
    try {
        ['yru_car_status_EV-01', 'yru_car_status_1'].forEach(k => {
            const raw = localStorage.getItem(k);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed.status === 'รถขัดข้อง' || parsed.driver_status === 'รถขัดข้อง' || parsed.status === 'pause' || parsed.driver_status === 'pause') {
                    parsed.status = 'พร้อมใช้งาน';
                    parsed.driver_status = 'พร้อมใช้งาน';
                    localStorage.setItem(k, JSON.stringify(parsed));
                }
            }
        });
        ['yru_trams_v18', 'yru_trams_v16', 'yru_trams_v15'].forEach(key => {
            let list = JSON.parse(localStorage.getItem(key) || '[]');
            if (Array.isArray(list)) {
                let found = list.find(t => t.id === 'EV-01');
                if (found) {
                    found.status = 'พร้อมใช้งาน';
                    found.coords = '6.549929, 101.291254';
                    localStorage.setItem(key, JSON.stringify(list));
                }
            }
        });
    } catch(e) {}

    function isVehicleInGarage(tram) {
        if (!tram) return false;
        // รถคันที่ 1 (EV-01) ให้อยู่จุดจอดที่ 1 (ประตูหลังมอ.) เสมอ
        if (tram.id === 'EV-01') return false;

        var carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
        var rawStatus = (tram.status || carData.status || '').toString();

        try {
            var rawLive = localStorage.getItem('yru_car_status_' + tram.id);
            if (rawLive) {
                var parsed = JSON.parse(rawLive);
                if (parsed.status) rawStatus = parsed.status.toString();
                else if (parsed.driver_status) rawStatus = parsed.driver_status.toString();
            }
        } catch(e) {}
        
        var isMaintenanceOrSuspended = 
            rawStatus.includes('ปรับปรุง') || 
            rawStatus.includes('ระงับ') || 
            rawStatus === 'MAINTENANCE' || 
            rawStatus === 'SUSPENDED';

        if (isMaintenanceOrSuspended) return true;

        var baseIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
        var isBase = baseIds.includes(tram.id);

        if (isBase && (rawStatus === 'พร้อมใช้งาน' || rawStatus === 'ปกติกำลังขับ' || rawStatus === 'ACTIVE' || rawStatus === 'normal' || rawStatus === 'pause' || rawStatus === 'พักเบรค' || rawStatus === 'พักเบรก' || rawStatus.includes('พัก'))) {
            return false;
        }

        var stationId = (tram.current_station_id || '').toString();
        var isAtGarage = (stationId === 'GARAGE' || stationId === 'GARAGE_STATION');
        return isAtGarage;
    }`;

if (homeContent.includes('function isVehicleInGarage(tram) {')) {
    const startPos = homeContent.indexOf('function isVehicleInGarage(tram) {');
    const endPos = homeContent.indexOf('return isAtGarage;\n    }', startPos);
    if (startPos !== -1 && endPos !== -1) {
        const fullOld = homeContent.substring(startPos, endPos + 'return isAtGarage;\n    }'.length);
        homeContent = homeContent.replace(fullOld, isVehicleInGarageReplacement.trim());
        fs.writeFileSync(homePath, homeContent, 'utf8');
        console.log('Updated isVehicleInGarage in home/index.blade.php');
    }
}
