@extends('layouts.app')
@section('title', 'Бронювання')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-success text-white">
                    Бронювання: {{ $table->number }}
                </div>
                <div class="card-body">
                    <p>Ресторан: <strong>{{ $table->restaurant->name }}</strong></p>

                    @php
                        // Базова ціна за годину
                        $pricePerHour = $table->price_per_seat * $table->capacity;

                        if ($table->capacity >= 4) {
                            $discountPercent = 10;
                        } elseif ($table->capacity >= 2) {
                            $discountPercent = 7;
                        } else {
                            $discountPercent = 5;
                        }

                        $isVip = Auth::check() && Auth::user()->is_vip;
                    @endphp

                    <div class="alert alert-light border">
                        Вартість столика: <strong>{{ $pricePerHour }} грн / год</strong>
                        <br>
                        <small class="text-muted">({{ $table->capacity }} місць × {{ $table->price_per_seat }} грн)</small>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 list-unstyled">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('booking.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="table_id" value="{{ $table->id }}">

                        <div class="mb-3">
                            <label>Дата:</label>
                            <input type="date" name="date" class="form-control" required
                                   min="{{ date('Y-m-d') }}"
                                   max="{{ date('Y-m-d', strtotime('+14 days')) }}"
                                   value="{{ old('date') }}">
                        </div>

                        <div class="mb-3">
                            <label>Час початку:</label>
                            <input type="time" name="time" class="form-control"
                                   required
                                   min="07:00" max="22:00"
                                   value="{{ old('time') }}"
                                   oninvalid="this.setCustomValidity('Будь ласка, оберіть час між 07:00 та 22:00')"
                                   oninput="this.setCustomValidity('')">

                        </div>

                        <div class="mb-3">
                            <label>Тривалість:</label>
                            <select name="duration" id="durationSelect" class="form-select">
                                <option value="1" {{ old('duration') == '1' ? 'selected' : '' }}>1 година</option>
                                <option value="2" {{ old('duration') == '2' ? 'selected' : '' }}>2 години</option>
                                <option value="3" {{ old('duration') == '3' ? 'selected' : '' }}>3 години</option>
                                <option value="4" {{ old('duration') == '4' ? 'selected' : '' }}>4 години</option>
                            </select>
                        </div>

                        <div class="price-calculation mb-4 p-3 border rounded bg-light">
                            <h5>Вартість бронювання (Стіл на {{ $table->capacity }} місць):</h5>

                            <div id="vipPriceBlock" style="display: none;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted text-decoration-line-through fs-5" id="oldPriceDisplay">
                                        0 грн
                                    </span>
                                    <span class="fs-2 fw-bold text-success" id="vipFinalPriceDisplay">
                                        0 грн
                                    </span>
                                </div>
                                <div class="alert alert-success mt-2 py-2 mb-0">
                                    <i class="bi bi-star-fill"></i>
                                    Ваша VIP-знижка: <strong>{{ $discountPercent }}%</strong>
                                </div>
                            </div>

                            <div id="regularPriceBlock" style="display: none;">
                                <div class="fs-2 fw-bold" id="regularPriceDisplay">
                                    0 грн
                                </div>

                                <small class="text-muted d-block mt-1" id="currencyInfo">
                                    Завантаження курсів...
                                </small>

                                <p class="text-muted mt-2 small">
                                    <a href="{{ route('subscription.index') }}" class="fw-bold text-warning" style="text-decoration: none;">
                                        👑 Станьте VIP
                                    </a>
                                    і зекономте <strong id="potentialSaveDisplay">0 грн</strong> ({{ $discountPercent }}%)!
                                </p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Перейти до оплати</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Отримуємо дані з PHP
        const pricePerHour = {{ $pricePerHour }};
        const discountPercent = {{ $discountPercent }};
        const isVip = {{ $isVip ? 'true' : 'false' }};

        // Отримуємо курси валют з контролера
        // (Якщо контролер їх не передав, ставимо 1, щоб не ламалось)
        const usdRate = {{ $usdRate ?? 1 }};
        const eurRate = {{ $eurRate ?? 1 }};

        // Знаходимо елементи
        const durationSelect = document.getElementById('durationSelect');

        const vipBlock = document.getElementById('vipPriceBlock');
        const oldPriceDisplay = document.getElementById('oldPriceDisplay');
        const vipFinalPriceDisplay = document.getElementById('vipFinalPriceDisplay');

        const regularBlock = document.getElementById('regularPriceBlock');
        const regularPriceDisplay = document.getElementById('regularPriceDisplay');
        const potentialSaveDisplay = document.getElementById('potentialSaveDisplay');

        const currencyInfoDisplay = document.getElementById('currencyInfo');

        function updatePrice() {
            const hours = parseInt(durationSelect.value);

            // Базова ціна
            const baseTotal = pricePerHour * hours;

            // Розмір знижки
            const discountAmount = baseTotal * (discountPercent / 100);

            // Фінальна ціна для VIP
            const vipTotal = baseTotal - discountAmount;

            if (isVip) {
                // Якщо VIP: показуємо стару ціну (перекреслену) та нову зі знижкою
                vipBlock.style.display = 'block';
                regularBlock.style.display = 'none';

                oldPriceDisplay.innerText = Math.round(baseTotal) + ' грн';
                vipFinalPriceDisplay.innerText = Math.round(vipTotal) + ' грн';
            } else {
                // Якщо не VIP: показуємо звичайну ціну та суму потенційної економії
                vipBlock.style.display = 'none';
                regularBlock.style.display = 'block';

                regularPriceDisplay.innerText = Math.round(baseTotal) + ' грн';
                potentialSaveDisplay.innerText = Math.round(discountAmount) + ' грн';

                // Конвертація у валюту в реальному часі
                if (usdRate > 1 && eurRate > 1) {
                    const priceInUsd = (baseTotal / usdRate).toFixed(2);
                    const priceInEur = (baseTotal / eurRate).toFixed(2);

                    currencyInfoDisplay.innerHTML = `
                        <i class="bi bi-currency-exchange"></i>
                        Еквівалент: <strong>$${priceInUsd}</strong> / <strong>€${priceInEur}</strong>
                        <br><span style="font-size: 0.8em">(Курс ПБ: ${usdRate} / ${eurRate})</span>
                    `;
                } else {
                    currencyInfoDisplay.innerText = '';
                }
            }
        }

        // Оновлювати ціну щоразу, коли змінюється вибір у списку "Тривалість"
        durationSelect.addEventListener('change', updatePrice);
        updatePrice();
    </script>
@endsection
