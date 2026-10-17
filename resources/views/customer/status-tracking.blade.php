@extends('layouts.app')

@section('title', 'NORD CAFE - Status Pesanan ' . $order->order_number)

@section('content')
<main class="flex-grow px-margin-mobile pt-unit-md flex flex-col gap-unit-md max-w-xl mx-auto w-full relative z-10 pb-24">
    <div class="mt-unit-sm">
        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-status-unpaid mb-1">Status Pesanan</p>
        <h2 class="text-headline-sm font-headline-sm font-bold text-on-surface tracking-tight">{{ $order->order_number }}</h2>
    </div>

    <!-- Status Card -->
    <div class="card-soft p-unit-md flex flex-col gap-0 relative">
        
        <!-- Timeline Item 1: Belum Dibayar -->
        <div class="flex gap-unit-md relative timeline-item timeline-line pb-unit-lg">
            <div class="relative z-10 w-6 h-6 rounded-full bg-primary flex items-center justify-center shrink-0 mt-1 shadow-sm">
                <span class="material-symbols-outlined text-on-primary text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
            </div>
            <div class="flex flex-col">
                <span class="text-body-lg font-body-lg font-semibold text-on-surface">Belum Dibayar</span>
                <span class="text-label-caps font-label-caps text-status-unpaid mt-1">{{ $order->created_at->format('H:i A') }}</span>
            </div>
        </div>

        <!-- Timeline Item 2: Sudah Dibayar -->
        <div class="flex gap-unit-md relative timeline-item timeline-line pb-unit-lg">
            @if(in_array($order->status, ['paid', 'preparing', 'ready', 'completed']))
                <div class="relative z-10 w-6 h-6 rounded-full bg-primary flex items-center justify-center shrink-0 mt-1 shadow-sm">
                    <span class="material-symbols-outlined text-on-primary text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg font-semibold text-on-surface">Sudah Dibayar</span>
                    <span class="text-label-caps font-label-caps text-status-unpaid mt-1">{{ $order->updated_at->format('H:i A') }}</span>
                </div>
            @else
                <div class="relative z-10 w-6 h-6 rounded-full border-2 border-outline-variant bg-surface-container-lowest flex items-center justify-center shrink-0 mt-1"></div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg text-outline font-medium mt-1">Sudah Dibayar</span>
                </div>
            @endif
        </div>

        <!-- Timeline Item 3: Sedang Diproses -->
        <div class="flex gap-unit-md relative timeline-item timeline-line pb-unit-lg">
            @if(in_array($order->status, ['preparing', 'ready']))
                <div class="relative z-10 w-6 h-6 rounded-full bg-status-processing flex items-center justify-center shrink-0 mt-1 shadow-sm ring-4 ring-status-processing/20">
                    <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                </div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg font-bold text-status-processing">Sedang Diproses</span>
                    <span class="text-body-md font-body-md text-on-surface-variant mt-1">Barista sedang menyiapkan pesanan Anda.</span>
                </div>
            @elseif($order->status === 'completed')
                <div class="relative z-10 w-6 h-6 rounded-full bg-primary flex items-center justify-center shrink-0 mt-1 shadow-sm">
                    <span class="material-symbols-outlined text-on-primary text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg font-semibold text-on-surface">Sedang Diproses</span>
                </div>
            @else
                <div class="relative z-10 w-6 h-6 rounded-full border-2 border-outline-variant bg-surface-container-lowest flex items-center justify-center shrink-0 mt-1"></div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg text-outline font-medium mt-1">Sedang Diproses</span>
                </div>
            @endif
        </div>

        <!-- Timeline Item 4: Selesai -->
        <div class="flex gap-unit-md relative timeline-item">
            @if($order->status === 'completed')
                <div class="relative z-10 w-6 h-6 rounded-full bg-status-completed flex items-center justify-center shrink-0 mt-1 shadow-sm ring-4 ring-status-completed/20">
                    <span class="material-symbols-outlined text-on-primary text-[14px]" style="font-variation-settings: 'FILL' 1;">check</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg font-bold text-status-completed">Selesai</span>
                    <span class="text-body-md font-body-md text-on-surface-variant mt-1">Pesanan Anda siap diambil / disajikan!</span>
                </div>
            @else
                <div class="relative z-10 w-6 h-6 rounded-full border-2 border-outline-variant bg-surface-container-lowest flex items-center justify-center shrink-0 mt-1"></div>
                <div class="flex flex-col">
                    <span class="text-body-lg font-body-lg text-outline font-medium mt-1">Selesai</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Refresh Action -->
    <button id="btn-refresh-status" class="btn-soft mt-unit-sm py-3 px-6 flex items-center justify-center gap-2 text-label-caps font-label-caps" onclick="window.location.reload();">
        <span class="material-symbols-outlined text-[18px]">refresh</span>
        Segarkan Status
    </button>
</main>

<style>
    .timeline-line::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 24px;
        bottom: -8px;
        width: 2px;
        background-color: rgba(28, 26, 23, 0.1);
        z-index: 0;
    }
    html.dark .timeline-line::before {
        background-color: rgba(255, 255, 255, 0.1);
    }
    .timeline-item:last-child .timeline-line::before {
        display: none;
    }
</style>

@push('scripts')
<script>
    // Real-time polling to update status automatically
    const orderNumber = "{{ $order->order_number }}";
    let currentStatus = "{{ $order->status }}";

    setInterval(async () => {
        try {
            const res = await fetch(`/api/order-status/${orderNumber}`);
            const data = await res.json();
            if (data.status !== currentStatus) {
                window.location.reload();
            }
        } catch (e) {}
    }, 3000);
</script>
@endpush
@endsection
