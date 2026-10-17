@extends('layouts.app')

@section('title', 'NORD CAFE - Checkout')

@section('content')
<main class="flex-1 w-full max-w-2xl mx-auto px-margin-mobile py-unit-lg flex flex-col gap-unit-md pb-[120px]">
    <!-- Page Title + Back (edit pesanan tanpa tombol back browser) -->
    <div class="flex items-center gap-3 mb-unit-sm -ml-1">
        <a href="{{ route('customer.cart') }}" class="btn-icon-back shrink-0" aria-label="Kembali" title="Kembali"
           onclick="if(history.length>1){event.preventDefault();history.back();}">
            <span class="material-symbols-outlined text-xl">arrow_back</span>
        </a>
        <div class="flex flex-col">
            <h1 class="font-headline-lg text-headline-lg text-on-surface leading-tight tracking-tight">Checkout</h1>
            <span class="text-body-secondary text-status-unpaid">Kembali untuk ubah pesanan</span>

        </div>
    </div>

    <!-- Order Summary Card -->
    <section class="card-soft p-gutter flex flex-col gap-unit-sm">
        <h2 class="font-headline-sm text-headline-sm text-on-surface mb-2 tracking-tight">Ringkasan Pesanan</h2>

        <div id="checkout-items-list" class="flex flex-col gap-2.5 mb-2">
            <!-- Rendered by JS -->
        </div>

        <hr class="divider-fine my-1"/>

        <div class="flex justify-between items-center font-body-md text-body-md text-on-surface-variant py-0.5">
            <span>Subtotal</span>
            <span id="checkout-subtotal" class="text-on-surface">Rp0</span>
        </div>
        <div class="flex justify-between items-center font-body-md text-body-md text-on-surface-variant py-0.5">
            <span>Pajak (PB1 10%)</span>
            <span id="checkout-tax" class="text-on-surface">Rp0</span>
        </div>
        <hr class="divider-fine my-2"/>
        <div class="flex justify-between items-center font-headline-sm text-headline-sm text-on-surface">
            <span class="font-semibold">Total Bayar</span>
            <span id="checkout-total" class="font-bold text-primary tracking-tight transition-colors duration-300">Rp0</span>
        </div>
    </section>

    <!-- Payment Method Card -->
    <section class="card-soft p-gutter flex flex-col">
        <div class="mb-4">
            <h3 class="font-label-caps text-label-caps text-status-unpaid uppercase tracking-wider mb-1">Metode Pembayaran (Opsional)</h3>
            <p class="font-body-secondary text-body-secondary text-on-surface-variant">Bayar tunai / QRIS langsung di kasir saat pesanan diverifikasi.</p>
        </div>

        <!-- Payment Options -->
        <div class="flex flex-col gap-2.5">
            <!-- Option 1: Cash -->
            <label class="payment-method-row flex items-center gap-3.5 p-4 cursor-pointer rounded-xl border border-on-surface/10 hover:border-primary/30 hover:bg-primary/5 transition-all duration-300">
                <div class="radio-indicator w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center shrink-0 transition-colors"></div>
                <input type="radio" name="payment_method" value="cash" class="hidden">
                <span class="pay-icon flex items-center justify-center w-9 h-9 rounded-full bg-surface-container text-on-surface-variant shrink-0 transition-colors">
                    <span class="material-symbols-outlined text-xl">payments</span>
                </span>
                <span class="font-body-md text-body-md text-on-surface flex-1">Bayar Tunai</span>
            </label>

            <!-- Option 2: QRIS (Selected by default) -->
            <label class="payment-method-row flex items-center gap-3.5 p-4 cursor-pointer rounded-xl border border-primary/40 bg-primary/5 hover:bg-primary/10 transition-all duration-300">
                <div class="radio-indicator w-5 h-5 rounded-full border-[6px] border-primary flex items-center justify-center shrink-0 transition-colors"></div>
                <input type="radio" name="payment_method" value="qris" checked class="hidden">
                <span class="pay-icon flex items-center justify-center w-9 h-9 rounded-full bg-primary/15 text-primary shrink-0 transition-colors">
                    <span class="material-symbols-outlined text-xl">qr_code</span>
                </span>
                <span class="font-body-md text-body-md text-on-surface font-semibold flex-1">QRIS</span>
            </label>
        </div>
    </section>
</main>

<!-- Fixed Bottom Action Bar -->
<div class="fixed bottom-0 left-0 w-full p-margin-mobile bg-gradient-to-t from-background-warm via-background-warm/90 to-transparent dark:from-[#121212] dark:via-[#121212]/90 dark:to-transparent pb-8 pt-10 pointer-events-none z-40">
    <div class="max-w-2xl mx-auto w-full pointer-events-auto">
        <button id="btn-submit-order" class="btn-cta w-full text-headline-sm h-[54px] flex items-center justify-center gap-2">
            Buat Pesanan
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cart = NordCart.getItems();
        const itemsContainer = document.getElementById('checkout-items-list');

        if (!cart || cart.length === 0) {
            window.location.href = "{{ route('customer.cart') }}";
            return;
        }

        let subtotal = 0;
        itemsContainer.innerHTML = '';

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            const row = document.createElement('div');
            row.className = 'flex justify-between items-center gap-3 font-body-md text-body-md py-1 list-item-in';
            row.style.animationDelay = Math.min(index * 55, 300) + 'ms';
            row.innerHTML = `
                <span class="flex items-center gap-2 min-w-0">
                    <span class="text-on-surface truncate">${item.name}</span>
                    <span class="shrink-0 px-1.5 py-0.5 rounded-md bg-surface-container text-on-surface-variant text-[11px] font-bold">${item.quantity}x</span>
                </span>
                <span class="text-on-surface font-semibold whitespace-nowrap">Rp${itemTotal.toLocaleString('id-ID')}</span>
            `;
            itemsContainer.appendChild(row);
        });

        const tax = Math.round(subtotal * 0.10);
        const total = subtotal + tax;
        document.getElementById('checkout-subtotal').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
        document.getElementById('checkout-tax').textContent = `Rp${tax.toLocaleString('id-ID')}`;
        document.getElementById('checkout-total').textContent = `Rp${total.toLocaleString('id-ID')}`;

        // Radio toggle UI
        const rows = document.querySelectorAll('.payment-method-row');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                rows.forEach(r => {
                    r.classList.remove('bg-primary/5', 'border-primary/40', 'bg-surface-variant/10');
                    r.classList.add('border-on-surface/10');
                    r.querySelector('.radio-indicator').className = 'radio-indicator w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center shrink-0 transition-colors';
                    const icon = r.querySelector('.pay-icon');
                    if (icon) {
                        icon.classList.remove('bg-primary/15', 'text-primary');
                        icon.classList.add('bg-surface-container', 'text-on-surface-variant');
                    }
                    r.querySelector('input').checked = false;
                });
                row.classList.add('bg-primary/5', 'border-primary/40');
                row.classList.remove('border-on-surface/10');
                row.querySelector('.radio-indicator').className = 'radio-indicator w-5 h-5 rounded-full border-[6px] border-primary flex items-center justify-center shrink-0 transition-colors';
                const activeIcon = row.querySelector('.pay-icon');
                if (activeIcon) {
                    activeIcon.classList.remove('bg-surface-container', 'text-on-surface-variant');
                    activeIcon.classList.add('bg-primary/15', 'text-primary');
                }
                row.querySelector('input').checked = true;
            });
        });

        // Submit Order
        document.getElementById('btn-submit-order').addEventListener('click', async () => {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'qris';
            
            const btn = document.getElementById('btn-submit-order');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] animate-spin">progress_activity</span> Membuat Pesanan...';

            try {
                const response = await fetch("{{ route('customer.placeOrder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: cart,
                        payment_method: selectedMethod,
                        order_type: 'dine_in'
                    })
                });

                const res = await response.json();
                if (res.success) {
                    NordCart.clearCart();
                    localStorage.setItem('nord_last_order', res.order_number);
                    window.location.href = res.redirect_url;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Buat Pesanan <span class="material-symbols-outlined text-[20px]">arrow_forward</span>';
                    Swal.fire('Gagal', res.message || 'Gagal membuat pesanan.', 'error');
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = 'Buat Pesanan <span class="material-symbols-outlined text-[20px]">arrow_forward</span>';
                Swal.fire('Error', 'Terjadi gangguan koneksi.', 'error');
            }
        });
    });
</script>
@endpush
@endsection
