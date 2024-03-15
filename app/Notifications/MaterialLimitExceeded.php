<?php

namespace App\Notifications;

use App\Models\MaterialLimitStatistic;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaterialLimitExceeded extends Notification
{
    use Queueable;

    private array $statisticData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($statisticData)
    {
        $this->statisticData = $statisticData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject('Статистика лимита списания по ТМЦ на ' . date('Y-m-d'))
            ->from(env('MAIL_USERNAME'), 'Alma Telecommunications Kazakhstan')
            ->line('Отчет по статистике')
            ->view('emails.statistics', ['statisticData' => $this->statisticData]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
