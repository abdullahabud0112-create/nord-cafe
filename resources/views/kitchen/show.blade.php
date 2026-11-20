@extends('layouts.app')

@section('title', 'NORD CAFE - Kitchen Detail ' . $order->order_number)

@section('content')
<main class="flex-1 px-margin-mobile pt-unit-md pb-32 max-w-2xl mx-auto w-full flex flex-col gap-unit-md">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('kitchen.index') }}" class="w-10 h-10 rounded-xl btn-soft flex items-center justify-center" aria-label="Kembali">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="text-center">
            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary">Detail Dapur</p>
            <h2 class="text-headline-md font-headline-md font-bold tracking-tight">{{ $order->order_number }}</h2>
        </div>
        <div class="w-10"></div>
    </div>

    <!-- Order meta -->
    <section class="card-soft p-unit-md flex flex-col gap-2">
        <div class="flex items-center justify-between gap-2">
            <div>
                <p class="text-body-lg font-bold text-on-surface">{{ $order->customer_name }}</p>
                <p class="text-body-secondary text-status-unpaid text-xs mt-0.5">
                    {{ $order->created_at->format('d M Y, H:i') }} ·
                    {{ $order->table_number ?: ($order->order_type == 'takeaway' ? 'Takeaway' : 'Dine In') }}
                </p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="status-pill {{ $order->departmentStatusBadgeClass('makanan') }}">
                    <span class="pill-dot"></span>{{ $order->departmentStatusLabel('makanan') }}
                </span>
                <span class="status-pill {{ $order->payment_status_badge_class }}">
                    <span class="pill-dot"></span>{{ $order->payment_status_label }}
                </span>
            </div>
        </div>

        @if($order->notes)
            <p class="text-xs text-status-unpaid bg-surface-container-low rounded-xl px-3 py-2">
                <span class="font-semibold text-on-surface">Catatan pesanan:</span> {{ $order->notes }}
            </p>
        @endif

        @if($order->status === 'cancelled')
            <p class="text-xs font-semibold text-[#B42318] bg-[#B42318]/10 rounded-xl px-3 py-2">
                Pesanan ini dibatalkan dan tidak dapat diproses.
            </p>
        @endif
    </section>

    <!-- Food items -->
    <section class="card-soft p-unit-md">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-label-caps font-label-caps text-status-unpaid uppercase tracking-wider">
                Item Makanan ({{ $order->departmentItemCount('makanan') }})
            </h3>
            @if($order->departmentStatus('makanan') !== 'completed' && $order->status !== 'cancelled')
                <div class="flex gap-2">
                    <form action="{{ route('kitchen.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="preparing">
                        <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg btn-soft">Proses Semua</button>
                    </form>
                    <form action="{{ route('kitchen.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg btn-cta-success">Selesai Semua</button>
                    </form>
                </div>
            @endif
        </div>

        @forelse($order->items as $item)
            <article class="flex flex-col gap-2 py-3 {{ ! $loop->first ? 'border-t border-on-surface/10' : '' }}">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 flex flex-col items-center gap-1">
                        <span class="text-headline-sm font-headline-sm font-extrabold text-primary leading-none">{{ $item->quantity }}x</span>
                        <img src="{{ $item->product->image_url ?? '/placeholder.svg' }}"
                             alt="{{ $item->product->name ?? 'Menu' }}"
                             class="w-14 h-14 rounded-xl object-cover border border-on-surface/5">
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-body-lg text-on-surface leading-tight">{{ $item->product->name ?? 'Menu' }}</p>
                            <span class="status-pill {{ $item->status_badge_class }} shrink-0">
                                <span class="pill-dot"></span>{{ $item->status_label }}
                            </span>
                        </div>
                        <p class="text-xs text-status-unpaid mt-1">
                            Jumlah: <span class="font-semibold text-on-surface">{{ $item->quantity }}x</span>
                            · Harga: Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                            · Subtotal: <span class="font-semibold text-on-surface">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </p>
                        @if($item->options_display)
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($item->options_display as $optionLine)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-surface-container text-on-surface-variant">{{ $optionLine }}</span>
                                @endforeach
                            </div>
                        @endif
                        @if($item->item_notes)
                            <p class="text-xs text-[#96561B] mt-1.5 bg-[#E3A15C]/15 border border-[#E3A15C]/30 rounded-lg px-2.5 py-1.5">
                                <span class="font-bold">Catatan:</span> {{ $item->item_notes }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($item->status !== 'completed' && $order->status !== 'cancelled')
                    <div class="flex justify-end gap-2">
                        @if($item->status === 'pending')
                            <form action="{{ route('kitchen.updateItemStatus', [$order->id, $item->id]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="preparing">
                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg btn-soft">Proses</button>
                            </form>
                        @endif
                        <form action="{{ route('kitchen.updateItemStatus', [$order->id, $item->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg btn-cta-success">Selesai</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <div class="py-6 text-center">
                <span class="material-symbols-outlined text-4xl text-status-unpaid">no_meals</span>
                <p class="text-body-secondary text-status-unpaid mt-1">Pesanan ini tidak memiliki item makanan.</p>
            </div>
        @endforelse
    </section>

    <!-- Summary -->
    <section class="card-soft p-unit-md flex flex-col gap-2">
        <div class="flex justify-between text-body-md">
            <span class="text-status-unpaid">Subtotal makanan</span>
            <span class="font-semibold">Rp{{ number_format($order->departmentSubtotal('makanan'), 0, ',', '.') }}</span>
        </div>
        <hr class="divider-soft">
        <div class="flex justify-between">
            <span class="text-body-lg font-bold">Total Transaksi Utama</span>
            <span class="text-body-lg font-bold text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <p class="text-[11px] text-status-unpaid">Satu pesanan, satu kode, satu total. Item minuman tidak ditampilkan di sini dan diproses Cashier.</p>
    </section>
</main>
@endsection
