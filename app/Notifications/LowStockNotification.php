<?php

namespace App\Notifications;

use App\Models\Ingredient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Ingredient $ingredient)
    {
    }

    public function via($notifiable)
    {
        return [
            // 'mail',
            'database',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Refill the ingredient stock for '.$this->ingredient->name.'.')
                    ->action('Refill', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toArray($notifiable)
    {
        return [
            'ingredient_id' => $this->ingredient->id,
            'stock_capacity' => $this->ingredient->stock_capacity,
            'current_stock_amount' => $this->ingredient->current_stock_amount,
            'message' => 'Refill the ingredient stock for '.$this->ingredient->name.'.',
        ];
    }
}
