@extends('layouts.app')

@section('title', 'NORD CAFE - Keranjang Pesanan')

@section('content')
<main class="flex-1 w-full max-w-7xl mx-auto px-margin-mobile py-unit-xl">
    <h1 class="text-headline-sm font-headline-sm font-bold text-on-surface mb-unit-lg tracking-tight">Keranjang Pesanan</h1>

    <!-- Cart Empty State -->
    <div id="cart-empty" class="hidden card-soft p-unit-xl text-center mb-unit-xl max-w-xl mx-auto">
        <span class="material-symbols-outlined text-5xl text-status-unpaid mb-2">shopping_basket</span>
        <h2 class="text-headline-sm font-bold text-on-surface">Keranjang Masih Kosong</h2>
        <p class="text-body-secondary text-status-unpaid mt-1 mb-6">Pilih menu favoritmu dan tambahkan ke keranjang.</p>
        <a href="{{ route('customer.catalog') }}" class="btn-cta inline-flex items-center gap-2 py-3 px-6 text-body-md">
            <span class="material-symbols-outlined text-lg">restaurant_menu</span>
            Lihat Katalog Menu
        </a>
    </div>

    <!-- Cart Content State -->
    <div id="cart-content" class="flex flex-col gap-unit-md max-w-3xl mx-auto">
        <!-- Isi Pesanan: dibagi 2 grup — Minuman → Makanan -->
        <div class="flex flex-col gap-unit-xl mb-unit-xl">
            <section id="group-minuman" class="hidden">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between px-1">
                        <h2 class="flex items-center gap-1.5 text-label-caps font-label-caps uppercase tracking-[0.14em] text-on-surface-variant">
                            <span class="material-symbols-outlined text-base text-primary">local_cafe</span>
                            Minuman
                        </h2>
                        <span id="group-minuman-subtotal" class="text-[11px] font-bold text-primary tracking-tight">Rp0</span>
                    </div>
                    <div id="group-minuman-items" class="flex flex-col gap-unit-md">
                        <!-- Dynamic items rendered via JavaScript -->
                    </div>
                </div>
            </section>

            <section id="group-makanan" class="hidden">
                <div class="flex flex-col gap-2.5">
                    <div class="flex items-center justify-between px-1">
                        <h2 class="flex items-center gap-1.5 text-label-caps font-label-caps uppercase tracking-[0.14em] text-on-surface-variant">
                            <span class="material-symbols-outlined text-base text-primary">lunch_dining</span>
                            Makanan
                        </h2>
                        <span id="group-makanan-subtotal" class="text-[11px] font-bold text-primary tracking-tight">Rp0</span>
                    </div>
                    <div id="group-makanan-items" class="flex flex-col gap-unit-md">
                        <!-- Dynamic items rendered via JavaScript -->
                    </div>
                </div>
            </section>
        </div>

        <!-- Summary Card -->
        <div class="card-soft p-gutter mb-unit-xl">
            <div class="flex justify-between items-center mb-unit-sm">
                <span class="text-on-surface-variant text-body-secondary font-body-secondary">Subtotal</span>
                <span id="summary-subtotal" class="text-on-surface text-body-secondary font-semibold">Rp0</span>
            </div>
            <div class="flex justify-between items-center mb-unit-md">
                <span class="text-on-surface-variant text-body-secondary font-body-secondary">Pajak (PB1)</span>
                <span id="summary-tax" class="text-on-surface-variant text-body-secondary font-body-secondary">-</span>
            </div>
            <hr class="divider-fine mb-unit-md"/>
            <div class="flex justify-between items-center">
                <span class="font-bold text-on-surface text-headline-sm font-headline-sm tracking-tight">Total</span>
                <span id="summary-total" class="font-bold text-primary text-headline-sm font-headline-sm tracking-tight">Rp0</span>
            </div>
        </div>

        <!-- Actions: Continue Shopping (secondary) + Checkout (primary) -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('customer.catalog') }}" class="btn-outline w-full sm:w-auto py-4 px-6 flex items-center justify-center gap-2 text-body-lg font-body-lg">
                <span class="material-symbols-outlined text-xl">restaurant_menu</span>
                <span>Tambah Pesanan</span>
            </a>
            {{-- href diarahkan JS ke /checkout/minuman atau /checkout/makanan
                sesuai item yang terakhir ditambahkan ke keranjang --}}
            <a id="btn-checkout" href="{{ route('customer.checkoutDrink') }}" class="btn-cta w-full sm:flex-1 py-4 px-6 flex justify-between items-center text-body-lg font-body-lg">
                <span>Checkout</span>
                <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
            </a>
        </div>
    </div>
</main>

@push('scripts')
<script>
    /* Entrance animation runs only on first paint — re-render on qty change
       must not replay it or the whole list jumps every tap */
    let cartFirstPaint = true;

    const PRODUCT_TYPES = @json($productTypes);
    const DRINK_CHECKOUT_URL = @json(route('customer.checkoutDrink'));
    const FOOD_CHECKOUT_URL = @json(route('customer.checkoutFood'));

    /* Backfill carts saved before category_type existed, then persist. */
    function resolveTypes(cart) {
        let patched = false;
        cart.forEach(item => {
            if (item.category_type !== 'minuman' && item.category_type !== 'makanan') {
                item.category_type = PRODUCT_TYPES[String(item.product_id)] === 'minuman' ? 'minuman' : 'makanan';
                patched = true;
            }
        });
        if (patched) NordCart.saveItems(cart);
        return cart;
    }

    function itemCard(entry, groupIndex) {
        const item = entry.item;
        const itemTotal = item.price * item.quantity;

        const itemEl = document.createElement('div');
        itemEl.className = 'card-soft card-hover p-gutter flex justify-between items-start gap-3' + (cartFirstPaint ? ' list-item-in' : '');
        if (cartFirstPaint) itemEl.style.animationDelay = Math.min(groupIndex * 50, 250) + 'ms';

        const optionsArr = [];
        if (item.options && item.options.ice) optionsArr.push(item.options.ice);
        if (item.options && item.options.sugar) optionsArr.push(item.options.sugar);
        const optionsStr = optionsArr.length > 0 ? optionsArr.join(', ') : 'Reguler';
        const itemImg = item.image_url || 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=200&q=80';

        itemEl.innerHTML = `
            <img src="${itemImg}" alt="${item.name}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover bg-surface-container shrink-0" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=200&q=80'">
            <div class="flex flex-col flex-1 min-w-0">
                <span class="font-bold text-on-surface text-body-lg font-body-lg truncate">${item.name}</span>
                <span class="text-status-unpaid text-body-secondary font-body-secondary mt-0.5">${item.quantity}x, ${optionsStr}</span>
                <div class="flex items-center gap-3 mt-2.5">
                    <div class="flex items-center bg-surface-container rounded-full p-0.5 border border-on-surface/10">
                        <button onclick="updateQty(${entry.index}, ${item.quantity - 1})" aria-label="Kurangi" class="w-7 h-7 flex items-center justify-center text-on-surface hover:bg-primary/10 rounded-full transition-colors">
                            <span class="material-symbols-outlined text-sm">remove</span>
                        </button>
                        <span class="w-6 text-center text-xs font-bold text-on-surface">${item.quantity}</span>
                        <button onclick="updateQty(${entry.index}, ${item.quantity + 1})" aria-label="Tambah" class="w-7 h-7 flex items-center justify-center text-on-surface hover:bg-primary/10 rounded-full transition-colors">
                            <span class="material-symbols-outlined text-sm">add</span>
                        </button>
                    </div>
                    <button onclick="updateQty(${entry.index}, 0)" class="text-error text-xs font-semibold hover:underline flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">delete</span> Hapus
                    </button>
                </div>
            </div>
            <span class="font-bold text-primary text-body-lg font-body-lg whitespace-nowrap text-right">Rp${itemTotal.toLocaleString('id-ID')}</span>
        `;
        return itemEl;
    }

    function renderGroup(itemsId, subtotalId, entries) {
        const container = document.getElementById(itemsId);
        container.innerHTML = '';
        entries.forEach((entry, i) => container.appendChild(itemCard(entry, i)));

        const subtotal = entries.reduce((s, e) => s + e.item.price * e.item.quantity, 0);
        document.getElementById(subtotalId).textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
    }

    function renderCart() {
        const cart = NordCart.getItems();
        const emptyState = document.getElementById('cart-empty');
        const contentState = document.getElementById('cart-content');

        if (!cart || cart.length === 0) {
            emptyState.classList.remove('hidden');
            contentState.classList.add('hidden');
            cartFirstPaint = false;
            return;
        }

        emptyState.classList.add('hidden');
        contentState.classList.remove('hidden');

        resolveTypes(cart);

        /* Split isi pesanan into 2 groups; entry.index = index in the full cart
           so updateQty() keeps addressing the right item after grouping. */
        const drinks = [];
        const foods = [];
        cart.forEach((item, index) => {
            const entry = { item, index };
            (item.category_type === 'minuman' ? drinks : foods).push(entry);
        });

        document.getElementById('group-minuman').classList.toggle('hidden', drinks.length === 0);
        document.getElementById('group-makanan').classList.toggle('hidden', foods.length === 0);

        renderGroup('group-minuman-items', 'group-minuman-subtotal', drinks);
        renderGroup('group-makanan-items', 'group-makanan-subtotal', foods);

        const subtotalDrinks = drinks.reduce((s, e) => s + e.item.price * e.item.quantity, 0);
        const subtotalFoods = foods.reduce((s, e) => s + e.item.price * e.item.quantity, 0);
        const subtotal = subtotalDrinks + subtotalFoods;

        document.getElementById('summary-subtotal').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
        document.getElementById('summary-tax').textContent = `-`;
        document.getElementById('summary-total').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;

        /* Smart checkout entry: pesanan terakhir makanan → halaman konfirmasi
           makanan; minuman → halaman konfirmasi minuman. */
        const lastType = cart.length ? cart[cart.length - 1].category_type : 'minuman';
        const checkoutBtn = document.getElementById('btn-checkout');
        if (checkoutBtn) {
            checkoutBtn.href = lastType === 'minuman' ? DRINK_CHECKOUT_URL : FOOD_CHECKOUT_URL;
        }

        cartFirstPaint = false;
    }

    function updateQty(index, newQty) {
        NordCart.updateQuantity(index, newQty);
        renderCart();
    }

    document.addEventListener('DOMContentLoaded', renderCart);
</script>
@endpush
@endsection
