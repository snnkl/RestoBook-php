<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use App\Services\TurboSmsService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSubscriptionExpiredSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        $phone = $this->user->phone;
        $text = "Ваша VIP-підписка закінчилась! Поновіть її, щоб зберегти знижки.";


        // 1. Спроба відправити в Telegram
        if ($this->user->telegram_chat_id) {
            try {
                $tgService = new TelegramService();
                $tgService->sendMessage($this->user->telegram_chat_id, $text);

                Log::info("✈️ [VIP Alert] Відправлено в Telegram для {$this->user->email}");
                return;
            } catch (Exception $e) {
                Log::error("⚠️ Не вдалося відправити VIP-нагадування в Telegram: " . $e->getMessage());

            }
        }

        Log::info("📱 [VIP Alert] Telegram відсутній, відправляємо SMS для {$this->user->email}");

        $smsService = new TurboSmsService();
        $smsService->send($phone, $text);
    }
}
