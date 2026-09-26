const fs = require('fs');

const currentPath = 'resources/views/passenger/executive/index.blade.php';
const cContent = fs.readFileSync(currentPath, 'utf8');

const lines = cContent.split(/\r?\n/);
console.log('Lines 1580-1650:');
console.log(lines.slice(1580, 1650).map((l, i) => (1581 + i) + ': ' + l).join('\n'));
