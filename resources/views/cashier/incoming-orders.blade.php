@extends('layouts.app')

@section('title', 'NORD CAFE - Cashier Pesanan Minuman')

@section('content')
<main class="flex-1 px-margin-mobile pt-unit-md pb-[100px] max-w-3xl mx-auto w-full flex flex-col gap-unit-md">
    <!-- Header Section -->
    <section class="mb-unit-sm flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary mb-1">Bagian Cashier</p>
            <h2 class="text-headline-lg font-headline-lg text-on-surface mb-1 tracking-tight">
                {{ $statusFilter === 'completed' ? 'Riwayat Minuman Selesai' : 'Pesanan Minuman Masuk' }}
            </h2>
            <p class="text-body-secondary font-body-secondary text-status-unpaid">
                {{ $statusFilter === 'completed' ? 'Minuman yang telah selesai diproses' : 'Hanya item minuman yang menjadi tanggung jawab Cashier' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($statusFilter === 'completed')
                <a href="{{ route('cashier.incomingOrders') }}" class="btn-cta px-4 py-2 text-xs rounded-xl">
                    Pesanan Aktif
                </a>
            @else
                <a href="{{ route('cashier.incomingOrders', ['status' => 'completed']) }}" class="btn-soft px-4 py-2 text-xs rounded-xl">
                    Riwayat ({{ $stats['completed'] }})
                </a>
            @endif
        </div>
    </section>

    <!-- Drink item stats -->
    <section class="grid grid-cols-3 gap-2">
        <div class="card-soft rounded-2xl px-3 py-3 text-center">
            <p class="text-headline-sm font-headline-sm font-bold text-status-unpaid">{{ $stats['pending'] }}</p>
            <p class="text-[10px] font-bold uppercase tracking-wider text-status-unpaid mt-0.5">Menunggu</p>
        </div>
        <div class="card-soft rounded-2xl px-3 py-3 text-center">
            <p class="text-headline-sm font-headline-sm font-bold text-[#A0610D]">{{ $stats['preparing'] }}</p>
            <p class="text-[10px] font-bold uppercase tracking-wider text-status-unpaid mt-0.5">Diproses</p>
        </div>
        <div class="card-soft rounded-2xl px-3 py-3 text-center">
            <p class="text-headline-sm font-headline-sm font-bold text-status-completed">{{ $stats['completed'] }}</p>
            <p class="text-[10px] font-bold uppercase tracking-wider text-status-unpaid mt-0.5">Selesai</p>
        </div>
    </section>

    <!-- Order List Cards -->
    <section class="flex flex-col gap-unit-md">
        @forelse($orders as $order)
            @php
                $drinkStatus = $order->departmentStatus('minuman');
                $isUnpaid = ! $order->isPaymentSettled();
            @endphp
            <article class="card-soft card-hover rounded-2xl p-4 relative overflow-hidden animate-in-soft"
                     style="animation-delay: {{ min($loop->index * 40, 240) }}ms">
                <span class="absolute left-0 top-0 bottom-0 w-1 {{ $drinkStatus === 'completed' ? 'bg-status-completed' : ($drinkStatus === 'preparing' ? 'bg-status-processing' : 'bg-primary') }}"></span>

                <!-- Status Row -->
                <div class="flex justify-between items-start gap-2 mb-3">
                    <div class="flex flex-col">
                        <span class="text-label-caps font-label-caps text-on-surface tracking-wider uppercase flex items-center gap-1">
                            @if($order->order_type == 'takeaway')
                                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">shopping_bag</span>
                            @endif
                            {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'TAKEAWAY' : 'DINE IN') }}
                        </span>
                        <span class="text-[11px] text-status-unpaid mt-0.5">
                            {{ $order->created_at->format('d M Y, H:i') }} · {{ $order->customer_name }}
                        </span>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="status-pill {{ $drinkStatus === 'completed' ? 'pill-done' : ($drinkStatus === 'preparing' ? 'pill-preparing' : 'pill-unpaid') }}">
                            <span class="pill-dot"></span>{{ $order->departmentStatusLabel('minuman') }}
                        </span>
                        <span class="status-pill {{ $order->payment_status_badge_class }}">
                            <span class="pill-dot"></span>{{ $order->payment_status_label }}
                        </span>
                    </div>
                </div>

                <!-- Order Identity -->
                <div class="flex items-center justify-between mb-2">
                    <div class="flex flex-col">
                        <span class="text-headline-sm font-headline-sm text-on-surface font-semibold">{{ $order->order_number }}</span>
                        <span class="text-body-secondary text-status-unpaid text-xs">{{ $order->departmentItemCount('minuman') }} minuman · Rp{{ number_format($order->departmentSubtotal('minuman'), 0, ',', '.') }}</span>
                    </div>
                    <a href="{{ route('cashier.orderDetail', $order->id) }}" class="w-10 h-10 bg-surface-container text-on-surface-variant rounded-xl flex items-center justify-center hover:bg-primary/10 hover:text-primary transition-colors" title="Lihat detail minuman">
                        <span class="material-symbols-outlined text-xl">chevron_right</span>
                    </a>
                </div>

                @if($order->notes)
                    <p class="text-xs text-status-unpaid bg-surface-container-low rounded-xl px-3 py-2 mb-2">
                        <span class="font-semibold text-on-surface">Catatan pesanan:</span> {{ $order->notes }}
                    </p>
                @endif

                <!-- Drink Items -->
                <div class="rounded-xl border border-on-surface/10 bg-surface-container-lowest px-3 py-1 mb-3">
                    @foreach($order->items as $item)
                        <div class="flex items-start gap-3 py-2.5 {{ ! $loop->first ? 'border-t border-on-surface/5' : '' }}">
                            <img src="{{ $item->product->image_url ?? '/placeholder.svg' }}"
                                 alt="{{ $item->product->name ?? 'Menu' }}"
                                 class="w-11 h-11 rounded-xl object-cover shrink-0 border border-on-surface/5">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-body-md text-on-surface leading-tight">{{ $item->product->name ?? 'Menu' }}</p>
                                    <span class="status-pill {{ $item->status_badge_class }} shrink-0">
                                        <span class="pill-dot"></span>{{ $item->status_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-status-unpaid mt-0.5">
                                    {{ $item->quantity }}x · Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                    · Subtotal <span class="font-semibold text-on-surface">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </p>
                                @if($item->options_display)
                                    <p class="text-[11px] text-status-unpaid mt-1">
                                        <span class="font-semibold">Varian:</span> {{ $item->options_summary }}
                                    </p>
                                @endif
                                @if($item->item_notes)
                                    <p class="text-[11px] text-[#96561B] mt-1 bg-[#E3A15C]/10 rounded-lg px-2 py-1">
                                        <span class="font-bold">Catatan:</span> {{ $item->item_notes }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-2 items-center w-full">
                    @if($isUnpaid)
                        <form action="{{ route('cashier.confirmPayment', $order->id) }}" method="POST" class="flex-1 min-w-[140px]">
                            @csrf
                            <button type="submit" class="btn-cta w-full py-2.5 text-sm flex justify-center items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">sell</span> Konfirmasi Bayar
                            </button>
                        </form>
                    @endif

                    @if($drinkStatus !== 'completed')
                        <form action="{{ route('cashier.updateDepartmentStatus', $order->id) }}" method="POST" class="flex-1 min-w-[120px]">
                            @csrf
                            <input type="hidden" name="status" value="preparing">
                            <button type="submit" class="btn-soft w-full py-2.5 text-sm flex justify-center items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">local_cafe</span> Proses Minuman
                            </button>
                        </form>
                        <form action="{{ route('cashier.updateDepartmentStatus', $order->id) }}" method="POST" class="flex-1 min-w-[120px]">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn-cta-success w-full py-2.5 text-sm flex justify-center items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">check_circle</span> Selesai
                            </button>
                        </form>
                    @else
                        <div class="flex-1 text-xs text-status-completed font-semibold flex items-center gap-1.5 px-2">
                            <span class="material-symbols-outlined text-base">task_alt</span> Minuman selesai diproses
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="card-soft rounded-2xl p-8 text-center animate-in-soft">
                <span class="material-symbols-outlined text-5xl text-status-unpaid mb-2">local_cafe</span>
                <h3 class="text-headline-sm font-bold text-on-surface">Tidak Ada Pesanan Minuman</h3>
                <p class="text-body-secondary text-status-unpaid mt-1">
                    {{ $statusFilter === 'completed' ? 'Belum ada minuman yang diselesaikan.' : 'Belum ada pesanan minuman aktif yang perlu diproses.' }}
                </p>
            </div>
        @endforelse
    </section>
</main>

@push('scripts')
<script>
    (() => {
        let signature = @json($feedSignature);
        const endpoint = @json(route('cashier.feed'));

        setInterval(async () => {
            try {
                const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                if (data.signature && data.signature !== signature) {
                    window.location.reload();
                }
            } catch (error) {
                // Silent: polling must never interrupt the cashier workflow.
            }
        }, 15000);
    })();
</script>
@endpush
@endsection
