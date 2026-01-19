<?php

namespace App\Http\Controllers;

use App\Jobs\SendBookingSms;
use App\Models\Booking;
use App\Models\Table;
use App\Services\TelegramService;
use App\Services\CurrencyService;
use App\Services\MonobankService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    // Відображення сторінки створення бронювання з курсами валют
    public function create($tableId)
    {
        $table = Table::findOrFail($tableId);

        $currencyService = new CurrencyService();
        $rates = $currencyService->getRates();

        $usdRate = $currencyService->getCourse('USD', $rates);
        $eurRate = $currencyService->getCourse('EUR', $rates);

        return view('booking.create', compact('table', 'usdRate', 'eurRate'));
    }

    // Збереження нового бронювання
    public function store(Request $request)
    {
        // Валідація: існування столика, дати, часу роботи та тривалості
        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'date' => 'required|date|after_or_equal:today|before_or_equal:+14 days',
            'time' => ['required', function ($attribute, $value, $fail) {
                if ($value < '07:00' || $value > '22:00') {
                    $fail('Ресторан працює з 07:00 до 22:00. Будь ласка, оберіть інший час.');
                }
            }],
            'duration' => 'required|integer|min:1|max:4',
        ]);

        $start = Carbon::parse($request->date . ' ' . $request->time);
        $end = $start->copy()->addHours((int)$request->duration);

        // Перевірка, чи не обрано час у минулому
        if ($start->isPast()) {
            return back()->withErrors(['time' => 'Ви не можете забронювати час, який вже минув!'])
                ->withInput();
        }

        // Перевірка на перетин часу з іншими бронюваннями (овербукінг)
        $exists = Booking::where('table_id', $request->table_id)
            ->where('status', '!=', 'cancelled') // Скасовані не рахуємо
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->exists();

        if ($exists) {
            return back()->withErrors(['time' => 'Цей столик вже зайнято на обраний час. Спробуйте інший час або столик.'])->withInput();
        }

        $table = Table::find($request->table_id);
        $duration = (int)$request->duration;

        // Розрахунок ціни
        $finalPrice = $this->calculateFinalPrice($table, $duration);

        // Створення запису зі статусом "очікування оплати"
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'table_id' => $request->table_id,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'pending',
        ]);

        // Створення інвойсу в Monobank
        $monobank = new MonobankService();
        $response = $monobank->createInvoice($booking, $finalPrice);

        // Редірект на сторінку оплати банку
        if (is_array($response) && isset($response['pageUrl'])) {
            $booking->update(['invoice_id' => $response['invoiceId']]);

            return redirect($response['pageUrl']);
        }

        $booking->delete();
        return redirect('/')->with('error', 'Помилка створення оплати. Спробуйте пізніше.');
    }

    // Список активних бронювань користувача
    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->where('end_time', '>=', Carbon::now())
            ->with('table.restaurant')
            ->orderBy('start_time')
            ->get();

        return view('booking.index', compact('bookings'));
    }

    // Скасування бронювання
    public function destroy($id)
    {
        $booking = Booking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($booking->user->telegram_chat_id) {
            $msg = "❌ <strong>Бронювання #$booking->id скасовано.</strong>";
            (new TelegramService())->sendMessage($booking->user->telegram_chat_id, $msg);
        }

        $booking->delete();

        return $this->backSuccess('Бронювання успішно скасовано!');
    }

    // Повторна спроба оплати вже створеного бронювання
    public function payExisting($id)
    {
        $booking = Booking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $duration = $booking->start_time->diffInHours($booking->end_time);

        $finalPrice = $this->calculateFinalPrice($booking->table, $duration);

        $monobank = new MonobankService();


        $responseData = $monobank->createInvoice($booking, $finalPrice);


        if ($responseData && isset($responseData['pageUrl'])) {

            $booking->update([
                'invoice_id' => $responseData['invoiceId']
            ]);

            return redirect($responseData['pageUrl']);
        }

        return $this->backError('Помилка банку. Спробуйте пізніше.');
    }

    // Обробка Webhook від Monobank (підтвердження оплати)
    public function handleWebhook(Request $request)
    {
        Log::info('📥 Webhook отримано:', $request->all());

        $invoiceId = $request->input('invoiceId');
        $status = $request->input('status');

        $booking = Booking::where('invoice_id', $invoiceId)->first();

        if (!$booking) {
            return $this->jsonError();
        }


        // Ідемпотентність: ігноруємо вже оплачені замовлення
        if ($booking->status === 'confirmed') {
            Log::info("🔄 Повторний webhook для #$booking->id. Ігноруємо.");
            return $this->jsonOk();
        }

        // Якщо оплата успішна — оновлюємо статус і ставимо задачу в чергу
        if ($status === 'success') {
            $booking->update(['status' => 'confirmed']);

            // Відправляємо СМС/Телеграм
            SendBookingSms::dispatch($booking);

            Log::info("✅ Бронь #$booking->id успішно оплачена!");
        }

        return $this->jsonOk();
    }

    // Сторінка підключення Telegram бота
    public function telegramPage()
    {
        $connectCode = 'connect-' . Auth::id();

        return view('booking.telegram', compact('connectCode'));
    }

    // Перевірка підключення Telegram
    public function checkTelegramConnection()
    {
        $telegram = new TelegramService();
        $updates = $telegram->getUpdates();

        $connectCode = 'connect-' . Auth::id();

        // Шукаємо повідомлення з кодом підключення
        foreach ($updates as $update) {
            if (isset($update['message']['text']) && trim($update['message']['text']) === $connectCode) {

                $chatId = $update['message']['chat']['id'];

                $user = Auth::user();
                $user->telegram_chat_id = $chatId;
                $user->save();

                $telegram->sendMessage($chatId, "✅ Ви успішно підключили сповіщення від RestoBook!");

                return back();
            }
        }

        return $this->backError('Ми ще не бачимо вашого повідомлення. Спробуйте написати код боту ще раз.');
    }


}
