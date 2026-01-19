@extends('layouts.app')
@section('title' , 'VIP' )
@section('content')
    <div class="container py-5 text-center">

        @if(Auth::check() && Auth::user()->is_vip)
            <div class="alert alert-success d-inline-block px-5 py-3 shadow-sm">
                <h2 class="mb-0">👑 Ви — VIP клієнт!</h2>
                <p class="mb-0 mt-2">Ваша підписка активна до: <strong>{{ \Carbon\Carbon::parse(Auth::user()->subscription_ends_at)->format('d.m.Y H:i') }}</strong></p>
            </div>
            <div class="mt-4">
                <a href="/" class="btn btn-outline-success">Перейти до бронювання зі знижкою</a>
            </div>
        @else
            <h1 class="display-4 fw-bold text-warning mb-4" style="text-shadow: 1px 1px 2px #000;">👑 Стань VIP-клієнтом</h1>
            <p class="lead text-muted mb-5">Отримуйте ексклюзивні знижки на кожне бронювання в мережі наших ресторанів.</p>

            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="card shadow border-warning mb-4">
                        <div class="card-header bg-warning text-dark fw-bold">
                            Переваги підписки
                        </div>
                        <ul class="list-group list-group-flush text-start">
                            <li class="list-group-item">✅ <strong>-5%</strong> на будь-які бронювання</li>
                            <li class="list-group-item">✅ <strong>-7%</strong> для компаній (від 2 місць)</li>
                            <li class="list-group-item">✅ <strong>-10%</strong> для вечірок (від 4 місць)</li>
                            <li class="list-group-item">🚀 Пріоритетна підтримка</li>
                        </ul>
                        <div class="card-body bg-light">
                            @if(Auth::user()->is_vip && \Carbon\Carbon::parse(Auth::user()->subscription_ends_at)->isFuture())

                                <div class="alert alert-success">
                                    <h4>👑 Ви вже VIP-клієнт!</h4>
                                    <p>Ваша підписка активна до: <strong>{{ \Carbon\Carbon::parse(Auth::user()->subscription_ends_at)->format('d.m.Y H:i') }}</strong></p>
                                </div>

                            @else
                            <h3 class="card-title">200 ₴ <small class="text-muted">/ міс</small></h3>
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{ route('subscription.pay') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-lg">
                                    Оформити VIP
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
