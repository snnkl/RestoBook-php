<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{

    protected function calculateFinalPrice($table, $duration)
    {
        $basePrice = $table->price_per_seat * $table->capacity * $duration;

        if (!Auth::check() ||
            !Auth::user()->subscription_ends_at) {
            return $basePrice;
        }

        if ($table->capacity >= 4) {
            $discountPercent = 10;
        } elseif ($table->capacity >= 2) {
            $discountPercent = 7;
        } else {
            $discountPercent = 5;
        }

        $discountAmount = ($basePrice * $discountPercent) / 100;

        return $basePrice - $discountAmount;
    }


    protected function backSuccess($message)
    {
        return back()->with('success', $message);
    }

    protected function backError($message)
    {
        return back()->with('error', $message);
    }


    protected function jsonOk()
    {
        return response()->json(['status' => 'ok']);
    }

    protected function jsonError()
    {
        return response()->json(['status' => 'error'], 404);
    }
}
