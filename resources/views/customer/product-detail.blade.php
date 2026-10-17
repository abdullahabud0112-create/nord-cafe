@extends('layouts.app')

@section('title', 'NORD CAFE - Detail Produk')

@section('content')
<div class="w-full max-w-2xl mx-auto flex flex-col pb-24">
    <!-- Header: Back arrow + centered title -->
    <header class="flex justify-between items-center w-full px-margin-mobile h-14 shrink-0 card-soft rounded-2xl my-3">
        <a href="{{ route('customer.catalog') }}" aria-label="Kembali" title="Kembali"
           class="text-on-surface hover:text-primary transition-colors p-2 -ml-2"
           onclick="if(history.length>1){event.preventDefault();history.back();}">
            <span class="material-symbols-outlined text-2xl">arrow_back</span>
        </a>
        <h1 class="text-headline-sm font-headline-sm font-bold text-on-surface tracking-tight">Detail Produk</h1>
        <div class="w-8"></div>
    </header>

    <!-- Content Area -->
    <main class="flex-1 flex flex-col gap-6">
        <!-- Large real photo -->
        <div class="w-full aspect-[4/3] sm:aspect-[16/10] relative shrink-0 bg-surface-container rounded-2xl overflow-hidden card-soft border-0">
            <img class="w-full h-full object-cover"
                 src="{{ $product->image_url }}"
                 alt="{{ $product->name }}"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80'"/>
        </div>

        <!-- Product Details Container -->
        <div class="px-1 flex-1 flex flex-col justify-between gap-4">
            <div>
                <!-- Title & Price -->
                <div class="mb-3">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <div class="flex items-center text-status-processing">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24"><path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                            @endfor
                        </div>
                        <span class="text-xs font-semibold text-on-surface-variant">{{ number_format(floatval($product->rating ?: 4.8), 1) }}</span>
                        @if($product->category)
                        <span class="w-1 h-1 rounded-full bg-outline-variant"></span>
                        <span class="text-[10px] font-bold uppercase tracking-[0.08em] text-status-unpaid">{{ $product->category->name }}</span>
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold text-on-surface tracking-tight mb-1.5">{{ $product->name }}</h2>
                    <p class="text-xl font-bold text-primary tracking-tight">
                        Rp{{ number_format($product->price, 0, ',', '.') }}
                    </p>
                </div>

                <hr class="divider-soft my-4"/>

                <!-- Description -->
                <p class="text-body-md text-on-surface-variant leading-relaxed">
                    {{ $product->description ?: 'Espresso fresh brew dipadukan susu segar dan bahan pilihan berkualitas tinggi khas NORD CAFE.' }}
                </p>
            </div>

            <!-- Quantity Stepper Row -->
            <div class="flex items-center justify-between my-2 pt-4 border-t border-on-surface/10">
                <span class="text-body-lg font-bold text-on-surface">Jumlah</span>
                <div class="flex items-center bg-surface-container-low rounded-full border border-on-surface/10 p-1 shadow-xs">
                    <button id="btn-decrease" aria-label="Decrease quantity" class="w-8 h-8 flex items-center justify-center text-on-surface hover:bg-primary/10 rounded-full transition-colors">
                        <span class="material-symbols-outlined">remove</span>
                    </button>
                    <span id="qty-display" class="w-8 text-center text-body-lg font-bold">1</span>
                    <button id="btn-increase" aria-label="Increase quantity" class="w-8 h-8 flex items-center justify-center text-on-surface hover:bg-primary/10 rounded-full transition-colors">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Fixed Bottom Action Bar -->
    <div class="fixed bottom-0 left-0 w-full px-margin-mobile py-4 bg-surface-container-lowest/90 dark:bg-[#121212]/85 backdrop-blur-xl border-t border-on-surface/10 dark:border-white/10 pb-6 z-40">
        <div class="max-w-2xl mx-auto">
            @if($product->is_available)
            <button id="btn-add-to-cart" class="btn-cta w-full py-3.5 text-body-lg flex items-center justify-center gap-2">
                <span class="material-symbols-outlined">shopping_cart</span>
                Tambahkan ke Keranjang
            </button>
            @else
            <button disabled class="w-full bg-surface-variant text-status-unpaid py-3.5 rounded-xl text-body-lg font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                Menu Habis
            </button>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let quantity = 1;
        const product = @json($product);
        const qtyDisplay = document.getElementById('qty-display');
        const btnDecrease = document.getElementById('btn-decrease');
        const btnIncrease = document.getElementById('btn-increase');
        const btnAddToCart = document.getElementById('btn-add-to-cart');

        function popQty() {
            qtyDisplay.classList.remove('qty-pop');
            void qtyDisplay.offsetWidth;
            qtyDisplay.classList.add('qty-pop');
        }

        btnDecrease.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                qtyDisplay.textContent = quantity;
                popQty();
            }
        });

        btnIncrease.addEventListener('click', () => {
            quantity++;
            qtyDisplay.textContent = quantity;
            popQty();
        });

        if (btnAddToCart) {
            btnAddToCart.addEventListener('click', () => {
                NordCart.addItem(product, quantity, {
                    ice: 'normal',
                    sugar: 'normal'
                }, '');
            });
        }
    });
</script>
@endpush
@endsection
