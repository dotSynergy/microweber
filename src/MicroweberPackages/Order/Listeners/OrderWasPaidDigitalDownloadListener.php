<?php

namespace MicroweberPackages\Order\Listeners;

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use MicroweberPackages\Digital\Models\DigitalDownload;
use MicroweberPackages\Order\Models\OrderAnonymousClient;
use MicroweberPackages\Order\Notifications\DigitalDownloadNotification;
use MicroweberPackages\Product\Models\Product;
use MicroweberPackages\User\Models\User;

class OrderWasPaidDigitalDownloadListener
{
    public function handle($event)
    {
        Log::info('OrderWasPaidDigitalDownloadListener triggered', [
            'event_class' => is_object($event) ? get_class($event) : gettype($event),
        ]);
        $order = $event->order;
        if (!$order) {
            Log::warning('OrderWasPaidDigitalDownloadListener missing order');
            return;
        }

        Log::info('OrderWasPaidDigitalDownloadListener handling order', [
            'order_id' => $order->id ?? null,
            'order_email' => $order->email ?? null,
        ]);

        $orderItems = app()->shop_manager->order_items($order->id);
        if (empty($orderItems)) {
            Log::info('OrderWasPaidDigitalDownloadListener no order items', [
                'order_id' => $order->id ?? null,
            ]);
            return;
        }

        $downloads = [];
        foreach ($orderItems as $item) {
            if (!isset($item['rel_id'])) {
                Log::warning('OrderWasPaidDigitalDownloadListener order item missing rel_id', [
                    'order_id' => $order->id ?? null,
                    'item' => $item,
                ]);
                continue;
            }

            $product = Product::find((int) $item['rel_id']);
            if (!$product) {
                Log::warning('OrderWasPaidDigitalDownloadListener product not found', [
                    'order_id' => $order->id ?? null,
                    'rel_id' => $item['rel_id'],
                ]);
                continue;
            }

            $contentData = $product->getContentData();
            if (isset($contentData['physical_product']) && (int) $contentData['physical_product'] === 1) {
                Log::info('OrderWasPaidDigitalDownloadListener skipping physical product', [
                    'order_id' => $order->id ?? null,
                    'product_id' => $product->id,
                ]);
                continue;
            }

            $digitalFile = $contentData['digital_file'] ?? '';
            if (!$digitalFile) {
                Log::info('OrderWasPaidDigitalDownloadListener missing digital_file', [
                    'order_id' => $order->id ?? null,
                    'product_id' => $product->id,
                ]);
                continue;
            }

            $existing = DigitalDownload::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->first();
            if ($existing) {
                Log::info('OrderWasPaidDigitalDownloadListener using existing download', [
                    'order_id' => $order->id ?? null,
                    'product_id' => $product->id,
                    'download_id' => $existing->id ?? null,
                ]);
                $downloads[] = $existing;
                continue;
            }

            $maxDownloads = isset($contentData['digital_max_downloads']) ? (int) $contentData['digital_max_downloads'] : 0;
            if ($maxDownloads <= 0) {
                $maxDownloads = null;
            }

            $expiresDays = isset($contentData['digital_expires_days']) ? (int) $contentData['digital_expires_days'] : 0;
            $expiresAt = $expiresDays > 0 ? now()->addDays($expiresDays) : null;

            $token = bin2hex(random_bytes(32));
            $blockedFileUrl = $this->storeBlockedCopy($digitalFile, $order->id, $product->id, $token);
            if (!$blockedFileUrl) {
                Log::warning('Digital download file could not be copied to blocked folder', [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'file_url' => $digitalFile,
                ]);
            }

            $download = DigitalDownload::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'user_id' => $order->created_by ?: null,
                'email' => $order->email ?: null,
                'token' => $token,
                'file_url' => $blockedFileUrl ?: $digitalFile,
                'download_count' => 0,
                'max_downloads' => $maxDownloads,
                'expires_at' => $expiresAt,
            ]);

            Log::info('OrderWasPaidDigitalDownloadListener created download', [
                'order_id' => $order->id ?? null,
                'product_id' => $product->id,
                'download_id' => $download->id ?? null,
            ]);

            $downloads[] = $download;
        }

        if (empty($downloads)) {
            Log::info('OrderWasPaidDigitalDownloadListener no downloads to notify', [
                'order_id' => $order->id ?? null,
            ]);
            return;
        }

        $notification = new DigitalDownloadNotification($order, $downloads);
        $notifiable = false;

        if (isset($order->created_by) && $order->created_by > 0) {
            $customer = User::where('id', $order->created_by)->first();
            if ($customer) {
                if (empty($order->email)) {
                    $notifiable = $customer;
                }
            }
        }

        if (!$notifiable) {
            $notifiable = OrderAnonymousClient::find($order->id);
        }

        if ($notifiable) {
            $notifiable->notifyNow($notification);
        } else {
            Log::warning('OrderWasPaidDigitalDownloadListener no notifiable found', [
                'order_id' => $order->id ?? null,
                'order_email' => $order->email ?? null,
            ]);
        }
    }

    private function storeBlockedCopy(string $fileUrl, int $orderId, int $productId, string $token): ?string
    {
        $sourcePath = app()->url_manager->to_path($fileUrl);
        $sourcePath = $this->normalizeFilePath($fileUrl, $sourcePath);
        if (!$sourcePath || !is_file($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = $token . ($extension ? '.' . $extension : '');
        $targetDir = userfiles_path() . 'media' . DS . 'digital-downloads' . DS . $orderId . DS . $productId;

        if (!is_dir($targetDir)) {
            mkdir_recursive($targetDir);
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (!@copy($sourcePath, $targetPath)) {
            return null;
        }

        return userfiles_url() . 'media/digital-downloads/' . $orderId . '/' . $productId . '/' . $filename;
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
        if ($publicRoot && strpos($real, $publicRoot) === 0) {
            return $real;
        }

        return null;
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
