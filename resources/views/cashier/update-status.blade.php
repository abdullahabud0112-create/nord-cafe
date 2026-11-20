@extends('layouts.app')

@section('title', 'NORD CAFE - Cashier Verifikasi ' . $order->order_number)

@section('content')
<main class="pt-10 px-margin-mobile max-w-[600px] mx-auto pb-24">
    <div class="card-soft p-unit-lg flex flex-col gap-unit-md">

        <!-- Header Section -->
        <div class="mb-2">
            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary mb-1">Hasil Pindai QR · Verifikasi Kasir</p>
            <h2 class="text-headline-sm font-headline-sm text-on-surface font-bold tracking-tight">{{ $order->order_number }}</h2>
            <p class="text-body-secondary font-body-secondary text-status-unpaid mt-1">
                {{ $order->customer_name }} ·
                {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'Takeaway' : 'Dine In') }} ·
                {{ $order->created_at->format('d M Y, H:i') }}
            </p>
        </div>

        <!-- Payment state (separate from processing state) -->
        <div class="rounded-2xl border border-on-surface/10 bg-surface-container-lowest p-unit-md">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-label-caps font-label-caps text-status-unpaid uppercase tracking-wider">Status Pembayaran</p>
                    <p class="text-body-lg font-bold text-on-surface mt-1">{{ $order->payment_status_label }}</p>
                    <p class="text-xs text-status-unpaid mt-0.5">Metode: {{ strtoupper($order->payment_method ?: 'qris') }} · Total Rp{{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
                <span class="status-pill {{ $order->payment_status_badge_class }}">
                    <span class="pill-dot"></span>{{ $order->isPaymentSettled() ? 'PAID' : 'UNPAID' }}
                </span>
            </div>

            @if(! $order->isPaymentSettled() && $order->status !== 'cancelled')
                <form action="{{ route('cashier.confirmPayment', $order->id) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn-cta w-full py-3 text-sm flex justify-center items-center gap-2">
                        <span class="material-symbols-outlined text-base">sell</span> Konfirmasi Pembayaran
                    </button>
                </form>
            @endif
        </div>

        @if($order->status === 'cancelled')
            <div class="flex flex-col items-center text-center gap-3 py-6 px-4 rounded-2xl border border-on-surface/10 bg-surface-container-lowest">
                <span class="material-symbols-outlined text-4xl text-status-unpaid">block</span>
                <p class="font-body-lg text-body-lg text-on-surface font-semibold">Pesanan ini dibatalkan dan tidak dapat diproses.</p>
            </div>
        @else
            <!-- Drink slice for the cashier -->
            <div class="rounded-2xl border border-on-surface/10 bg-surface-container-lowest p-unit-md">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div>
                        <p class="text-label-caps font-label-caps text-status-unpaid uppercase tracking-wider">Bagian Cashier · Minuman</p>
                        <p class="text-xs text-status-unpaid mt-0.5">{{ $order->departmentItemCount('minuman') }} item minuman</p>
                    </div>
                    <span class="status-pill {{ $order->departmentStatusBadgeClass('minuman') }}">
                        <span class="pill-dot"></span>{{ $order->departmentStatusLabel('minuman') }}
                    </span>
                </div>

                @forelse($order->items as $item)
                    <div class="flex items-start justify-between gap-3 py-2.5 {{ ! $loop->first ? 'border-t border-on-surface/10' : '' }}">
                        <div class="min-w-0">
                            <p class="font-semibold text-body-md text-on-surface">{{ $item->quantity }}x {{ $item->product->name ?? 'Menu' }}</p>
                            <p class="text-xs text-status-unpaid mt-0.5">Rp{{ number_format($item->unit_price, 0, ',', '.') }} · Subtotal Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            @if($item->options_display)
                                <p class="text-[11px] text-status-unpaid mt-1">Varian: {{ $item->options_summary }}</p>
                            @endif
                            @if($item->item_notes)
                                <p class="text-[11px] text-[#96561B] mt-1">Catatan: {{ $item->item_notes }}</p>
                            @endif
                        </div>
                        <span class="status-pill {{ $item->status_badge_class }} shrink-0">
                            <span class="pill-dot"></span>{{ $item->status_label }}
                        </span>
                    </div>
                @empty
                    <p class="text-body-secondary text-status-unpaid py-3">
                        Pesanan ini tidak memiliki item minuman. Item makanan diproses di halaman Kitchen.
                    </p>
                @endforelse

                @if($order->departmentStatus('minuman') !== null)
                    <div class="flex gap-2 mt-3">
                        @if($order->departmentStatus('minuman') !== 'completed')
                            <form action="{{ route('cashier.updateDepartmentStatus', $order->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="preparing">
                                <button type="submit" class="btn-soft w-full py-2.5 text-sm">Proses Minuman</button>
                            </form>
                            <form action="{{ route('cashier.updateDepartmentStatus', $order->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn-cta-success w-full py-2.5 text-sm">Selesai</button>
                            </form>
                        @else
                            <p class="text-xs text-status-completed font-semibold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">task_alt</span> Minuman selesai diproses
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Actions -->
        <div class="mt-2 flex justify-end gap-3">
            <a href="{{ route('cashier.incomingOrders') }}" class="btn-soft px-6 py-2.5 text-body-lg flex items-center justify-center">
                Pesanan Masuk
            </a>
            <a href="{{ route('cashier.orderDetail', $order->id) }}" class="btn-cta px-6 py-2.5 text-body-lg flex items-center justify-center">
                Lihat Detail
            </a>
        </div>
    </div>
</main>
@endsection
