@extends('layouts.app')

@section('title', 'NORD CAFE - Cashier Update Status')

@section('content')
<main class="pt-10 px-margin-mobile max-w-[600px] mx-auto pb-24">
    <div class="card-soft p-unit-lg flex flex-col gap-unit-md">
        
        <!-- Header Section -->
        <div class="mb-2">
            <h2 class="text-headline-sm font-headline-sm text-on-surface font-bold tracking-tight">Perbarui Status</h2>
            <p class="text-body-secondary font-body-secondary text-status-unpaid mt-1">
                {{ $order->order_number }} · Rp{{ number_format($order->total, 0, ',', '.') }}
                @if($order->table_number)
                    · <span class="font-semibold text-on-surface">{{ $order->table_number }}</span>
                @endif
            </p>
        </div>

        @if($order->isTerminal())
        <!-- Terminal state: order can never be updated again -->
        <div class="flex flex-col items-center text-center gap-3 py-6 px-4 rounded-2xl border border-on-surface/10 bg-surface-container-lowest">
            <span class="material-symbols-outlined text-4xl text-status-unpaid">lock</span>
            <p class="font-body-lg text-body-lg text-on-surface font-semibold">
                Pesanan ini sudah {{ $order->status_label }} dan tidak dapat diubah lagi.
            </p>
            <a href="{{ route('cashier.orderDetail', $order->id) }}"
               class="btn-soft px-6 py-2.5 text-body-lg flex items-center justify-center">
                Lihat Detail Pesanan
            </a>
        </div>
        @else
        <form id="update-status-form" action="{{ route('cashier.updateStatus', $order->id) }}" method="POST">
            @csrf
            
            <input type="hidden" name="status" id="selected-status-input" value="{{ in_array($order->status, ['preparing', 'ready']) ? 'preparing' : ($order->status == 'completed' ? 'completed' : 'paid') }}">

            <!-- Status Options -->
            <div class="flex flex-col gap-unit-sm" id="status-options-container">
                
                <!-- Option 1: Sudah Dibayar -->
                @php
                    $isPaidSelected = ($order->status == 'paid');
                @endphp
                <button type="button" 
                        data-status="paid"
                        class="status-option-btn w-full flex items-center justify-between p-unit-md rounded-xl transition-all {{ $isPaidSelected ? 'border border-primary/50 bg-primary/10 text-primary shadow-sm' : 'border border-on-surface/10 bg-surface-container-lowest hover:border-primary/30 hover:bg-primary/5 text-on-surface' }}">
                    <div class="flex items-center gap-3">
                        <span class="status-emoji flex items-center justify-center w-9 h-9 rounded-full {{ $isPaidSelected ? 'bg-primary/15 text-primary' : 'bg-surface-container text-on-surface-variant' }} shrink-0">💵</span>
                        <span class="font-body-lg text-body-lg label-text {{ $isPaidSelected ? 'font-bold text-primary' : 'text-on-surface' }}">
                            Sudah Dibayar
                        </span>
                    </div>
                    <span class="material-symbols-outlined status-icon {{ $isPaidSelected ? 'text-primary' : 'text-status-unpaid' }}" 
                          style="{{ $isPaidSelected ? 'font-variation-settings: \'FILL\' 1;' : '' }}">
                        {{ $isPaidSelected ? 'radio_button_checked' : 'check_circle' }}
                    </span>
                </button>

                <!-- Option 2: Sedang Diproses -->
                @php
                    $isPreparingSelected = in_array($order->status, ['preparing', 'ready']);
                @endphp
                <button type="button" 
                        data-status="preparing"
                        class="status-option-btn w-full flex items-center justify-between p-unit-md rounded-xl transition-all {{ $isPreparingSelected ? 'border border-primary/50 bg-primary/10 text-primary shadow-sm' : 'border border-on-surface/10 bg-surface-container-lowest hover:border-primary/30 hover:bg-primary/5 text-on-surface' }}">
                    <div class="flex items-center gap-3">
                        <span class="status-emoji flex items-center justify-center w-9 h-9 rounded-full {{ $isPreparingSelected ? 'bg-primary/15 text-primary' : 'bg-surface-container text-on-surface-variant' }} shrink-0">☕</span>
                        <span class="font-body-lg text-body-lg label-text {{ $isPreparingSelected ? 'font-bold text-primary' : 'text-on-surface' }}">
                            Sedang Diproses
                        </span>
                    </div>
                    <span class="material-symbols-outlined status-icon {{ $isPreparingSelected ? 'text-primary' : 'text-status-unpaid' }}"
                          style="{{ $isPreparingSelected ? 'font-variation-settings: \'FILL\' 1;' : '' }}">
                        {{ $isPreparingSelected ? 'radio_button_checked' : 'radio_button_unchecked' }}
                    </span>
                </button>

                <!-- Option 3: Selesai -->
                @php
                    $isCompletedSelected = ($order->status == 'completed');
                @endphp
                <button type="button" 
                        data-status="completed"
                        class="status-option-btn w-full flex items-center justify-between p-unit-md rounded-xl transition-all {{ $isCompletedSelected ? 'border border-primary/50 bg-primary/10 text-primary shadow-sm' : 'border border-on-surface/10 bg-surface-container-lowest hover:border-primary/30 hover:bg-primary/5 opacity-70 hover:opacity-100 text-on-surface' }}">
                    <div class="flex items-center gap-3">
                        <span class="status-emoji flex items-center justify-center w-9 h-9 rounded-full {{ $isCompletedSelected ? 'bg-primary/15 text-primary' : 'bg-surface-container text-status-unpaid' }} shrink-0 text-sm">✓✓</span>
                        <span class="font-body-lg text-body-lg label-text {{ $isCompletedSelected ? 'font-bold text-primary' : 'text-status-unpaid' }}">
                            Selesai
                        </span>
                    </div>
                    <span class="material-symbols-outlined status-icon {{ $isCompletedSelected ? 'text-primary' : 'text-status-unpaid' }}"
                          style="{{ $isCompletedSelected ? 'font-variation-settings: \'FILL\' 1;' : '' }}">
                        {{ $isCompletedSelected ? 'radio_button_checked' : 'radio_button_unchecked' }}
                    </span>
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('cashier.orderDetail', $order->id) }}" 
                   class="btn-soft px-6 py-2.5 text-body-lg flex items-center justify-center">
                    Batal
                </a>
                <button type="submit"
                        id="btn-save-status"
                        class="btn-cta px-6 py-2.5 text-body-lg flex items-center justify-center gap-2">
                    Simpan
                </button>
            </div>
        </form>
        @endif
    </div>
</main>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('.status-option-btn');
        const hiddenInput = document.getElementById('selected-status-input');
        const form = document.getElementById('update-status-form');
        const saveBtn = document.getElementById('btn-save-status');

        if (!form) return;

        const baseInactive = 'status-option-btn w-full flex items-center justify-between p-unit-md rounded-xl transition-all border border-on-surface/10 bg-surface-container-lowest hover:border-primary/30 hover:bg-primary/5 text-on-surface';
        const activeSelected = 'status-option-btn w-full flex items-center justify-between p-unit-md rounded-xl transition-all border border-primary/50 bg-primary/10 text-primary shadow-sm';

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetStatus = btn.getAttribute('data-status');
                hiddenInput.value = targetStatus;

                // Reset all buttons to unselected state
                buttons.forEach(b => {
                    const status = b.getAttribute('data-status');
                    const label = b.querySelector('.label-text');
                    const icon = b.querySelector('.status-icon');
                    const emojiWrap = b.querySelector('.status-emoji');

                    b.className = (status === 'completed') ? baseInactive + ' opacity-70 hover:opacity-100' : baseInactive;
                    label.className = (status === 'completed')
                        ? 'font-body-lg text-body-lg label-text text-status-unpaid font-normal'
                        : 'font-body-lg text-body-lg label-text text-on-surface font-normal';
                    icon.className = 'material-symbols-outlined status-icon text-status-unpaid';
                    icon.textContent = (status === 'paid') ? 'check_circle' : 'radio_button_unchecked';
                    icon.style.fontVariationSettings = '';
                    if (emojiWrap) {
                        emojiWrap.className = 'status-emoji flex items-center justify-center w-9 h-9 rounded-full bg-surface-container text-on-surface-variant shrink-0';
                    }
                });

                // Apply selected active state to clicked button
                btn.className = activeSelected;
                const activeLabel = btn.querySelector('.label-text');
                const activeIcon = btn.querySelector('.status-icon');
                const activeEmoji = btn.querySelector('.status-emoji');
                
                activeLabel.className = 'font-body-lg text-body-lg label-text font-bold text-primary';
                activeIcon.className = 'material-symbols-outlined status-icon text-primary';
                activeIcon.textContent = 'radio_button_checked';
                activeIcon.style.fontVariationSettings = "'FILL' 1";
                if (activeEmoji) {
                    activeEmoji.className = 'status-emoji flex items-center justify-center w-9 h-9 rounded-full bg-primary/15 text-primary shrink-0';
                }
            });
        });

        // Form submission with loading state
        form.addEventListener('submit', () => {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Menyimpan...';
        });
    });
</script>
@endpush
@endsection
