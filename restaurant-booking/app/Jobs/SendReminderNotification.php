<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\TelegramService;
use App\Services\TurboSmsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(): void
    {
        $user = $this->booking->user;
        $time = Carbon::parse($this->booking->start_time)->format('H:i');
        $restaurantName = $this->booking->table->restaurant->name;

        $msg = "⏰ Нагадування! Чекаємо вас сьогодні о $time " .
            "у ресторані '$restaurantName'. " .
            "Ваш столик №{$this->booking->table->number}.";

        if ($user->telegram_chat_id) {
            try {
                $tgService = new TelegramService();
                $tgService->sendMessage($user->telegram_chat_id, $msg);

                Log::info("✈️ [Reminder] Відправлено в Telegram для бронювання #{$this->booking->id}");
                return; // Успіх -> виходимо, СМС не шлемо
            } catch (Exception $e) {
                Log::error("⚠️ Не вдалося відправити нагадування в Telegram: " . $e->getMessage());
            }
        }

        Log::info("📱 [Reminder] Відправляємо SMS для бронювання #{$this->booking->id}");

        $smsService = new TurboSmsService();
        $smsService->send($user->phone, $msg);
    }
}
