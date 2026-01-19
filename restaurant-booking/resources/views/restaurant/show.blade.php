@extends('layouts.app')
@section('title' , $restaurant->name )
@section('content')

<div class="container mt-5">
    <a href="/" class="btn btn-outline-secondary mb-3">← Назад до списку</a>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h1 class="display-5">{{ $restaurant->name }}</h1>
            <p class="text-muted">{{ $restaurant->address }}</p>
        </div>
    </div>

    <h3 class="mb-3">Вільні столики:</h3>

    <div class="row">
        @foreach($restaurant->tables as $table)
            <div class="col-md-3 mb-3">
                <div class="card h-100 {{ $table->is_active ? 'border-success' : 'border-danger' }}">
                    <div class="card-body text-center">
                        <h5 class="card-title">{{ $table->number }}</h5>
                        <p class="card-text">
                            Місць: <strong>{{ $table->capacity }}</strong><br>
                            Ціна: <strong>{{ $table->price_per_seat * $table->capacity }} грн/год</strong>
                        </p>

                        @if($table->is_active)
                            <a href="{{ route('booking.create', $table->id) }}" class="btn btn-success w-100">Забронювати</a>
                        @else
                            <button class="btn btn-secondary w-100" disabled>Недоступний</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
