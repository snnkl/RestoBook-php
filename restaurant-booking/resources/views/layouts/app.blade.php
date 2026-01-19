<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Головна | RestoBook')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; }
        .navbar-brand { font-weight: bold; }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-white bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand text-primary" href="/">🍽️ RestoBook</a>

        <div class="d-flex align-items-center">
            @auth
                <a href="{{ route('booking.index') }}" class="btn btn-link text-decoration-none me-3">
                    📅 Мої бронювання
                </a>

                <a href="{{ route('telegram.page') }}" class="btn btn-link text-decoration-none me-2" title="Налаштування сповіщень">
                    @if(Auth::user()->telegram_chat_id)
                        <span style="color: #2AABEE;">✈️</span> @else
                        <span class="badge bg-info text-dark">🤖 Підключити бота</span> @endif
                </a>

                <a href="{{ route('subscription.index') }}" class="btn btn-warning btn-sm fw-bold me-3 text-dark">
                    👑 VIP Club
                </a>

                <span class="me-3 text-muted">| {{ Auth::user()->name }}</span>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">Вийти</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm me-2">Вхід</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Реєстрація</a>
            @endauth
        </div>
    </div>
</nav>

<main class="container flex-grow-1">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @yield('content')
</main>

<footer class="bg-white text-center py-3 mt-auto border-top">
    <small class="text-muted">&copy; 2026 RestoBook.</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
