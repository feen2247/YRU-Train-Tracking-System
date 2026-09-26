import fs from 'fs';
const content = fs.readFileSync('resources/views/passenger/tracking/index.blade.php', 'utf8');
const styleMatches = content.match(/<style[\s\S]*?<\/style>/gi) || [];
styleMatches.forEach((s, idx) => {
    console.log('Style block ' + idx + ':');
    if (s.includes('hidden') || s.includes('modal') || s.includes('z-index') || s.includes('inbox')) {
        console.log(s);
    }
});
