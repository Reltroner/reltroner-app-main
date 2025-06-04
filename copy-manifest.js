// copy-manifest.js
import fs from 'fs';
import path from 'path';

const from = path.resolve('public/build/.vite/manifest.json');
const to = path.resolve('public/build/manifest.json');

try {
    fs.copyFileSync(from, to);
    console.log('✅ Manifest copied to public/build/manifest.json');
} catch (err) {
    console.error('❌ Failed to copy manifest.json:', err.message);
}
