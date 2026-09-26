const fs = require('fs');
const vm = require('vm');
const content = fs.readFileSync('scratch/rendered_admin.html', 'utf8');
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let count = 0;
while ((match = scriptRegex.exec(content)) !== null) {
    count++;
    const startPos = match.index;
    const lineOffset = content.substring(0, startPos).split('\n').length;
    let code = match[1];
    if (!code.trim() || match[0].includes('src=')) continue;
    try {
        new vm.Script(code, { lineOffset: lineOffset, filename: 'rendered_admin.html' });
        console.log(`Script ${count} (starting line ${lineOffset}) is valid!`);
    } catch(err) {
        console.error(`Script ${count} (starting line ${lineOffset}) error:`, err);
        // Print the lines around error if line number available
        const lines = content.split('\n');
        const errLine = err.stack.match(/rendered_admin\.html:(\d+)/);
        if (errLine) {
            const lNum = parseInt(errLine[1]);
            console.log(`--- Error around line ${lNum} ---`);
            for (let i = Math.max(0, lNum - 10); i < Math.min(lines.length, lNum + 10); i++) {
                console.log(`${i+1}: ${lines[i]}`);
            }
        }
    }
}
