<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RequisitionNotification extends Notification
{
    use Queueable;

    public $requisition;
    public $message;
    public $url;

    public function __construct($requisition, $message, $url)
    {
        $this->requisition = $requisition;
        $this->message = $message;
        $this->url = $url;
    }

    // আমরা আপাতত সিস্টেমে (Database) নোটিফিকেশন পাঠাবো।
    // চাইলে পরে এখানে 'mail' যুক্ত করে ইমেইলও পাঠানো যাবে।
    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'requisition_id' => $this->requisition->id,
            'requisition_no' => $this->requisition->requisition_no,
            'message' => $this->message,
            'url' => $this->url
        ];
    }
}
