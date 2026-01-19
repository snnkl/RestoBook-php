@extends('layouts.app')
@section('title', 'Реєстрація')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">Реєстрація клієнта</div>
                <div class="card-body">

                    <form action="{{ route('register') }}" method="POST">
                        @csrf <div class="mb-3">
                            <label>Ім'я:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Email:</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Телефон:</label>
                            <input type="text" name="phone" class="form-control" placeholder="+380..." required>
                        </div>

                        <div class="mb-3">
                            <label>Пароль:</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Зареєструватися</button>
                    </form>

                    <p class="mt-3 text-center">
                        Вже є акаунт? <a href="{{ route('login') }}">Увійти</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

