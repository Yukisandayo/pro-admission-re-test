<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Transaction;

class TransactionCompletedNotification extends Notification
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $itemName = $this->transaction->item ? $this->transaction->item->name : '特定の商品';

        return (new MailMessage)
                    ->subject("【フリマアプリ】商品『{$itemName}』の取引が完了しました")
                    ->greeting("{$notifiable->name}様") // 出品者の名前（$notifiableはUserモデル）
                    ->line("あなたが販売された商品『{$itemName}』について、購入者による取引完了報告がされました。")
                    ->line('この取引を最終的に完了するには、**あなた（出品者）による購入者への評価**が必要です。')
                    ->action('取引チャットを確認', route('transactions.chat', $this->transaction->id))
                    ->line('引き続き、よろしくお願いいたします。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
