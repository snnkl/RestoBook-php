@extends('layouts.app')

@section('content')
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Знайди свій ідеальний столик</h1>
        <p class="lead text-muted">Обирай ресторан, час та насолоджуйся вечором</p>
    </div>

    <div class="row">
        @foreach($restaurants as $restaurant)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <h3 class="h5">{{ $restaurant->name }}</h3>
                        <p class="text-muted small">📍 {{ $restaurant->address }}</p>
                        <a href="{{ route('restaurant.show', $restaurant->id) }}" class="btn btn-primary w-100">
                            Переглянути столики
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
