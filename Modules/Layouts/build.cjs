#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Configuration
const config = {
    source: './resources/assets/js/layouts-module-settings.js',
    destination: '../../public/modules/layouts/js/layouts-module-settings.js'
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

    const success = copyFile(config.source, config.destination);

    if (success) {
        console.log('Build completed successfully!');
        process.exit(0);
    } else {
        console.log('Build failed!');
        process.exit(1);
    }
}

// Watch mode function
function watch() {
    console.log('Starting watch mode...');
    console.log(`Watching: ${config.source}`);

    // Initial build
    build();

    // Watch for changes
    fs.watchFile(config.source, (curr, prev) => {
        console.log(`\nFile changed: ${config.source}`);
        copyFile(config.source, config.destination);
    });

    console.log('Press Ctrl+C to stop watching...');
}

// Command line argument handling
const args = process.argv.slice(2);

if (args.includes('--watch') || args.includes('-w')) {
    watch();
} else {
    build();
}
