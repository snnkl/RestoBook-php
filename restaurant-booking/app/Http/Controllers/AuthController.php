<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Форма реєстрації
    public function showRegister()
    {
        return view('auth.register');
    }

    // Обробка реєстрації
    public function register(Request $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        Auth::login($user);

        return redirect()->intended(route('home'));
    }

    // Форма входу
    public function showLogin()
    {
        return view('auth.login');
    }

    // Обробка входу
    public function login(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->intended(route('home'));

        }

        return back()->withErrors(['email' => 'Невірні дані']);
    }

    // Вихід
    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }
}
