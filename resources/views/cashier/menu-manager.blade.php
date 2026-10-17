@extends('layouts.app')

@section('title', 'NORD CAFE - Kelola Katalog Menu')

@section('content')
<main class="max-w-7xl mx-auto pb-16">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-headline-lg font-headline-lg font-bold text-on-surface">Kelola Katalog Menu</h1>
            <p class="text-body-secondary font-body-secondary text-status-unpaid">Atur ketersediaan (tersedia/habis), ubah harga, atau tambah menu baru.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <button onclick="openAddModal()" class="btn-cta px-4 py-2 text-body-md flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Menu Baru
            </button>
            <a href="{{ route('customer.catalog') }}" target="_blank" class="btn-soft px-3.5 py-2 text-body-md flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">open_in_new</span> Lihat Katalog
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card-soft p-4 mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Main Type Selector -->
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cashier.menuManager', ['type' => 'all', 'search' => request('search')]) }}" 
               class="px-3.5 py-1.5 rounded-xl text-body-md font-semibold transition-colors {{ $selectedType === 'all' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-variant/20' }}">
                Semua
            </a>
            <a href="{{ route('cashier.menuManager', ['type' => 'minuman', 'search' => request('search')]) }}" 
               class="px-3.5 py-1.5 rounded-xl text-body-md font-semibold transition-colors {{ $selectedType === 'minuman' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-variant/20' }}">
                Minuman
            </a>
            <a href="{{ route('cashier.menuManager', ['type' => 'makanan', 'search' => request('search')]) }}" 
               class="px-3.5 py-1.5 rounded-xl text-body-md font-semibold transition-colors {{ $selectedType === 'makanan' ? 'bg-primary text-on-primary' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-variant/20' }}">
                Makanan
            </a>
        </div>

        <!-- Search Form -->
        <form action="{{ route('cashier.menuManager') }}" method="GET" class="relative min-w-[240px]">
            <input type="hidden" name="type" value="{{ $selectedType }}">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-status-unpaid text-lg">search</span>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Cari nama menu..." 
                   class="w-full pl-9 pr-3 py-1.5 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
        </form>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="mb-4 p-3 rounded-xl bg-status-completed/10 border border-status-completed/30 text-status-completed text-body-md flex items-center gap-2">
        <span class="material-symbols-outlined text-sm">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Menu Table -->
    <div class="card-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-body-md">
                <thead class="bg-surface-container-low text-on-surface-variant font-semibold border-b border-on-surface/10">
                    <tr>
                        <th class="py-3.5 px-4 text-[11px] uppercase tracking-wider">Menu</th>
                        <th class="py-3.5 px-4 text-[11px] uppercase tracking-wider">Sub-Kategori</th>
                        <th class="py-3.5 px-4 text-[11px] uppercase tracking-wider">Harga</th>
                        <th class="py-3.5 px-4 text-[11px] uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-[11px] uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-on-surface/5">
                    @forelse($products as $prod)
                    <tr class="hover:bg-surface-container-low/60 dark:hover:bg-white/5 transition-colors duration-200">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="w-10 h-10 rounded-lg object-cover bg-surface-variant shrink-0" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=120&q=80'">
                                <div>
                                    <div class="font-bold text-on-surface">{{ $prod->name }}</div>
                                    @if($prod->tags)
                                    <span class="text-label-caps text-status-unpaid">{{ $prod->tags }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-label-caps font-semibold bg-surface-container text-on-surface-variant border border-on-surface/5">
                                {{ $prod->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-bold text-primary text-body-lg">Rp{{ number_format($prod->price, 0, ',', '.') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <form action="{{ route('cashier.toggleAvailability', $prod->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold cursor-pointer transition-all {{ $prod->is_available ? 'status-pill pill-done !py-1' : 'status-pill pill-unpaid !py-1' }}">
                                    <span class="pill-dot"></span>
                                    {{ $prod->is_available ? 'Tersedia' : 'Habis' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <button onclick="openEditModal({{ json_encode($prod) }})" class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors" title="Edit Menu">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <form action="{{ route('cashier.deleteProduct', $prod->id) }}" method="POST" onsubmit="return confirm('Hapus menu {{ $prod->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-status-unpaid hover:text-error hover:bg-error-container/40 transition-colors" title="Hapus Menu">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-status-unpaid">Tidak ada item menu ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-on-surface/10 bg-surface-container-low/60 dark:bg-white/5">
            {{ $products->links() }}
        </div>
    </div>
</main>

<!-- Modal Tambah Menu Baru -->
<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/20 mb-4">
            <h3 class="text-headline-sm font-bold text-on-surface">Tambah Item Menu Baru</h3>
            <button onclick="closeAddModal()" class="text-status-unpaid hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form action="{{ route('cashier.storeProduct') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-body-secondary font-semibold text-on-surface mb-1">Nama Menu</label>
                <input type="text" name="name" required placeholder="Contoh: Kopi Aren Spesial" class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-body-secondary font-semibold text-on-surface mb-1">Harga (Rupiah)</label>
                    <input type="number" name="price" required placeholder="23000" class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-body-secondary font-semibold text-on-surface mb-1">Sub-Kategori</label>
                    <select name="category_id" required class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-body-secondary font-semibold text-on-surface mb-1">Tags / Label (Opsional)</label>
                <input type="text" name="tags" placeholder="Bestseller, Signature" class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
            </div>

            <div>
                <label class="block text-body-secondary font-semibold text-on-surface mb-1">URL Foto (Opsional)</label>
                <input type="url" name="image_url" placeholder="https://..." class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
            </div>

            <div class="pt-3 flex gap-2">
                <button type="button" onclick="closeAddModal()" class="btn-soft flex-1 py-2.5 text-body-md">Batal</button>
                <button type="submit" class="btn-cta flex-1 py-2.5 text-body-md">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Menu -->
<div id="editModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/20 mb-4">
            <h3 class="text-headline-sm font-bold text-on-surface">Edit Item Menu</h3>
            <button onclick="closeEditModal()" class="text-status-unpaid hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="editForm" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-body-secondary font-semibold text-on-surface mb-1">Nama Menu</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-body-secondary font-semibold text-on-surface mb-1">Harga (Rupiah)</label>
                    <input type="number" id="edit_price" name="price" required class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-body-secondary font-semibold text-on-surface mb-1">Sub-Kategori</label>
                    <select id="edit_category_id" name="category_id" required class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-body-secondary font-semibold text-on-surface mb-1">Tags / Highlight</label>
                <input type="text" id="edit_tags" name="tags" class="w-full px-3 py-2 rounded-xl bg-surface-container-low border border-outline-variant/30 text-body-md focus:outline-none focus:border-primary">
            </div>

            <div class="pt-3 flex gap-2">
                <button type="button" onclick="closeEditModal()" class="btn-soft flex-1 py-2.5 text-body-md">Batal</button>
                <button type="submit" class="btn-cta flex-1 py-2.5 text-body-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    function openEditModal(prod) {
        document.getElementById('editForm').action = `/cashier/menu/${prod.id}/update`;
        document.getElementById('edit_name').value = prod.name;
        document.getElementById('edit_price').value = parseInt(prod.price);
        document.getElementById('edit_category_id').value = prod.category_id;
        document.getElementById('edit_tags').value = prod.tags || '';
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
