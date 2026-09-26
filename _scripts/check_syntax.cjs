const fs = require('fs');
const vm = require('vm');
const content = fs.readFileSync('resources/views/passenger/tracking/index.blade.php', 'utf8');
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;
while ((match = scriptRegex.exec(content)) !== null) {
    count++;
    if (count === 8) {
        const startPos = match.index;
        const lineOffset = content.substring(0, startPos).split('\n').length;
        let code = match[1];
        // Replace blade variables with dummy identifiers/strings
        code = code.replace(/["']\{\{\s*(.*?)\s*\}\}["']/g, '"dummy"');
        code = code.replace(/\{\{\s*(.*?)\s*\}\}/g, '"dummy"');
        code = code.replace(/\{!!\s*(.*?)\s*!!\}/g, '"dummy"');
        code = code.replace(/@json\(.*?\)/g, '{}');
        
        try {
            new vm.Script(code, { lineOffset: lineOffset, filename: 'tracking.blade.php' });
            console.log('Script 8 is valid!');
        } catch(err) {
            console.error('Script 8 error at:', err.stack);
        }
    }
}
