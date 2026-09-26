const fs = require('fs');
const path = require('path');

// 1. Home
const homePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'home', 'index.blade.php');
let home = fs.readFileSync(homePath, 'utf8');

home = home.replace(
    `            if (tram.coords) {`,
    `            if (tram.id === 'EV-01') {
                latLng = L.latLng(6.549929, 101.291254);
            } else if (tram.coords) {`
);

fs.writeFileSync(homePath, home, 'utf8');
console.log('Fixed EV-01 coords in home');

// 2. Tracking
const trackingPath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'tracking', 'index.blade.php');
let tracking = fs.readFileSync(trackingPath, 'utf8');

tracking = tracking.replace(
    `            if (tram.coords) {`,
    `            if (tram.id === 'EV-01') {
                latLng = [6.549929, 101.291254];
            } else if (tram.coords) {`
);

fs.writeFileSync(trackingPath, tracking, 'utf8');
console.log('Fixed EV-01 coords in tracking');
