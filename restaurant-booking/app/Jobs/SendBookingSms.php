<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\TurboSmsService;
use App\Services\TelegramService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBookingSms implements ShouldQueue
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

        $text = "Вітаємо, $user->name! " .
            "Бронь #{$this->booking->id} підтверджено. " .
            "Чекаємо вас о {$this->booking->start_time}!";


        if ($user->telegram_chat_id) {
            try {
                $tgService = new TelegramService();
                $tgService->sendMessage($user->telegram_chat_id, $text);

                Log::info("✈️ [Notification] Відправлено в Telegram для $user->email");


                return;
            } catch (Exception $e) {
                Log::error("⚠️ Не вдалося відправити в Telegram: " . $e->getMessage());
            }
        }

        Log::info("📱 [Notification] Telegram відсутній, відправляємо SMS для $user->email");

        $smsService = new TurboSmsService();
        $smsService->send($user->phone, $text);
    }
}
