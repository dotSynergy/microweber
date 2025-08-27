#!/usr/bin/env node

const fs = require('fs');
const path = require('path');

// Configuration
const config = {
    assets: [
        {
            source: './resources/assets/js/',
            destination: '../../public/modules/cookie_notice/js/',
            extensions: ['.js']
        },
        {
            source: './resources/assets/css/',
            destination: '../../public/modules/cookie_notice/css/',
            extensions: ['.css']
        }
    ]
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

// Copy files from a directory (flat copy)
function copyAssetDirectory(assetConfig) {
    let successCount = 0;
    let totalCount = 0;

    // Ensure destination directory exists
    ensureDirectoryExists(assetConfig.destination);

    // Check if source directory exists
    if (!fs.existsSync(assetConfig.source)) {
        console.log(`Source directory does not exist: ${assetConfig.source}`);
        return { success: 0, total: 0 };
    }

    try {
        const files = fs.readdirSync(assetConfig.source);
        files.forEach(file => {
            const fileExtension = path.extname(file).toLowerCase();

            // Check if file has a valid extension for this asset type
            if (assetConfig.extensions.includes(fileExtension)) {
                totalCount++;
                const sourcePath = path.join(assetConfig.source, file);
                const destPath = path.join(assetConfig.destination, file);

                if (copyFile(sourcePath, destPath)) {
                    successCount++;
                }
            }
        });
    } catch (error) {
        console.error(`Error reading source directory: ${error.message}`);
        return { success: 0, total: 0 };
    }

    return { success: successCount, total: totalCount };
}

// Main build function
function build() {
    console.log('Starting build process...');

    let totalSuccess = 0;
    let totalFiles = 0;

    config.assets.forEach(assetConfig => {
        console.log(`\nProcessing ${assetConfig.source}...`);
        const result = copyAssetDirectory(assetConfig);
        totalSuccess += result.success;
        totalFiles += result.total;

        if (result.total > 0) {
            console.log(`Copied ${result.success}/${result.total} files from ${assetConfig.source}`);
        }
    });

    if (totalSuccess === totalFiles && totalFiles > 0) {
        console.log(`\nBuild completed successfully! Copied ${totalSuccess}/${totalFiles} files total.`);
        return true;
    } else if (totalFiles === 0) {
        console.log('\nNo files found to copy.');
        return true;
    } else {
        console.log(`\nBuild failed! Only ${totalSuccess}/${totalFiles} files copied.`);
        return false;
    }
}

// Watch mode function
function watch() {
    console.log('Starting watch mode...');

    config.assets.forEach(assetConfig => {
        if (fs.existsSync(assetConfig.source)) {
            console.log(`Watching: ${assetConfig.source}`);
        }
    });

    // Initial build
    if (!build()) {
        process.exit(1);
    }

    // Watch for changes in all asset directories
    config.assets.forEach(assetConfig => {
        if (fs.existsSync(assetConfig.source)) {
            fs.watch(assetConfig.source, { recursive: true }, (eventType, filename) => {
                if (filename) {
                    const fileExtension = path.extname(filename).toLowerCase();

                    // Check if file has a valid extension for this asset type
                    if (assetConfig.extensions.includes(fileExtension)) {
                        console.log(`\nFile changed: ${filename} in ${assetConfig.source}`);

                        const sourcePath = path.join(assetConfig.source, filename);
                        const destPath = path.join(assetConfig.destination, filename);

                        if (fs.existsSync(sourcePath)) {
                            copyFile(sourcePath, destPath);
                        }
                    }
                }
            });
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
