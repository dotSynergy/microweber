#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Configuration
const config = {
    source: './resources/assets/js/',
    destination: '../../public/modules/ai/js/'
};

// Ensure destination directory exists
function ensureDirectoryExists(dirPath) {
    if (!fs.existsSync(dirPath)) {
        fs.mkdirSync(dirPath, { recursive: true });
        console.log(`Created directory: ${dirPath}`);
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
        ensureDirectoryExists(path.dirname(destination));

        // Copy the file
        fs.copyFileSync(source, destination);
        console.log(`Copied: ${source} -> ${destination}`);

        return true;
    } catch (error) {
        console.error(`Error copying file: ${error.message}`);
        return false;
    }
}

// Copy all JS files from source directory (flat copy)
function copyFiles() {
    let successCount = 0;
    let totalCount = 0;

    // Ensure destination directory exists
    ensureDirectoryExists(config.destination);

    try {
        const files = fs.readdirSync(config.source);
        files.forEach(file => {
            if (path.extname(file) === '.js') {
                totalCount++;
                const sourcePath = path.join(config.source, file);
                const destPath = path.join(config.destination, file);

                if (copyFile(sourcePath, destPath)) {
                    successCount++;
                }
            }
        });
    } catch (error) {
        console.error(`Error reading source directory: ${error.message}`);
        return false;
    }

    console.log(`Copied ${successCount}/${totalCount} files successfully`);
    return successCount === totalCount;
}

// Main build function
function build() {
    console.log('Starting build process...');

    const success = copyFiles();

    if (success) {
        console.log('Build completed successfully!');
        return true;
    } else {
        console.log('Build failed!');
        return false;
    }
}

// Watch mode function
function watch() {
    console.log('Starting watch mode...');
    console.log(`Watching: ${config.source}`);

    // Initial build
    if (!build()) {
        process.exit(1);
    }

    // Watch for changes in the source directory
    fs.watch(config.source, { recursive: true }, (eventType, filename) => {
        if (filename && path.extname(filename) === '.js') {
            console.log(`\nFile changed: ${filename}`);

            const sourcePath = path.join(config.source, filename);
            const destPath = path.join(config.destination, filename);
            copyFile(sourcePath, destPath);
        }
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
