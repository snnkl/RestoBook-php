<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AuthController;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    $restaurants = Restaurant::all();
    return view('welcome', ['restaurants' => $restaurants]);
})->name('home');

Route::get('/restaurant/{id}', [RestaurantController::class, 'show'])->name('restaurant.show');


// Авторизація
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::post('/monobank/webhook', [BookingController::class, 'handleWebhook'])->name('monobank.webhook');
Route::post('/monobank/subscription/webhook', [SubscriptionController::class, 'handleWebhook'])->name('monobank.subscription.webhook');


Route::middleware(['auth'])->group(function () {

    // Бронювання
    Route::get('/booking/{table_id}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('booking.index');
    Route::delete('/booking/{id}', [BookingController::class, 'destroy'])->name('booking.destroy');
    Route::get('/booking/{id}/pay', [BookingController::class, 'payExisting'])->name('booking.pay');

    // Підписка
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/pay', [SubscriptionController::class, 'pay'])->name('subscription.pay');

    // Telegram
    Route::get('/telegram', [BookingController::class, 'telegramPage'])->name('telegram.page');
    Route::post('/telegram/check', [BookingController::class, 'checkTelegramConnection'])->name('telegram.check');

});
