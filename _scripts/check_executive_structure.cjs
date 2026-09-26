const fs = require('fs');

const backupPath = '_archives/backups/YRU_Deploy_Ready/resources/views/passenger/executive.blade.php';
const currentPath = 'resources/views/passenger/executive/index.blade.php';

const bContent = fs.readFileSync(backupPath, 'utf8');
const cContent = fs.readFileSync(currentPath, 'utf8');

console.log('=== Backup ===');
bContent.split(/\r?\n/).forEach((l, i) => {
    if (l.includes('id="page-') || (l.includes('function ') && !l.includes('=>'))) {
        console.log((i + 1) + ': ' + l.trim());
    }
});

console.log('\n=== Current Executive Index ===');
cContent.split(/\r?\n/).forEach((l, i) => {
    if (l.includes('id="page-') || (l.includes('function ') && !l.includes('=>'))) {
        console.log((i + 1) + ': ' + l.trim());
    }
});
