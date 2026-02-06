<?php

namespace MicroweberPackages\Digital\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MicroweberPackages\Digital\Models\DigitalDownload;

class DigitalDownloadController extends Controller
{
    public function show(Request $request, string $token)
    {

        $download = DigitalDownload::where('token', $token)->firstOrFail();

        if ($download->order && intval($download->order->is_paid) !== 1) {
            abort(403);
        }

        if (!$download->isAvailable()) {
            abort(410);
        }

        $fileUrl = $download->file_url;
        $filePath = app()->url_manager->to_path($fileUrl);
        $filePath = $this->normalizeFilePath($fileUrl, $filePath);

        if (!$filePath || !is_file($filePath)) {
            abort(404);
        }

        if (isset($download->max_downloads) && $download->max_downloads > 0 &&
            $download->download_count >= $download->max_downloads) {
            abort(403);
        }

        $download->download_count = $download->download_count + 1;
        $download->last_downloaded_at = now();
        $download->save();

        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
        return response()->download($filePath, null, $headers);
    }

    private function normalizeFilePath(string $fileUrl, $filePath): ?string
    {
        if (is_string($filePath) && $filePath !== '' && filter_var($filePath, FILTER_VALIDATE_URL)) {
            $filePath = '';
        }

        if (!$filePath || !is_string($filePath)) {
            $pathFromUrl = $this->extractPathFromUrl($fileUrl);
            if ($pathFromUrl) {
                $filePath = public_path(ltrim($pathFromUrl, '/'));
            }
        }

        if (!$filePath || !is_string($filePath)) {
            return null;
        }

        $real = realpath($filePath);
        if (!$real) {
            return null;
        }

        $publicRoot = realpath(public_path());
        if ($publicRoot && strpos($real, $publicRoot) !== 0) {
            return null;
        }

        return $real;
    }

    private function extractPathFromUrl(string $fileUrl): ?string
    {
        if ($fileUrl === '') {
            return null;
        }

        $parts = @parse_url($fileUrl);
        if (is_array($parts) && isset($parts['path'])) {
            return $parts['path'];
        }

        if (preg_match('#^https?:(/.*)$#i', $fileUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
