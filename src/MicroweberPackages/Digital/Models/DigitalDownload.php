<?php

namespace MicroweberPackages\Digital\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MicroweberPackages\Order\Models\Order;
use MicroweberPackages\Product\Models\Product;
use MicroweberPackages\User\Models\User;

class DigitalDownload extends Model
{
    protected $table = 'digital_downloads';

    protected $fillable = [
        'order_id',
        'product_id',
        'user_id',
        'email',
        'token',
        'file_url',
        'download_count',
        'max_downloads',
        'expires_at',
        'last_downloaded_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return Carbon::now()->greaterThan($this->expires_at);
    }

    public function isMaxedOut(): bool
    {
        if (!$this->max_downloads) {
            return false;
        }

        return $this->download_count >= $this->max_downloads;
    }

    public function isAvailable(): bool
    {
        return !$this->isExpired() && !$this->isMaxedOut();
    }
}
