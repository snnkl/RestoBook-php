<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Table;
use App\Models\Restaurant;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class BookingSystemTest extends TestCase
{
    // Цей трейт очищає базу після кожного тесту, щоб не смітити
    use RefreshDatabase;

    protected $user;
    protected $table;

    // Підготовка перед кожним тестом (створюємо юзера і столик)
    protected function setUp(): void
    {
        parent::setUp();

        // Створюємо тестового юзера
        $this->user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        // Створюємо ресторан і столик
        $restaurant = Restaurant::create(['name' => 'Test Resto', 'address' => 'Test St', 'description' => 'Desc']);
        $this->table = Table::create([
            'restaurant_id' => $restaurant->id,
            'number' => 1,
            'capacity' => 4,
            'price_per_seat' => 100
        ]);
    }

    /** @test 1. Реєстрація користувача */
    public function user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'phone' => '0991234567',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Перевіряємо, що нас перекинуло на головну (успіх)
        $response->assertRedirect('/');

        // Перевіряємо, що юзер з'явився в базі
        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    /** @test 2. Бронювання без конфліктів (Валідація) */
    public function user_cannot_book_occupied_table()
    {
        // 1. Створюємо перше бронювання на завтра 18:00 - 20:00
        Booking::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'start_time' => Carbon::tomorrow()->setHour(18),
            'end_time' => Carbon::tomorrow()->setHour(20),
            'status' => 'confirmed'
        ]);

        // Авторизуємось
        $this->actingAs($this->user);

        // 2. Пробуємо забронювати ТОЙ САМИЙ час (18:30, перетин)
        $response = $this->post(route('booking.store'), [
            'table_id' => $this->table->id,
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'time' => '18:30',
            'duration' => 1
        ]);

        // Очікуємо помилку в сесії (система не мала пропустити)
        $response->assertSessionHasErrors('time');
    }

    /** @test 3. Оплата підтверджує бронювання через Webhook */
    public function payment_confirms_booking_via_webhook()
    {
        // Створюємо неоплачене бронювання з інвойсом
        $booking = Booking::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'start_time' => Carbon::tomorrow(),
            'end_time' => Carbon::tomorrow()->addHour(),
            'status' => 'pending',
            'invoice_id' => 'test_invoice_123'
        ]);

        // Симулюємо POST запит від Monobank на твій Webhook
        $response = $this->postJson(route('monobank.webhook'), [
            'invoiceId' => 'test_invoice_123',
            'status' => 'success'
        ]);

        $response->assertStatus(200); // Перевіряємо, що сервер відповів OK

        // Перевіряємо, що статус у базі змінився на confirmed
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed'
        ]);
    }

    /** @test 4. Автоскасування (Command) */
    public function command_cancels_unpaid_bookings()
    {
        // Створюємо бронювання (Laravel автоматично поставить час "зараз")
        $oldBooking = Booking::create([
            'user_id' => $this->user->id,
            'table_id' => $this->table->id,
            'start_time' => Carbon::tomorrow(),
            'end_time' => Carbon::tomorrow()->addHour(),
            'status' => 'pending',
        ]);

        // "Старимо" запис вручну
        // Ми перезаписуємо поле і зберігаємо модель. Це обходить захист масового заповнення.
        $oldBooking->created_at = Carbon::now()->subMinutes(25); // Робимо його старшим за 15 хв
        $oldBooking->save();

        // Запускаємо команду
        $this->artisan('booking:cleanup')
            ->assertExitCode(0);

        //Перевіряємо, що статус змінився на cancelled
        $this->assertDatabaseHas('bookings', [
            'id' => $oldBooking->id,
            'status' => 'cancelled'
        ]);
    }
}
