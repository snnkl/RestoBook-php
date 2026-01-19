<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Jobs\SendSubscriptionExpiredSms;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscription:check';
    protected $description = 'Перевіряє, у кого закінчилась підписка';

    public function handle(): void
    {
        $expiredUsers = User::where('subscription_ends_at', '<', now())
            ->whereNotNull('subscription_ends_at')
            ->where('vip_notification_sent', false)
            ->get();

        foreach ($expiredUsers as $user) {

            SendSubscriptionExpiredSms::dispatch($user);

            $this->info("Queued notification for: $user->email");

            $user->update(['vip_notification_sent' => true]);
        }

        $this->info("Перевірку завершено.");
    }
}
