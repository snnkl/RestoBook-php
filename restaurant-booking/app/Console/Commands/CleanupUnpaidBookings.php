<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\TelegramService;
use Carbon\Carbon;

class CleanupUnpaidBookings extends Command
{
    protected $signature = 'booking:cleanup';
    protected $description = 'Скасовує неоплачені бронювання старші за 15 хвилин';

    public function handle(): void
    {
        $limitTime = Carbon::now()->subMinutes(15);

        $expiredBookings = Booking::where('status', 'pending')
            ->where('created_at', '<', $limitTime)
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'cancelled']);

            if ($booking->user->telegram_chat_id) {
                (new TelegramService())->sendMessage(
                    $booking->user->telegram_chat_id,
                    "⏳ Час на оплату бронювання #$booking->id вичерпано. Бронь скасовано."
                );
            }

            $this->info("Бронювання #$booking->id скасовано.");
        }

        $this->info("Перевірку завершено. Скасовано: " . $expiredBookings->count());
    }
}
