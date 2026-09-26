const fs = require('fs');
const vm = require('vm');
const content = fs.readFileSync('resources/views/passenger/admin/index.blade.php', 'utf8');
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;
while ((match = scriptRegex.exec(content)) !== null) {
    count++;
    const startPos = match.index;
    const lineOffset = content.substring(0, startPos).split('\n').length;
    let code = match[1];
    if (!code.trim() || match[0].includes('src=')) continue;
    code = code.replace(/['"]\{\{\s*(.*?)\s*\}['"]/g, '"dummy"');
    code = code.replace(/\{\{\s*(.*?)\s*\}\}/g, '"dummy"');
    code = code.replace(/\{!!\s*(.*?)\s*!!\}/g, '"dummy"');
    code = code.replace(/@json\(.*?\)/g, '{}');
    code = code.replace(/@php[\s\S]*?@endphp/g, '');
    try {
        new vm.Script(code, { lineOffset: lineOffset, filename: 'admin/index.blade.php' });
        console.log(`Script ${count} (line ${lineOffset}) is valid!`);
    } catch(err) {
        console.error(`Script ${count} (line ${lineOffset}) error:`, err.message);
    }
}
