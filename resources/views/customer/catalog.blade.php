@extends('layouts.app')

@section('title', 'NORD CAFE - Katalog Menu')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header (Compact & touch-friendly) -->
    <div class="mb-3 md:mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-on-surface tracking-tight">Katalog Menu</h2>
            <p class="text-xs md:text-sm text-status-unpaid mt-0.5">Pilih menu favoritmu</p>
        </div>

        <!-- Search Form -->
        <form action="{{ route('customer.catalog') }}" method="GET" class="relative w-full md:w-72">
            <input type="hidden" name="filter" value="{{ $selectedFilter }}">
            @if($activeChip)<input type="hidden" name="chip" value="{{ $activeChip }}">@endif
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-status-unpaid text-lg">search</span>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari menu..."
                   class="w-full pl-9 pr-8 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-on-surface text-sm focus:outline-none focus:border-primary shadow-xs">
            @if(request('search'))
            <a href="{{ route('customer.catalog', array_filter(['filter' => $selectedFilter, 'chip' => $activeChip])) }}"
               class="absolute right-2.5 top-1/2 -translate-y-1/2 text-status-unpaid hover:text-on-surface">
                <span class="material-symbols-outlined text-base">close</span>
            </a>
            @endif
        </form>
    </div>

    @php $cardIndex = 0; @endphp

    <!-- Sticky horizontal-scroll category chips (1 row) -->
    <div id="category-chips"
         class="sticky top-16 z-30 -mx-margin-mobile px-margin-mobile py-2.5 mb-4 bg-background-warm/95 backdrop-blur-md border-b border-on-surface/5 dark:bg-[#121212]/95 dark:border-white/5">
        <div class="relative">
            <!-- Edge fade hints (mobile scroll affordance) -->
            <div class="pointer-events-none absolute left-0 top-0 bottom-0 w-6 bg-gradient-to-r from-background-warm to-transparent dark:from-[#121212] z-10 md:hidden"></div>
            <div class="pointer-events-none absolute right-0 top-0 bottom-0 w-6 bg-gradient-to-l from-background-warm to-transparent dark:from-[#121212] z-10 md:hidden"></div>

            <div id="chips-scroll"
                 class="flex gap-2 overflow-x-auto scrollbar-hide scroll-smooth snap-x snap-mandatory"
                 role="navigation"
                 aria-label="Kategori menu">
                @foreach($chips as $chip)
                    @php
                        $chipParams = array_filter([
                            'chip' => $chip['key'],
                            'search' => request('search'),
                        ]);
                        $isChipActive = $activeChip === $chip['key'];
                    @endphp
                    <a href="{{ route('customer.catalog', $chipParams) }}"
                       data-chip="{{ $chip['key'] }}"
                       class="chip-pill whitespace-nowrap px-4 py-2 rounded-full text-xs font-bold shrink-0 border transition-all duration-200 active:scale-95 snap-start {{ $isChipActive ? 'bg-primary border-primary text-on-primary shadow-sm' : 'bg-transparent border-outline/20 text-on-surface-variant hover:bg-surface-variant/20' }}">
                        {{ $chip['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Mobile: quick dropdown selector → navigates to filtered category page -->
        <div class="mt-2 md:hidden">
            <label for="chip-jump" class="sr-only">Pilih kategori menu</label>
            <select id="chip-jump"
                    onchange="if(this.value){ window.location.href='{{ route('customer.catalog') }}?chip='+encodeURIComponent(this.value); }"
                    class="w-full px-3 py-2 rounded-xl bg-surface-container-lowest border border-outline-variant/40 text-on-surface text-xs font-semibold focus:outline-none focus:border-primary">
                <option value="">Pilih kategori (cepat)…</option>
                @foreach($chips as $chip)
                    <option value="{{ $chip['key'] }}" {{ $activeChip === $chip['key'] ? 'selected' : '' }}>{{ $chip['label'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Products: only the active category (or single default grid) -->
    @if($products->isEmpty())
    <div class="py-12 text-center card-soft p-6">
        <span class="material-symbols-outlined text-4xl text-status-unpaid mb-2">search_off</span>
        <h3 class="text-base font-bold text-on-surface">Menu Tidak Ditemukan</h3>
        <p class="text-xs text-status-unpaid mt-1">Tidak ada menu yang sesuai dengan filter atau pencarian Anda.</p>
        <a href="{{ route('customer.catalog', ['filter' => 'all']) }}" class="btn-cta inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl text-xs">
            <span class="material-symbols-outlined text-sm">refresh</span> Tampilkan Semua Menu
        </a>
    </div>
    @else
    @foreach($sections as $section)
        <section id="cat-{{ $section['key'] }}" class="mb-7">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                    <h3 class="text-sm sm:text-base font-bold text-on-surface tracking-tight truncate">{{ $section['label'] }}</h3>
                </div>
                @if($section['type'] === 'makanan' || $section['type'] === 'minuman')
                <span class="text-[10px] font-bold uppercase tracking-[0.08em] text-primary/80 shrink-0">
                    {{ $section['type'] === 'makanan' ? 'Makanan' : 'Minuman' }}
                </span>
                @elseif($section['tab'])
                <span class="text-[10px] font-bold uppercase tracking-[0.08em] text-primary/80 shrink-0">
                    {{ $section['tab'] }}
                </span>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-3.5 md:gap-4">
                @foreach($section['items'] as $product)
                @php
                    $tag = strtolower($product->tags ?? '');
                    $ratingVal = floatval($product->rating ?: 4.8);
                    $fullStars = floor($ratingVal);
                    $hasHalf = ($ratingVal - $fullStars) >= 0.25;
                    $hasDiscount = str_contains($tag, 'sale') || str_contains($tag, 'promo') || str_contains($tag, 'diskon');
                    $originalPrice = $hasDiscount ? round($product->price * 1.25) : null;
                    $fallbackImg = 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80';
                    $staggerIndex = $cardIndex++;
                @endphp
                <div class="group relative flex w-full flex-col overflow-hidden rounded-2xl card-soft card-hover animate-in-soft" style="animation-delay: {{ min($staggerIndex * 40, 240) }}ms">
            <!-- Consistent 4:3 image area -->
            <div class="relative aspect-[4/3] w-full overflow-hidden bg-surface-container shrink-0">
                <a href="{{ route('customer.productDetail', $product->id) }}" class="block w-full h-full">
                    <img src="{{ $product->image_url }}"
                         alt="{{ $product->name }}"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='{{ $fallbackImg }}'"
                         class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
                </a>

                <!-- Hover overlay gradient -->
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                <!-- Compact Wishlist button -->
                <button type="button"
                        onclick="toggleWishlist(event, {{ $product->id }})"
                        aria-label="Wishlist {{ $product->name }}"
                        class="wishlist-btn-{{ $product->id }} absolute top-2.5 right-2.5 z-10 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-black/40 hover:bg-black/65 backdrop-blur-sm text-white transition-all duration-150 active:scale-85">
                    <svg class="h-3.5 w-3.5 heart-icon transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <!-- Sold out overlay -->
                @if(!$product->is_available)
                <div class="absolute inset-0 bg-black/70 backdrop-blur-[2px] flex items-center justify-center z-20 pointer-events-none">
                    <span class="px-3 py-1 rounded-full bg-black/70 border border-white/20 text-white font-bold text-[10px] sm:text-xs uppercase tracking-wider">
                        Habis
                    </span>
                </div>
                @endif
            </div>

            <!-- Content Area — clear typographic hierarchy -->
            <div class="p-3 sm:p-3.5 flex flex-1 flex-col justify-between gap-2">
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-bold uppercase tracking-[0.08em] text-status-unpaid">
                        {{ $product->category->name ?? 'Menu' }}
                    </span>
                    <a href="{{ route('customer.productDetail', $product->id) }}" class="block">
                        <h3 class="line-clamp-1 font-semibold text-on-surface text-xs sm:text-sm tracking-tight hover:text-primary transition-colors">
                            {{ $product->name }}
                        </h3>
                    </a>

                    <!-- Rating Stars -->
                    <div class="flex items-center gap-1">
                        <div class="flex items-center text-status-processing">
                            @for($i = 0; $i < 5; $i++)
                                @if($i < $fullStars)
                                    <svg class="h-3 w-3 fill-current" viewBox="0 0 24 24">
                                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                    </svg>
                                @elseif($i == $fullStars && $hasHalf)
                                    <svg class="h-3 w-3 text-status-processing" viewBox="0 0 24 24">
                                        <defs>
                                            <linearGradient id="star-half-{{ $product->id }}">
                                                <stop offset="50%" stop-color="currentColor"/>
                                                <stop offset="50%" stop-color="rgba(255,255,255,0.2)"/>
                                            </linearGradient>
                                        </defs>
                                        <path fill="url(#star-half-{{ $product->id }})" stroke="currentColor" stroke-width="1" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                    </svg>
                                @else
                                    <svg class="h-3 w-3 text-outline-variant fill-current" viewBox="0 0 24 24">
                                        <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                    </svg>
                                @endif
                            @endfor
                        </div>
                        <span class="ml-0.5 font-semibold text-on-surface-variant text-[11px]">{{ number_format($ratingVal, 1) }}</span>
                    </div>

                    <!-- Price Row -->
                    <div class="flex items-baseline flex-wrap gap-x-1.5 gap-y-0.5 mt-0.5">
                        <span class="font-bold text-primary text-sm sm:text-[15px] tracking-tight">
                            Rp{{ number_format($product->price, 0, ',', '.') }}
                        </span>
                        @if($hasDiscount && $originalPrice)
                        <span class="text-on-surface-variant/60 text-[11px] line-through">
                            Rp{{ number_format($originalPrice, 0, ',', '.') }}
                        </span>
                        <span class="badge-soft badge-rose !py-0 !px-1.5 !text-[9px]">
                            -20%
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Add to Cart Button (Touch target 44px min-height) -->
                <div class="mt-1">
                    @if($product->is_available)
                    <button type="button"
                            id="add-btn-{{ $product->id }}"
                            onclick="animatedAddToCart(this, {{ json_encode($product) }})"
                            class="btn-cta w-full h-11 min-h-[44px] rounded-xl flex items-center justify-center gap-1.5 text-xs sm:text-sm select-none">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Add to Cart</span>
                    </button>
                    @else
                    <button disabled
                            class="w-full h-11 min-h-[44px] rounded-xl border border-on-surface/10 bg-surface-container text-status-unpaid text-xs sm:text-sm font-semibold flex items-center justify-center gap-1.5 cursor-not-allowed">
                        <span>Menu Habis</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>
                @endforeach
            </div>
        </section>
    @endforeach
    @endif
</div>

@push('scripts')
<script>
    function animatedAddToCart(btn, product) {
        NordCart.addItem(product, 1, {
            ice: 'normal',
            sugar: 'normal'
        }, '');

        const originalContent = btn.innerHTML;
        const originalClasses = btn.className;

        btn.disabled = true;
        btn.className = "btn-cta-success w-full h-11 min-h-[44px] rounded-xl flex items-center justify-center gap-1.5 text-xs sm:text-sm select-none";
        btn.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Added</span>
        `;

        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.className = originalClasses;
            btn.disabled = false;
        }, 1600);
    }

    function toggleWishlist(e, productId) {
        e.preventDefault();
        e.stopPropagation();
        const btn = document.querySelector('.wishlist-btn-' + productId);
        if (!btn) return;
        const svg = btn.querySelector('.heart-icon');
        const isWishlisted = svg.classList.contains('text-red-500');

        if (isWishlisted) {
            svg.classList.remove('text-red-500');
            svg.setAttribute('fill', 'none');
        } else {
            svg.classList.add('text-red-500');
            svg.setAttribute('fill', 'currentColor');
        }
    }
</script>
@endpush
@endsection
