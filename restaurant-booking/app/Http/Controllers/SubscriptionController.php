<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\MonobankService;
use App\Models\User;

class SubscriptionController extends Controller
{
    // Сторінка з описом переваг VIP-підписки
    public function index()
    {
        return view('subscription.index');
    }

    // Ініціалізація оплати: створення інвойсу та редірект на сторінку банку
    public function pay()
    {
        $user = Auth::user();
        $price = 200; // Ціна в гривнях

        $monoService = new MonobankService();

        // Генеруємо посилання на оплату через сервіс
        $paymentUrl = $monoService->createSubscriptionInvoice($user, $price);

        if ($paymentUrl) {
            return redirect($paymentUrl);
        }

        return back()->with('error', 'Помилка створення оплати. Спробуйте пізніше.');
    }

    // Обробка вебхуку від Monobank (автоматичне нарахування VIP)
    public function handleWebhook(Request $request)
    {
        Log::info('👑 VIP Webhook Raw:', $request->all());

        $status = $request->input('status');

        // Отримуємо ідентифікатор замовлення (reference), де зашитий ID користувача
        $reference = $request->input('reference') ?? $request->input('merchantPaymInfo.reference');

        if (!$reference) {
            return response()->json(['status' => 'ok']);
        }

        // Витягуємо чистий ID користувача з рядка типу "vip_user_5"
        $userId = str_replace('vip_user_', '', $reference);

        if ($status === 'success') {
            $user = User::find($userId);

            if ($user) {

                if ($user->subscription_ends_at && $user->subscription_ends_at->isFuture()) {
                    $newDate = $user->subscription_ends_at->copy()->addMonth();
                } else {
                    $newDate = Carbon::now()->addMonth();
                }

                $user->update([
                    'subscription_ends_at' => $newDate,
                    'vip_notification_sent' => false,
                ]);

                Log::info("✅ VIP активовано для User #$user->id до $newDate");
            } else {
                Log::error("❌ Користувача з ID $userId не знайдено в базі.");
            }
        }

        return $this->jsonOk();
    }
}
