@extends('layouts.app')

@section('title', 'NORD CAFE - Cashier Pesanan Masuk')

@section('content')
<main class="flex-1 px-margin-mobile pt-unit-md pb-[100px] max-w-2xl mx-auto w-full flex flex-col gap-unit-md">
    <!-- Header Section -->
    <section class="mb-unit-sm flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <h2 class="text-headline-lg font-headline-lg text-on-surface mb-1 tracking-tight">
                {{ $statusFilter === 'completed' ? 'Riwayat Pesanan Selesai' : 'Pesanan Masuk' }}
            </h2>
            <p class="text-body-secondary font-body-secondary text-status-unpaid">
                {{ $statusFilter === 'completed' ? 'Daftar pesanan yang telah selesai disajikan' : 'Kelola dan proses order aktif' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($statusFilter === 'completed')
                <a href="{{ route('cashier.incomingOrders') }}" class="btn-cta px-4 py-2 text-xs rounded-xl">
                    Lihat Pesanan Aktif
                </a>
            @elseif($stats['completed'] > 0)
                <a href="{{ route('cashier.incomingOrders', ['status' => 'completed']) }}" class="btn-soft px-4 py-2 text-xs rounded-xl">
                    Riwayat Selesai ({{ $stats['completed'] }})
                </a>
            @endif
        </div>
    </section>

    <!-- Order List Cards (Exact match with public/desain/cashier_incoming_orders/code.html) -->
    <section class="flex flex-col gap-unit-md">
        @forelse($orders as $order)
            @if($order->status == 'completed')
                <!-- Card: SELESAI (Muted state) -->
                <article class="card-soft rounded-2xl p-4 opacity-75 transition-all animate-in-soft" style="animation-delay: {{ min($loop->index * 40, 240) }}ms">
                    <!-- Status Row -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-label-caps font-label-caps text-status-unpaid tracking-wider uppercase">
                                {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'TAKEAWAY' : 'DINE IN') }}
                            </span>
                        </div>
                        <span class="status-pill pill-done"><span class="pill-dot"></span>Selesai</span>
                    </div>
                    <!-- Order Details -->
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col">
                            <span class="text-headline-sm font-headline-sm text-on-surface-variant line-through decoration-1">{{ $order->order_number }}</span>
                            <span class="text-body-secondary text-status-unpaid text-xs mt-0.5">{{ $order->items->sum('quantity') }} Item — Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('cashier.orderDetail', $order->id) }}" class="w-10 h-10 bg-surface-container text-on-surface-variant rounded-xl flex items-center justify-center hover:bg-primary/10 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">chevron_right</span>
                        </a>
                    </div>
                </article>

            @elseif($order->status == 'preparing' || $order->status == 'ready')
                <!-- Card: SEDANG DIPROSES -->
                <article class="card-soft card-hover rounded-2xl p-4 relative overflow-hidden group animate-in-soft" style="animation-delay: {{ min($loop->index * 40, 240) }}ms">
                    <!-- Color accent -->
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-status-processing"></span>
                    <!-- Status Row -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-label-caps font-label-caps text-on-surface tracking-wider flex items-center gap-1 uppercase">
                                @if($order->order_type == 'takeaway')
                                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">shopping_bag</span>
                                @endif
                                {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'TAKEAWAY' : 'DINE IN') }}
                            </span>
                        </div>
                        <span class="status-pill pill-preparing"><span class="pill-dot"></span>Sedang Diproses</span>
                    </div>
                    <!-- Order Details -->
                    <div class="flex flex-col mb-4">
                        <span class="text-headline-sm font-headline-sm text-on-surface font-semibold">{{ $order->order_number }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-body-secondary font-body-secondary text-status-unpaid">{{ $order->items->sum('quantity') }} Item — Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex gap-2 items-center w-full">
                        <form action="{{ route('cashier.updateStatus', $order->id) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn-cta-success w-full py-3 text-sm flex items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-base">check_circle</span> Tandai Selesai
                            </button>
                        </form>
                        <a href="{{ route('cashier.editStatus', $order->id) }}" class="w-12 h-12 btn-soft flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">chevron_right</span>
                        </a>
                    </div>
                </article>

            @elseif($order->status == 'paid')
                <!-- Card: SUDAH DIBAYAR -->
                <article class="card-soft card-hover rounded-2xl p-4 relative overflow-hidden group animate-in-soft" style="animation-delay: {{ min($loop->index * 40, 240) }}ms">
                    <!-- Color accent -->
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-primary"></span>
                    <!-- Status Row -->
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-label-caps font-label-caps text-on-surface tracking-wider uppercase">
                                {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'TAKEAWAY' : 'DINE IN') }}
                            </span>
                        </div>
                        <span class="status-pill pill-paid"><span class="pill-dot"></span>Sudah Dibayar</span>
                    </div>
                    <!-- Order Details -->
                    <div class="flex flex-col mb-4">
                        <span class="text-headline-sm font-headline-sm text-on-surface font-semibold">{{ $order->order_number }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-body-secondary font-body-secondary text-status-unpaid">{{ $order->items->sum('quantity') }} Item</span>
                            <span class="w-1 h-1 rounded-full bg-status-unpaid/50"></span>
                            <span class="text-body-lg font-body-lg font-bold text-on-surface">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex gap-2 items-center w-full">
                        <form action="{{ route('cashier.updateStatus', $order->id) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="status" value="preparing">
                            <button type="submit" class="btn-cta w-full py-3 text-sm flex justify-center items-center gap-2">
                                <span class="material-symbols-outlined text-base">play_arrow</span> Proses Pesanan
                            </button>
                        </form>
                        <a href="{{ route('cashier.editStatus', $order->id) }}" class="w-12 h-12 btn-soft flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">chevron_right</span>
                        </a>
                    </div>
                </article>

            @else
                <!-- Card: BELUM DIBAYAR -->
                <article class="card-soft card-hover rounded-2xl p-4 relative overflow-hidden group animate-in-soft" style="animation-delay: {{ min($loop->index * 40, 240) }}ms">
                    <!-- Color accent -->
                    <span class="absolute left-0 top-0 bottom-0 w-1 bg-status-unpaid/50"></span>
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-label-caps font-label-caps text-on-surface tracking-wider uppercase">
                                {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'TAKEAWAY' : 'DINE IN') }}
                            </span>
                        </div>
                        <span class="status-pill pill-unpaid"><span class="pill-dot"></span>Belum Dibayar</span>
                    </div>
                    <div class="flex flex-col mb-4">
                        <span class="text-headline-sm font-headline-sm text-on-surface font-semibold">{{ $order->order_number }}</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-body-secondary font-body-secondary text-status-unpaid">{{ $order->items->sum('quantity') }} Item</span>
                            <span class="w-1 h-1 rounded-full bg-status-unpaid/50"></span>
                            <span class="text-body-lg font-body-lg font-bold text-on-surface">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2 items-center w-full">
                        <form action="{{ route('cashier.confirmPayment', $order->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="btn-cta w-full py-3 text-sm flex justify-center items-center gap-2">
                                <span class="material-symbols-outlined text-base">sell</span> Konfirmasi / Scan
                            </button>
                        </form>
                        <a href="{{ route('cashier.orderDetail', $order->id) }}" class="w-12 h-12 btn-soft flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl">chevron_right</span>
                        </a>
                    </div>
                </article>
            @endif

        @empty
        <div class="card-soft rounded-2xl p-8 text-center animate-in-soft">
            <span class="material-symbols-outlined text-5xl text-status-unpaid mb-2">inbox</span>
            <h3 class="text-headline-sm font-bold text-on-surface">Tidak Ada Pesanan</h3>
            <p class="text-body-secondary text-status-unpaid mt-1">
                {{ $statusFilter === 'completed' ? 'Belum ada riwayat pesanan yang diselesaikan.' : 'Belum ada pesanan aktif yang perlu diproses.' }}
            </p>
        </div>
        @endforelse
    </section>
</main>
@endsection
