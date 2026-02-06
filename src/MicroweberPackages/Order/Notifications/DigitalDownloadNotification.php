<?php

namespace MicroweberPackages\Order\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use MicroweberPackages\Notification\Channels\AppMailChannel;

class DigitalDownloadNotification extends Notification
{
    use Queueable;

    public $order;
    public $downloads;

    public function __construct($order, $downloads)
    {
        $this->order = $order;
        $this->downloads = $downloads;
    }

    public function via($notifiable)
    {
        return [AppMailChannel::class];
    }

    public function toMail($notifiable)
    {
        $mail = new MailMessage();
        $mail->subject('Your digital download is ready');

        $content = view('order::email.digital_downloads', [
            'order' => $this->order,
            'downloads' => $this->downloads,
        ])->render();

        Log::info('Digital download email rendered', [
            'order_id' => $this->order ? $this->order->id : null,
            'rendered_template' => $content,
        ]);

        $mail->view('app::email.simple', ['content' => $content]);

        return $mail;
    }
}
