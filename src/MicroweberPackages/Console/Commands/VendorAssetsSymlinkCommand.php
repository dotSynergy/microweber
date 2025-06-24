<?php

namespace MicroweberPackages\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class VendorAssetsSymlinkCommand extends Command
{
    protected $name = 'microweber:vendor-assets-symlink';
    protected $description = 'Symlinks the system assets on your website to their vendor locations';

    public function __construct()
    {
        parent::__construct();
    }    public function handle()
    {
        $this->info('Creating symlinks for vendor assets...');

        $publicPath = public_path();
        $basePath = base_path();

        $allSystemLinks = [
            // filament system assets
            'js/filament/filament/echo.js' => 'vendor/filament/filament/dist/echo.js',
            'js/filament/filament/app.js' => 'vendor/filament/filament/dist/index.js',
            'js/filament/filament/forms/components' => 'vendor/filament/forms/dist/components',
            'js/filament/forms' => 'vendor/filament/forms/dist',
            'js/filament/notifications/notifications.js' =>  'vendor/filament/notifications/dist/index.js',
            'js/filament/support/support.js' =>  'vendor/filament/support/dist/index.js',
            'js/filament/support/async-alpine.js' =>  'vendor/filament/support/dist/async-alpine.js',
            'js/filament/tables/components' => 'vendor/filament/tables/dist/components',
            'js/filament/widgets' => 'vendor/filament/widgets/dist',

           // filamnent libs
//            'js/coolsam/flatpickr/components' => 'vendor/coolsam/flatpickr/resources/js/dist/components/flatpickr-component.js',
//            'js/coolsam/flatpickr/flatpickr-week-select-plugin.js' => 'vendor/bobimicroweber/',
//            'js/coolsam/flatpickr/flatpickr-range-plugin.js' => 'vendor/bobimicroweber/filament-flatpickr/',
//            'js/coolsam/flatpickr/flatpickr-confirm-date.js' => 'vendor/bobimicroweber/',
//            'js/coolsam/flatpickr/flatpickr-month-select-plugin.js' => 'vendor/bobimicroweber/',

            // Flatpickr assets
            'js/coolsam/flatpickr/flatpickr-range-plugin.js' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/plugins/rangePlugin.js',
            'js/coolsam/flatpickr/flatpickr-confirm-date.js' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/plugins/confirmDate/confirmDate.js',
            'js/coolsam/flatpickr/flatpickr-month-select-plugin.js' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/plugins/monthSelect/index.js',
            'js/coolsam/flatpickr/flatpickr-week-select-plugin.js' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/plugins/weekSelect/weekSelect.js',
            'css/coolsam/flatpickr/flatpickr-css.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/flatpickr.css',
            'css/coolsam/flatpickr/month-select-style.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/plugins/monthSelect/style.css',
            'css/coolsam/flatpickr/flatpickr-confirm-date-style.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/plugins/confirmDate/confirmDate.css',
            'css/coolsam/flatpickr/flatpickr-airbnb-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/airbnb.css',
            'css/coolsam/flatpickr/flatpickr-confetti-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/confetti.css',
            'css/coolsam/flatpickr/flatpickr-dark-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/dark.css',
            'css/coolsam/flatpickr/flatpickr-light-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/light.css',
            'css/coolsam/flatpickr/flatpickr-default-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/light.css',
            'css/coolsam/flatpickr/flatpickr-material_blue-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/material_blue.css',
            'css/coolsam/flatpickr/flatpickr-material_green-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/material_green.css',
            'css/coolsam/flatpickr/flatpickr-material_red-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/material_red.css',
            'css/coolsam/flatpickr/flatpickr-material_orange-theme.css' => 'vendor/bobimicroweber/filament-flatpickr/resources/assets/flatpickr/dist/themes/material_orange.css',
            'js/coolsam/flatpickr/flatpickr-component.js' => 'vendor/bobimicroweber/filament-flatpickr/resources/js/dist/components/flatpickr-component.js',



            'vendor/microweber-packages/microweber-filament-theme/build' => 'vendor/microweber-packages/microweber-filament-theme/resources/dist/build',

        ];

        // Create symlinks for system assets
        foreach ($allSystemLinks as $link => $target) {

            $tagetPath = normalize_path($basePath . DIRECTORY_SEPARATOR . $target,false);
            $linkPath = normalize_path($publicPath . DIRECTORY_SEPARATOR .$link,false);

            $this->createSymlink($linkPath,$tagetPath);
        }

//        // Get all modules and create symlinks for their assets
//        $allModules = app()->modules->all();
//        foreach ($allModules as $module) {
//            $this->symlinkModuleAssets($module);
//        }
//
//        // Get all templates and create symlinks for their assets
//        $allTemplates = app()->templates->all();
//        foreach ($allTemplates as $template) {
//            $this->symlinkTemplateAssets($template);
//        }

        $this->info('Vendor assets symlinks created successfully!');
    }

    protected function _symlink($target, $link) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $target = str_replace('/', '\\', $target);
            $link = str_replace('/', '\\', $link);
            if (is_dir($target)) {
                exec('cmd /c mklink /D "' . $link . '" "' . $target . '"');
            } else {
                exec('cmd /c mklink "' . $link . '" "' . $target . '"');
            }
        } else {
            symlink($target, $link);
        }
    }

    protected function _unlink($link) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $link = str_replace('/', '\\', $link);
            if (is_dir($link)) {
                exec('cmd /c rmdir /S /Q "' . $link . '"');
            } else {
                exec('cmd /c del /F /Q "' . $link . '"');
            }
        } else {
            if (is_dir($link)) {
                $this->recursiveRemoveDirectory($link);
            } else {
                unlink($link);
            }
        }
    }

    protected function recursiveRemoveDirectory($directory) {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->recursiveRemoveDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }

    /**
     * Create a symlink from link to target
     */
    protected function createSymlink($link, $target)
    {
        // Ensure target exists
        if (!file_exists($target)) {

            $this->warn("Target does not exist: {$target}");
            return false;
        }

        if(is_link($link)){
            $this->info("Link already exists: {$link}");
            return true;
        }


        // Create directory for link if it doesn't exist
        $linkDir = dirname($link);
        if (!is_dir($linkDir)) {
            mkdir($linkDir, 0755, true);
        }

        // Remove existing link/file if it exists
        if (file_exists($link) || is_link($link)) {
            $this->_unlink($link);
        }

        // Create the symlink
        $this->_symlink($target, $link);
        $this->line("Created symlink: {$link} -> {$target}");
        return true;
    }

    /**
     * Create symlinks for module assets
     */
    protected function symlinkModuleAssets($module)
    {
        if (!isset($module['path'])) {
            return;
        }

        $modulePath = $module['path'];
        $moduleName = basename($modulePath);

        // Common asset directories in modules
        $assetDirs = ['js', 'css', 'img', 'images', 'fonts', 'assets'];

        foreach ($assetDirs as $assetDir) {
            $sourceDir = $modulePath . DIRECTORY_SEPARATOR . $assetDir;
            $targetDir = public_path('modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $assetDir);

            if (is_dir($sourceDir)) {
                $this->createSymlink($targetDir, $sourceDir);
            }
        }
    }

    /**
     * Create symlinks for template assets
     */
    protected function symlinkTemplateAssets($template)
    {
        if (!isset($template['path'])) {
            return;
        }

        $templatePath = $template['path'];
        $templateName = basename($templatePath);

        // Common asset directories in templates
        $assetDirs = ['js', 'css', 'img', 'images', 'fonts', 'assets'];

        foreach ($assetDirs as $assetDir) {
            $sourceDir = $templatePath . DIRECTORY_SEPARATOR . $assetDir;
            $targetDir = public_path('templates' . DIRECTORY_SEPARATOR . $templateName . DIRECTORY_SEPARATOR . $assetDir);

            if (is_dir($sourceDir)) {
                $this->createSymlink($targetDir, $sourceDir);
            }
        }
    }

}
