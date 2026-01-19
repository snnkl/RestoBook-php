<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Веселий Вареник', 'Steak House', 'Pizza Club', 'Sushi Master', 'Burger Bar'];
        foreach ($names as $name) {
            $restaurant = Restaurant::create([
                'name' => $name,
                'address' => 'вул. Головна, ' . rand(1, 50),
            ]);

            for ($i = 1; $i <= 10; $i++) {
                $restaurant->tables()->create([
                    'number' => 'Стіл №' . $i,
                    'capacity' => rand(1, 8),
                    'price_per_seat' => 100,
                    'is_active' => true
                ]);
            }
        }
        echo "✅ Створено 5 ресторанів і 50 столиків\n";

        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $users[] = User::create([
                'name' => 'Клієнт ' . $i,
                'email' => "client$i@example.com",
                'phone' => '+3805000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
            ]);
        }
        echo "✅ Створено 20 тестових клієнтів\n";

        $tables = Table::all();

        for ($j = 1; $j <= 30; $j++) {
            $randomTable = $tables->random();
            $randomUser = $users[array_rand($users)];

            $randomDate = Carbon::today()->addDays(rand(1, 30));
            $randomHour = rand(10, 20);

            $startTime = $randomDate->copy()->setHour($randomHour)->setMinute(0);
            $endTime = $startTime->copy()->addHours(2);

            Booking::create([
                'user_id' => $randomUser->id,
                'table_id' => $randomTable->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'confirmed',
            ]);
        }
        echo "✅ Створено 30 бронювань\n";
    }
}
