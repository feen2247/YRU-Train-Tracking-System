const fs = require('fs');
const path = require('path');

// 1. Home
const homePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'home', 'index.blade.php');
let home = fs.readFileSync(homePath, 'utf8');

// Update isVehicleInGarage in home
const isVehicleInGarageHome = `    function isVehicleInGarage(tram) {
        if (!tram) return false;
        var carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
        var rawStatus = (tram.status || carData.status || '').toString().trim();

        try {
            var rawLive = localStorage.getItem('yru_car_status_' + tram.id);
            if (rawLive) {
                var parsed = JSON.parse(rawLive);
                if (parsed.status) rawStatus = parsed.status.toString().trim();
                else if (parsed.driver_status) rawStatus = parsed.driver_status.toString().trim();
            }
        } catch(e) {}
        
        var isMaintenanceOrSuspended = 
            rawStatus === 'รถขัดข้อง' ||
            rawStatus === 'ระงับการใช้งาน' ||
            rawStatus.includes('ขัดข้อง') || 
            rawStatus.includes('ปรับปรุง') || 
            rawStatus.includes('ระงับ') || 
            rawStatus === 'MAINTENANCE' || 
            rawStatus === 'SUSPENDED';

        if (isMaintenanceOrSuspended) return true;

        var stationId = (tram.current_station_id || '').toString();
        var isAtGarage = (stationId === 'GARAGE' || stationId === 'GARAGE_STATION');
        return isAtGarage;
    }`;

// Replace isVehicleInGarage in home
const homeGarageRegex = /function isVehicleInGarage\(tram\) \{[\s\S]*?return isAtGarage;\s*\}/;
if (homeGarageRegex.test(home)) {
    home = home.replace(homeGarageRegex, isVehicleInGarageHome.trim());
}

// Ensure defaultTramCoords in home
const defaultTramCoordsBlock = `    const defaultTramCoords = {
        "EV-01": "6.549929, 101.291254", // จุดจอด 1 ประตูหลังมอ.
        "EV-02": "6.549100, 101.290467", // จุดจอด 2 ตึกศิลปะ
        "EV-03": "6.547835, 101.289502", // จุดจอด 3 ศูนย์วิทยาศาสตร์
        "EV-04": "6.547224, 101.289471", // จุดจอด 4 คณะวิทยาศาสตร์
        "EV-05": "6.547311, 101.288880", // จุดจอด 5 คณะสังคมศาสตร์
        "EV-06": "6.548822, 101.288523", // จุดจอด 6 อาคารเรียน 20
        "EV-07": "6.549225, 101.289286", // จุดจอด 7 คณะวิทยาการจัดการ
        "EV-08": "6.549929, 101.291254", // จุดจอด 1 ประตูหลังมอ.
        "EV-09": "6.547835, 101.289502", // จุดจอด 3 ศูนย์วิทยาศาสตร์
        "EV-10": "6.547311, 101.288880"  // จุดจอด 5 คณะสังคมศาสตร์
    };`;

const homeCoordsRegex = /const defaultTramCoords = \{[\s\S]*?\};/;
if (homeCoordsRegex.test(home)) {
    home = home.replace(homeCoordsRegex, defaultTramCoordsBlock.trim());
}

fs.writeFileSync(homePath, home, 'utf8');
console.log('home/index.blade.php updated successfully');

// 2. Tracking
const trackingPath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'tracking', 'index.blade.php');
let tracking = fs.readFileSync(trackingPath, 'utf8');

const trackingGarageRegex = /function isVehicleInGarage\(tram\) \{[\s\S]*?return isAtGarage;\s*\}/;
if (trackingGarageRegex.test(tracking)) {
    tracking = tracking.replace(trackingGarageRegex, isVehicleInGarageHome.trim());
}

const trackingCoordsRegex = /const defaultTramCoords = \{[\s\S]*?\};/;
if (trackingCoordsRegex.test(tracking)) {
    tracking = tracking.replace(trackingCoordsRegex, defaultTramCoordsBlock.trim());
}

fs.writeFileSync(trackingPath, tracking, 'utf8');
console.log('tracking/index.blade.php updated successfully');

// 3. Admin index
const adminPath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'admin', 'index.blade.php');
let admin = fs.readFileSync(adminPath, 'utf8');

const adminCoordsRegex = /const defaultTramCoords = \{[\s\S]*?\};/;
if (adminCoordsRegex.test(admin)) {
    admin = admin.replace(adminCoordsRegex, defaultTramCoordsBlock.trim());
}

fs.writeFileSync(adminPath, admin, 'utf8');
console.log('admin/index.blade.php updated successfully');
