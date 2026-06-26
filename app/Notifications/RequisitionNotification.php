<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function via($notifiable): array
    {
        // নিশ্চিত করুন যে ইমেইল ফিল্ডটি নাল নয় এবং অন্তত ৫ অক্ষরের (মেইল ফরম্যাট চেক)
        return (!empty($notifiable->email) && filter_var($notifiable->email, FILTER_VALIDATE_EMAIL))
            ? ['database', 'mail']
            : ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        // ডিফল্ট ফ্রম অ্যাড্রেস কনফিগ থেকে নিন, নাহলে একটি ফলব্যাক দিন
        $fromAddress = config('mail.from.address') ?: 'noreply@yourdomain.com';
        $fromName = config('mail.from.name') ?: 'System Notification';

        return (new MailMessage)
            ->from($fromAddress, $fromName)
            ->subject(__('New Requisition Alert'))
            ->line($this->message)
            ->action(__('View Approval Queue'), $this->url)
            ->line(__('Thank you for using our system.'));
    }

    public function toArray($notifiable): array
    {
        return [
            'requisition_id' => $this->requisition->id,
            'requisition_no' => $this->requisition->requisition_no,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}
