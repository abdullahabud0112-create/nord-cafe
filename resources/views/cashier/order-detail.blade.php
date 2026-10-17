@extends('layouts.app')

@section('title', 'NORD CAFE - Cashier Detail ' . $order->order_number)

@section('content')
<div class="w-full max-w-[420px] mx-auto relative bg-background-warm min-h-[680px] flex flex-col shadow-lg rounded-2xl overflow-hidden mb-20">
    <!-- Main Content -->
    <main class="flex-1 px-margin-mobile py-unit-lg overflow-y-auto pb-32">
        <!-- Header Section -->
        <div class="mb-unit-md">
            <div class="flex items-center justify-between gap-3 mb-unit-xs">
                <h2 class="text-headline-sm font-headline-sm font-bold tracking-tight">Detail Pesanan {{ $order->order_number }}</h2>
                
                @if($order->status == 'unpaid' || $order->status == 'pending')
                    <span class="status-pill pill-unpaid"><span class="pill-dot"></span>Belum Dibayar</span>
                @elseif($order->status == 'paid')
                    <span class="status-pill pill-paid"><span class="pill-dot"></span>Sudah Dibayar</span>
                @elseif($order->status == 'preparing' || $order->status == 'ready')
                    <span class="status-pill pill-preparing"><span class="pill-dot"></span>Sedang Diproses</span>
                @elseif($order->status == 'completed')
                    <span class="status-pill pill-done"><span class="pill-dot"></span>Selesai</span>
                @endif
            </div>

            <div class="flex items-center text-status-unpaid text-body-secondary font-body-secondary gap-1">
                <span class="material-symbols-outlined text-[16px]">schedule</span>
                <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                <span class="mx-1">·</span>
                <span>{{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'Takeaway' : 'Dine In') }}</span>
            </div>
        </div>

        <!-- Receipt Card -->
        <div class="card-soft p-gutter mb-4">
            <div class="flex justify-between items-center mb-unit-md">
                <h3 class="text-label-caps font-label-caps text-status-unpaid uppercase tracking-wider">ITEM PESANAN</h3>
                <a href="{{ route('cashier.editStatus', $order->id) }}" class="text-xs text-primary font-bold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">edit</span> Ubah Status
                </a>
            </div>
            
            <div class="flex flex-col gap-unit-md">
                @foreach($order->items as $item)
                <div class="flex justify-between items-start gap-3">
                    <div class="flex items-start gap-2 min-w-0">
                        <span class="shrink-0 mt-0.5 px-1.5 py-0.5 rounded-md bg-surface-container text-on-surface-variant text-[11px] font-bold">{{ $item->quantity }}x</span>
                        <p class="font-semibold text-body-lg text-on-surface truncate">{{ $item->product->name ?? 'Menu Item' }}</p>
                    </div>
                    <p class="font-bold text-body-lg text-on-surface whitespace-nowrap">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>

            <hr class="divider-soft my-unit-md">

            <!-- Total -->
            <div class="flex justify-between items-center">
                <p class="text-headline-sm font-headline-sm font-bold text-on-surface">Total</p>
                <p class="text-headline-sm font-headline-sm font-bold text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</p>
            </div>
        </div>
    </main>

    <!-- Action Area -->
    <div class="absolute bottom-0 left-0 w-full bg-background-warm/95 dark:bg-[#121212]/85 backdrop-blur-xl px-margin-mobile py-unit-md z-20 border-t border-on-surface/10 dark:border-white/10">
        <div class="flex gap-unit-sm w-full">
            <a href="{{ route('cashier.incomingOrders') }}" class="btn-soft w-[35%] text-body-lg rounded-xl py-3 flex items-center justify-center">
                Batal
            </a>

            @if($order->status == 'unpaid' || $order->status == 'pending')
                <form action="{{ route('cashier.confirmPayment', $order->id) }}" method="POST" class="w-[65%]">
                    @csrf
                    <button type="submit" class="btn-cta w-full text-body-lg py-3 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">sell</span>
                        Konfirmasi / Scan
                    </button>
                </form>
            @elseif($order->status == 'paid')
                <form action="{{ route('cashier.updateStatus', $order->id) }}" method="POST" class="w-[65%]">
                    @csrf
                    <input type="hidden" name="status" value="preparing">
                    <button type="submit" class="btn-cta w-full text-body-lg py-3 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">play_arrow</span>
                        Proses Pesanan
                    </button>
                </form>
            @elseif($order->status == 'preparing' || $order->status == 'ready')
                <form action="{{ route('cashier.updateStatus', $order->id) }}" method="POST" class="w-[65%]">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn-cta-success w-full text-body-lg py-3 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">check</span>
                        Tandai Selesai
                    </button>
                </form>
            @else
                <a href="{{ route('cashier.editStatus', $order->id) }}" class="btn-soft w-[65%] text-body-lg py-3 flex items-center justify-center gap-2">
                    Ubah Status
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
