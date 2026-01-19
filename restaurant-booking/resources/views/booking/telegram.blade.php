@extends('layouts.app')
@section('title', 'Телеграм')
@section('content')
    <div class="container text-center mt-5">
        <h2>🤖 Підключення Telegram-бота</h2>
        <p class="lead">Отримуйте сповіщення про ваші бронювання миттєво.</p>

        @if(Auth::user()->telegram_chat_id)
            <div class="alert alert-success mt-4">
                ✅ Ваш Telegram вже підключено!
            </div>
        @else
            <div class="card mx-auto mt-4 shadow-sm" style="max-width: 500px;">
                <div class="card-body">
                    <p>1. Знайдіть нашого бота в Telegram або натисніть кнопку:</p>
                    <a href="https://t.me/RestoBook_Ivan_Bot" target="_blank" class="btn btn-primary mb-3">
                        Відкрити бота
                    </a>

                    <p>2. Натисніть <strong>Start</strong> і надішліть йому цей код:</p>
                    <h3 class="bg-light p-2 rounded border border-primary d-inline-block user-select-all">
                        {{ $connectCode }}
                    </h3>

                    <p class="mt-3">3. Після відправки коду натисніть кнопку нижче:</p>

                    <form action="{{ route('telegram.check') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            Я відправив код, перевірити
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
