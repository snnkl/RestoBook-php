<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Jobs\SendReminderNotification;
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    protected $signature = 'booking:remind';
    protected $description = 'Надсилає нагадування за 2 години до візиту';

    public function handle(): void
    {
        $startTime = now()->addHours(2)->subMinutes(5);
        $endTime   = now()->addHours(2)->addMinutes(5);

        $bookings = Booking::where('status', 'confirmed')
            ->where('reminder_sent', false)
            ->whereBetween('start_time', [$startTime, $endTime])
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            SendReminderNotification::dispatch($booking);

            $booking->update(['reminder_sent' => true]);

            $count++;
        }

        $this->info("✅ Перевірку завершено. Відправлено завдань у чергу: $count");
    }
}
