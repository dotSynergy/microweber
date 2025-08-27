#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Configuration
const config = {
    files: [
        {
            source: './resources/assets/js/sortableMenu.js',
            destination: '../../public/modules/menu/js/sortableMenu.js'
        },
        {
            source: './resources/assets/js/menu-quick-settings.js',
            destination: '../../public/modules/menu/js/menu-quick-settings.js'
        }
    ]
};

// Ensure destination directory exists
function ensureDirectoryExists(filePath) {
    const dirname = path.dirname(filePath);
    if (!fs.existsSync(dirname)) {
        fs.mkdirSync(dirname, { recursive: true });
        console.log(`Created directory: ${dirname}`);
    }
}

// Copy file function
function copyFile(source, destination) {
    try {
        // Check if source file exists
        if (!fs.existsSync(source)) {
            throw new Error(`Source file does not exist: ${source}`);
        }

        // Ensure destination directory exists
        ensureDirectoryExists(destination);

        // Copy the file
        fs.copyFileSync(source, destination);
        console.log(`Copied: ${source} -> ${destination}`);

        return true;
    } catch (error) {
        console.error(`Error copying file: ${error.message}`);
        return false;
    }
}

// Main build function
function build() {
    console.log('Starting build process...');

    let successCount = 0;

    config.files.forEach(file => {
        if (copyFile(file.source, file.destination)) {
            successCount++;
        }
    });

    if (successCount === config.files.length) {
        console.log(`Build completed successfully! Copied ${successCount}/${config.files.length} files.`);
        return true;
    } else {
        console.log(`Build failed! Only ${successCount}/${config.files.length} files copied.`);
        return false;
    }
}

// Watch mode function
function watch() {
    console.log('Starting watch mode...');
    config.files.forEach(file => {
        console.log(`Watching: ${file.source}`);
    });

    // Initial build
    if (!build()) {
        process.exit(1);
    }

    // Watch for changes on each source file
    config.files.forEach(file => {
        fs.watchFile(file.source, (curr, prev) => {
            console.log(`\nFile changed: ${file.source}`);
            copyFile(file.source, file.destination);
        });
    });

    console.log('Press Ctrl+C to stop watching...');
}

// Command line argument handling
const args = process.argv.slice(2);

if (args.includes('--watch') || args.includes('-w')) {
    watch();
} else {
    const success = build();
    process.exit(success ? 0 : 1);
}
