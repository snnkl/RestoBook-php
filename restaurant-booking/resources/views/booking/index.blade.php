@extends('layouts.app')
@section('title', 'Мої бронювання')
@section('content')
    <h2 class="mb-4">📋 Історія бронювань</h2>

    @if($bookings->isEmpty())
        <div class="text-center py-5">
            <h4 class="text-muted">У вас поки немає активних бронювань</h4>
            <a href="/" class="btn btn-primary mt-3">Обрати ресторан</a>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                    <tr>
                        <th class="ps-4">№</th>
                        <th>Ресторан</th>
                        <th>Столик</th>
                        <th>Час візиту</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($bookings as $booking)
                        <tr>
                            <td class="ps-4 text-muted">
                                #{{ $booking->id }}
                            </td>

                            <td>
                                <strong class="text-dark">{{ $booking->table->restaurant->name }}</strong>
                                <div class="small text-muted">{{ $booking->table->restaurant->address }}</div>
                            </td>

                            <td>{{ $booking->table->number }}</td>

                            <td>
                                {{ $booking->start_time->format('d.m.Y о H:i') }}
                            </td>

                            <td>
                                @if($booking->status === 'confirmed')
                                    <span class="badge bg-success">Підтверджено</span>
                                @elseif($booking->status === 'pending')
                                    <span class="badge bg-warning text-dark">Очікує оплати</span>
                                    <div class="mt-1">
                                        <a href="{{ route('booking.pay', $booking->id) }}" class="btn btn-sm btn-primary py-0" style="font-size: 0.8rem;">
                                            Сплатити
                                        </a>
                                    </div>
                                @else
                                    <span class="badge bg-secondary">{{ $booking->status }}</span>
                                @endif
                            </td>

                            <td class="text-end pe-4">
                                <form action="{{ route('booking.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Ви впевнені, що хочете скасувати це бронювання?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmCancel(this)">
                                        Скасувати
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <script>
            function confirmCancel(button) {
                Swal.fire({
                    title: 'Ви впевнені?',
                    text: "Це бронювання буде скасовано безповоротно!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Так, скасувати!',
                    cancelButtonText: 'Ні, залишити',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {

                        button.closest('form').submit();
                    }
                })
            }
        </script>
    @endif
@endsection
