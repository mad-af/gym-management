import { createWriteStream, mkdirSync, existsSync } from 'fs';
import { dirname } from 'path';
import { thumbs } from '@dicebear/collection';
import { createAvatar } from '@dicebear/core';
import sharp from 'sharp';

// Arguments: [seed] [outputPath] [format: svg|png|base64]
const seed = process.argv[2] || 'User';
const outputPath = process.argv[3];
const format = process.argv[4] || 'svg';

// Ensure directory exists
if (outputPath) {
    const dir = dirname(outputPath);
    if (!existsSync(dir)) {
        mkdirSync(dir, { recursive: true });
    }
}

const options = {
    seed: seed,
    size: 200,
    backgroundColor: ['b6e3f4', 'c0aede', 'd1d4f9', 'ffd5dc', 'ffdfbf'],
};

// Generate Avatar
const avatar = createAvatar(thumbs, options);

async function saveAvatar() {
    try {
        if (format === 'base64') {
            const dataUri = await avatar.toDataUri();
            process.stdout.write(dataUri); // Output to stdout for PHP to capture
        } else if (format === 'png') {
            const svgString = avatar.toString();

            if (outputPath) {
                await sharp(Buffer.from(svgString)).png().toFile(outputPath);

                console.log(`Saved PNG to ${outputPath}`);
            } else {
                const pngBuffer = await sharp(Buffer.from(svgString))
                    .png()
                    .toBuffer();
                process.stdout.write(pngBuffer.toString('base64'));
            }
        } else {
            // Default SVG
            const svg = avatar.toString();
            if (outputPath) {
                const stream = createWriteStream(outputPath);
                stream.write(svg);
                stream.end();
                console.log(`Saved SVG to ${outputPath}`);
            } else {
                process.stdout.write(svg);
            }
        }
    } catch (e) {
        console.error('Error generating avatar:', e);
        process.exit(1);
    }
}

saveAvatar();
