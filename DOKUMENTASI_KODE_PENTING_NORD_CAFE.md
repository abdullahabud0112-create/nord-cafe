# DOKUMENTASI KODE PENTING NORD CAFE

Dokumen ini berisi analisis dan dokumentasi kode penting dari project Nord Cafe — sebuah sistem manajemen restoran/cafe berbasis Laravel 12 dengan dua alur pengguna: **Customer Self-Order** dan **Cashier POS**.

---

## 1. GAMBARAN UMUM ARSITEKTUR APLIKASI

Nord Cafe adalah aplikasi web berbasis Laravel 12 (PHP 8.2+) yang dibangun dengan arsitektur **MVC (Model-View-Controller)**. Aplikasi ini berjalan sebagai container Docker pada Vercel (`vercel.json` mengonfigurasi runtime container dengan entrypoint `Dockerfile`).

### Struktur Teknis

| Komponen | Detail |
|----------|--------|
| Framework | Laravel 12 |
| PHP | ^8.2 |
| Database | SQLite (default), dapat dikonfigurasi ke MySQL/PostgreSQL |
| Templating | Blade |
| CSS Framework | Tailwind CSS (via CDN) |
| JavaScript | Vanilla JS (Tailwind config inline, QRCode.js, html5-qrcode, SweetAlert2) |
| Komunikasi Real-time | Laravel Event Broadcasting (`ShouldBroadcastNow`) pada channel `orders` |
| Session | Database (default), direkomendasikan cookie untuk Vercel container |
| CSRF Protection | Laravel built-in CSRF token (`@csrf` di Blade, `X-CSRF-TOKEN` header di fetch) |
| Deployment | Vercel Services (container runtime) |

### Dua Alur Pengguna

1. **Customer Self-Order** — pelanggan bisa melihat katalog menu, menambahkan item ke keranjang (disimpan di `localStorage`), melakukan checkout, menerima tiket QR, dan melacak status pesanan secara real-time.
2. **Cashier POS** — kasir bisa melihat pesanan masuk, memindai barcode/QR, mengonfirmasi pembayaran, mengupdate status pesanan (dibayar → diproses → selesai), dan mengelola katalog menu (CRUD produk & kategori).

---

## 2. DAFTAR FITUR

| No | Fitur | Lokasi Utama |
|----|-------|--------------|
| 1 | Katalog Menu Pelanggan | `CustomerController@catalog`, `resources/views/customer/catalog.blade.php` |
| 2 | Detail Produk | `CustomerController@productDetail`, `resources/views/customer/product-detail.blade.php` |
| 3 | Keranjang Belanja (LocalStorage) | `resources/views/layouts/app.blade.php` (script `NordCart`), `resources/views/customer/cart.blade.php` |
| 4 | Checkout & Pemesanan | `CustomerController@checkout`, `CustomerController@placeOrder`, `resources/views/customer/checkout.blade.php` |
| 5 | CSRF Protection | Laravel built-in, `@csrf` di Blade, `X-CSRF-TOKEN` di JavaScript |
| 6 | Tiket QR Pesanan | `CustomerController@ticket`, `resources/views/customer/ticket.blade.php` |
| 7 | Pelacakan Status Pesanan | `CustomerController@trackStatus`, `CustomerController@getOrderStatusApi`, `resources/views/customer/status-tracking.blade.php` |
| 8 | Cashier Incoming Orders | `CashierController@incomingOrders`, `resources/views/cashier/incoming-orders.blade.php` |
| 9 | Scan Barcode / QR | `CashierController@scanBarcode`, `CashierController@searchBarcode`, `resources/views/cashier/scan-barcode.blade.php` |
| 10 | Konfirmasi Pembayaran | `CashierController@confirmPayment` |
| 11 | Update Status Pesanan | `CashierController@editStatus`, `CashierController@updateStatus` |
| 12 | CRUD Menu (Produk & Kategori) | `CashierController@menuManager`, `@storeProduct`, `@updateProduct`, `@toggleAvailability`, `@deleteProduct` |
| 13 | Authentication & Authorization (Role) | `app/Models/User.php` (`hasCashierAccess`), `app/Http/Middleware/EnsureCashierRole.php` |
| 14 | Order Status Event Broadcasting | `app/Events/OrderStatusUpdated.php` |
| 15 | Database Schema & Migration | `database/migrations/` |
| 16 | Seeding Data Awal | `database/seeders/NordCafeSeeder.php` |

---

## 3. PENJELASAN SETIAP FITUR

---

### FITUR 1: Katalog Menu Pelanggan

**Nama dan Fungsi:** Menampilkan daftar menu makanan dan minuman yang tersedia, dengan fitur filter berdasarkan kategori, pencarian, dan tampilan produk dalam kartu dengan gambar, rating, dan harga.

**Alur kerja:**
1. Pengunjung membuka halaman `/` (route `customer.catalog`).
2. `CustomerController@catalog` mengambil produk dari database dengan relasi kategori, difilter oleh ketersediaan (`is_available`), opsi kategori/chip, dan pencarian.
3. View `catalog.blade.php` merender produk dalam grid kartu dengan tombol "Add to Cart".

**Kode penting:**

File: `routes/web.php` (baris 8)
```php
Route::get('/', [CustomerController::class, 'catalog'])->name('customer.catalog');
```

File: `app/Http/Controllers/CustomerController.php` (baris 15-128)
```php
public function catalog(Request $request)
{
    $selectedFilter = $request->get('filter', $request->get('type', 'all'));
    $activeChip = $request->get('chip');

    $chips = [
        ['key' => 'steak', 'label' => 'Steak', 'slugs' => ['premium-steak', 'steak-original', 'steak-crispy'], 'type' => 'makanan'],
        ['key' => 'basic-coffee', 'label' => 'Basic Coffee', 'slugs' => ['basic-coffee'], 'type' => 'minuman'],
        // ... lebih banyak chip kategori
    ];

    $query = Product::with('category')->where('is_available', true);

    if ($activeChipDef) {
        $query->whereHas('category', function ($q) use ($activeChipDef) {
            $q->whereIn('slug', $activeChipDef['slugs']);
        });
    } else {
        // Legacy filter params dan kategori dari request
        if ($request->filled('category') && $request->category !== 'all') {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
    }

    if ($request->filled('search')) {
        $search = trim($request->search);
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('tags', 'like', "%{$search}%");
        });
    }

    $products = $query->orderBy('category_id')->get();
    $categories = Category::where('is_active', true)->get();

    // ... pembuatan sections untuk tampilan view

    return view('customer.catalog', compact('products', 'categories', 'selectedFilter', 'chips', 'sections', 'activeChip'));
}
```

**Penjelasan kode:**
- `Product::with('category')->where('is_available', true)` — memuat produk dengan relasi kategori dan hanya yang tersedia.
- `whereHas('category', ...)` — memfilter produk berdasarkan slug kategori atau tipe (makanan/minuman).
- Pencarian menggunakan `like` pada kolom `name` dan `tags`.
- Hasil dikirim ke view dengan variabel `products`, `categories`, `chips`, `sections`, dan `activeChip`.

**Hubungan komponen:**
- Route `customer.catalog` → `CustomerController@catalog` → Model `Product` + `Category` → View `customer.catalog`

---

### FITUR 2: Detail Produk

**Nama dan Fungsi:** Menampilkan detail lengkap satu produk termasuk gambar besar, harga, kategori, rating, dan tombol tambah ke keranjang dengan quantity stepper.

**Alur kerja:**
1. Pengguna klik produk dari katalog.
2. Route `/product/{id}` memanggil `CustomerController@productDetail`.
3. Controller mengambil produk berdasarkan ID dengan relasi kategori dan produk terkait.
4. View menampilkan detail dan memungkinkan pengguna menentukan quantity sebelum menambah ke keranjang.

**Kode penting:**

File: `routes/web.php` (baris 19)
```php
Route::get('/product/{id}', [CustomerController::class, 'productDetail'])->name('customer.productDetail');
```

File: `app/Http/Controllers/CustomerController.php` (baris 131-140)
```php
public function productDetail($id)
{
    $product = Product::with('category')->findOrFail($id);
    $relatedProducts = Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->take(3)
        ->get();

    return view('customer.product-detail', compact('product', 'relatedProducts'));
}
```

**Penjelasan kode:**
- `findOrFail($id)` — akan menghasilkan 404 jika produk tidak ditemukan.
- `relatedProducts` — mengambil hingga 3 produk dari kategori yang sama untuk rekomendasi.

**Hubungan komponen:**
- Route → `CustomerController@productDetail` → Model `Product` (relasi `category`) → View `customer.product-detail`

---

### FITUR 3: Keranjang Belanja (LocalStorage)

**Nama dan Fungsi:** Menyimpan item yang dipilih pelanggan di browser (`localStorage`) tanpa perlu database, memungkinkan penambahan, pengurangan quantity, dan penghapusan item sebelum checkout.

**Alur kerja:**
1. Pengguna klik "Add to Cart" pada katalog atau detail produk.
2. JavaScript `NordCart.addItem()` menyimpan item ke `localStorage` dengan kunci `nord_cart`.
3. Badge keranjang di header diperbarui secara otomatis.
4. Pada halaman keranjang, item dirender dari `localStorage` dengan subtotal dan total.

**Kode penting:**

File: `resources/views/layouts/app.blade.php` (baris 760-835)
```javascript
const NordCart = {
    getItems() {
        return JSON.parse(localStorage.getItem('nord_cart') || '[]');
    },
    saveItems(items) {
        localStorage.setItem('nord_cart', JSON.stringify(items));
        this.updateBadge();
    },
    addItem(product, quantity = 1, options = {}, notes = '') {
        let cart = this.getItems();
        const existingIndex = cart.findIndex(item =>
            item.product_id === product.id &&
            JSON.stringify(item.options) === JSON.stringify(options)
        );

        if (existingIndex > -1) {
            cart[existingIndex].quantity += quantity;
        } else {
            cart.push({
                product_id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                image_url: product.image_url,
                quantity: quantity,
                options: options,
                notes: notes
            });
        }

        this.saveItems(cart);
        Swal.fire({ /* toast notifikasi */ });
    },
    updateQuantity(index, newQty) {
        let cart = this.getItems();
        if (newQty <= 0) {
            cart.splice(index, 1);
        } else {
            cart[index].quantity = newQty;
        }
        this.saveItems(cart);
    },
    clearCart() {
        localStorage.removeItem('nord_cart');
        this.updateBadge();
    },
    updateBadge() {
        const cart = this.getItems();
        const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        // ... update badge DOM
    }
};
```

**Penjelasan kode:**
- `getItems()` / `saveItems()` — baca dan tulis cart ke `localStorage`.
- `addItem()` — menambah produk baru atau increment quantity jika sudah ada dengan opsi yang sama.
- `updateQuantity()` — mengubah quantity atau menghapus jika <= 0.
- `clearCart()` — mengosongkan cart setelah pesanan berhasil dibuat.
- `updateBadge()` — memperbarui angka badge keranjang di navigasi.

**Hubungan komponen:**
- View `layouts.app` (script global) → `localStorage` (browser) → View `customer.cart` dan `customer.checkout`

---

### FITUR 4: Checkout & Pemesanan

**Nama dan Fungsi:** Halaman checkout untuk meninjau ringkasan pesanan, memilih metode pembayaran (tunai/QRIS), dan membuat pesanan ke backend.

**Alur kerja:**
1. Pengguna membuka halaman `/checkout`.
2. JavaScript memeriksa cart dari `localStorage`; jika kosong, redirect ke keranjang.
3. Pengguna klik "Buat Pesanan" → JavaScript mengirim `POST` ke `/order/place` dengan data items, payment_method, dan order_type.
4. Backend `CustomerController@placeOrder` membuat Order dan OrderItem di database, lalu return `order_number` dan `redirect_url`.
5. Frontend redirect ke halaman tiket (`/ticket/{orderNumber}`).

**Kode penting:**

File: `routes/web.php` (baris 22)
```php
Route::post('/order/place', [CustomerController::class, 'placeOrder'])->name('customer.placeOrder');
```

File: `app/Http/Controllers/CustomerController.php` (baris 155-210)
```php
public function placeOrder(Request $request)
{
    $request->validate([
        'items' => 'required|array|min:1',
    ]);

    $latestId = Order::max('id') ?? 0;
    $orderSeq = sprintf('%03d', ($latestId + 1) % 1000);
    $orderNumber = 'ORD-' . date('ymd') . '-' . $orderSeq;

    $subtotal = 0;
    foreach ($request->items as $item) {
        $unitPrice = $item['unit_price'] ?? $item['price'] ?? 0;
        $subtotal += ($unitPrice * $item['quantity']);
    }

    $tax = (int) round($subtotal * 0.10);
    $total = $subtotal + $tax;

    $order = Order::create([
        'order_number' => $orderNumber,
        'customer_name' => $request->customer_name ?: 'Pelanggan',
        'table_number' => $request->table_number ?: 'Meja 04',
        'order_type' => $request->order_type ?: 'dine_in',
        'status' => 'unpaid',
        'payment_status' => 'unpaid',
        'payment_method' => $request->payment_method ?: 'qris',
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total,
        'barcode_hash' => $orderNumber,
        'notes' => $request->notes ?? null,
    ]);

    foreach ($request->items as $item) {
        $unitPrice = $item['unit_price'] ?? $item['price'] ?? 0;
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $unitPrice,
            'options_json' => $item['options'] ?? [],
            'item_notes' => $item['notes'] ?? $item['item_notes'] ?? null,
            'subtotal' => $unitPrice * $item['quantity'],
        ]);
    }

    session(['last_order_number' => $order->order_number]);

    return response()->json([
        'success' => true,
        'order_number' => $order->order_number,
        'redirect_url' => route('customer.ticket', $order->order_number),
    ]);
}
```

File: `resources/views/customer/checkout.blade.php` (baris 152-189)
```javascript
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
```

**Penjelasan kode:**
- `validate(['items' => 'required|array|min:1'])` — memastikan minimal ada 1 item dalam pesanan.
- Nomor order digenerate dengan format `ORD-YYMMDD-XXX` berdasarkan ID tertinggi di tabel orders.
- Pajak dihitung 10% dari subtotal.
- `Order::create()` membuat record order dengan status awal `unpaid`.
- `OrderItem::create()` dalam loop membuat item-item pesanan terkait.
- CSRF token dikirim melalui header `X-CSRF-TOKEN` dalam request `fetch`.
- Setelah sukses, cart dibersihkan dan redirect ke halaman tiket.

**Hubungan komponen:**
- Route → `CustomerController@placeOrder` → Model `Order` + `OrderItem` → JSON response → View `customer.ticket`

---

### FITUR 5: CSRF Protection

**Nama dan Fungsi:** Melindungi aplikasi dari serangan Cross-Site Request Forgery dengan memastikan setiap request POST/PUT/DELETE menyertakan token CSRF yang valid.

**Alur kerja:**
1. Laravel menghasilkan CSRF token per session, disimpan di meta tag di `<head>`.
2. Di Blade, `@csrf` menghasilkan input hidden dengan token.
3. Di JavaScript `fetch`, token dibaca dari meta tag dan dikirim melalui header `X-CSRF-TOKEN`.
4. Laravel middleware `VerifyCsrfToken` memvalidasi token sebelum memproses request.

**Kode penting:**

File: `resources/views/layouts/app.blade.php` (baris 6)
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

File: `resources/views/customer/checkout.blade.php` (baris 162-165)
```javascript
headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': '{{ csrf_token() }}'
},
```

File: `resources/views/cashier/update-status.blade.php` (baris 33-34)
```html
<form id="update-status-form" action="{{ route('cashier.updateStatus', $order->id) }}" method="POST">
    @csrf
```

File: `resources/views/cashier/menu-manager.blade.php` (baris 98-99, 111-113, 147)
```html
<form action="{{ route('cashier.toggleAvailability', $prod->id) }}" method="POST">
    @csrf
```

```html
<form action="{{ route('cashier.deleteProduct', $prod->id) }}" method="POST" onsubmit="...">
    @csrf
    @method('DELETE')
```

```html
<form action="{{ route('cashier.storeProduct') }}" method="POST" class="space-y-3.5">
    @csrf
```

**Penjelasan kode:**
- `@csrf` directive Laravel menghasilkan `<input type="hidden" name="_token" value="...">`.
- Di JavaScript, `X-CSRF-TOKEN` header digunakan untuk request AJAX/fetch.
- `@method('DELETE')` digunakan untuk spoofing method DELETE dalam form HTML.

**Hubungan komponen:**
- Blade directive `@csrf` → Laravel CSRF Middleware → Validasi otomatis pada setiap request POST/PUT/DELETE

---

### FITUR 6: Tiket QR Pesanan

**Nama dan Fungsi:** Menampilkan tiket digital dengan QR code yang berisi URL status pesanan. Kasir bisa memindai QR untuk mencocokkan item dan total tagihan.

**Alur kerja:**
1. Setelah pesanan dibuat, pengunjung diarahkan ke `/ticket/{orderNumber}`.
2. View mengambil data order beserta itemnya.
3. QR code digenerate menggunakan library `QRCode.js` dengan URL `cashier.editStatus`.
4. Halaman melakukan polling setiap 3 detik ke `/api/order-status/{orderNumber}` untuk mendeteksi perubahan status.

**Kode penting:**

File: `routes/web.php` (baris 23)
```php
Route::get('/ticket/{orderNumber}', [CustomerController::class, 'ticket'])->name('customer.ticket');
```

File: `app/Http/Controllers/CustomerController.php` (baris 213-217)
```php
public function ticket($orderNumber)
{
    $order = Order::with('items.product')->where('order_number', $orderNumber)->firstOrFail();
    return view('customer.ticket', compact('order'));
}
```

File: `resources/views/customer/ticket.blade.php` (baris 158-178, 288-303)
```javascript
const orderNumber = @json($order->order_number);
const updateStatusUrl = @json(route('cashier.editStatus', $order->order_number));
// ...
renderQr('qrcode-container', 170);
// ...
setInterval(async () => {
    if (ticketResolved) return;
    try {
        const res = await fetch(`/api/order-status/${encodeURIComponent(orderNumber)}`, { cache: 'no-store' });
        if (!res.ok) return;
        const data = await res.json();
        if (data.is_paid || data.is_preparing || inProcessing(data.status)) {
            applyProcessedState();
            // ...
        }
    } catch (e) {}
}, 3000);
```

**Penjelasan kode:**
- `firstOrFail()` — memastikan order dengan nomor tersebut ada, jika tidak akan 404.
- `with('items.product')` — eager loading item pesanan beserta produknya.
- QR code berisi URL `cashier.editStatus` agar kasir dapat langsung mengakses halaman update status saat memindai.
- Polling setiap 3 detik ke API status untuk update real-time tanpa refresh halaman.

**Hubungan komponen:**
- Route → `CustomerController@ticket` → Model `Order` (dengan `items.product`) → View `customer.ticket` + JavaScript polling → API `/api/order-status/{orderNumber}`

---

### FITUR 7: Pelacakan Status Pesanan

**Nama dan Fungsi:** Memungkinkan pelanggan memantau status pesanan dari "Belum Dibayar" hingga "Selesai" melalui timeline visual.

**Alur kerja:**
1. Pengguna membuka `/track/{orderNumber}` atau menggunakan order terakhir dari session.
2. Controller mengambil data order dan menampilkan timeline status.
3. JavaScript polling setiap 3 detik ke API status; jika status berubah, halaman direfresh otomatis.

**Kode penting:**

File: `routes/web.php` (baris 24-25)
```php
Route::get('/track/{orderNumber?}', [CustomerController::class, 'trackStatus'])->name('customer.trackStatus');
Route::get('/api/order-status/{orderNumber}', [CustomerController::class, 'getOrderStatusApi'])->name('customer.statusApi');
```

File: `app/Http/Controllers/CustomerController.php` (baris 220-252)
```php
public function trackStatus($orderNumber = null)
{
    if (!$orderNumber) {
        $orderNumber = session('last_order_number');
    }

    if ($orderNumber) {
        $order = Order::with('items.product')->where('order_number', $orderNumber)->first();
    } else {
        $order = Order::with('items.product')->latest()->first();
    }

    if (!$order) {
        return redirect()->route('customer.catalog');
    }

    return view('customer.status-tracking', compact('order'));
}

public function getOrderStatusApi($orderNumber)
{
    $order = Order::where('order_number', $orderNumber)->firstOrFail();
    return response()->json([
        'status' => $order->status,
        'status_label' => $order->status_label,
        'is_paid' => in_array($order->status, ['paid', 'preparing', 'ready', 'completed']),
        'is_preparing' => in_array($order->status, ['preparing', 'ready']),
        'is_completed' => $order->status === 'completed',
        'updated_at' => $order->updated_at->format('H:i A'),
    ]);
}
```

File: `resources/views/customer/status-tracking.blade.php` (baris 116-129)
```javascript
setInterval(async () => {
    try {
        const res = await fetch(`/api/order-status/${orderNumber}`);
        const data = await res.json();
        if (data.status !== currentStatus) {
            window.location.reload();
        }
    } catch (e) {}
}, 3000);
```

**Penjelasan kode:**
- `trackStatus` — mengambil order berdasarkan nomor, atau dari session, atau order terbaru sebagai fallback.
- `getOrderStatusApi` — mengembalikan JSON dengan status dan label status dalam bahasa Indonesia.
- Polling di frontend membandingkan status saat ini dengan status dari API; jika berbeda, halaman direfresh.

**Hubungan komponen:**
- Route → `CustomerController@trackStatus` / `@getOrderStatusApi` → Model `Order` → View `customer.status-tracking` + API polling

---

### FITUR 8: Cashier Incoming Orders

**Nama dan Fungsi:** Halaman utama kasir untuk melihat semua pesanan aktif (belum dibayar, sudah dibayar, diproses, siap) dan riwayat pesanan selesai.

**Alur kerja:**
1. Kasir membuka `/cashier` (dilindungi middleware `role`).
2. Controller mengambil order dengan filter status (active atau completed).
3. View menampilkan kartu-kartu order dengan status, total, dan aksi berdasarkan status (konfirmasi pembayaran, proses, selesai, atau ubah status).
4. Setiap kartu memiliki tombol aksi dan link ke detail/order.

**Kode penting:**

File: `routes/web.php` (baris 28-29)
```php
Route::prefix('cashier')->middleware('role')->group(function () {
    Route::get('/', [CashierController::class, 'incomingOrders'])->name('cashier.incomingOrders');
```

File: `app/Http/Controllers/CashierController.php` (baris 61-85)
```php
public function incomingOrders(Request $request)
{
    $statusFilter = $request->get('status', 'active');
    $query = Order::with('items.product')->latest();

    if ($statusFilter === 'completed') {
        $query->where('status', 'completed');
    } elseif ($statusFilter === 'active') {
        $query->whereIn('status', ['paid', 'preparing', 'ready', 'unpaid', 'pending']);
    }

    $orders = $query->get();

    $stats = [
        'active' => Order::whereIn('status', ['paid', 'preparing', 'ready', 'unpaid', 'pending'])->count(),
        'paid' => Order::where('status', 'paid')->count(),
        'preparing' => Order::whereIn('status', ['preparing', 'ready'])->count(),
        'completed' => Order::where('status', 'completed')->count(),
        'all' => Order::count(),
    ];

    return view('cashier.incoming-orders', compact('orders', 'statusFilter', 'stats'));
}
```

File: `resources/views/cashier/incoming-orders.blade.php` (baris 83-167)
```blade
@elseif($order->status == 'paid')
    <article class="card-soft card-hover rounded-2xl p-4 relative overflow-hidden group animate-in-soft">
        <span class="status-pill pill-paid"><span class="pill-dot"></span>Sudah Dibayar</span>
        <div class="flex flex-col mb-4">
            <span class="text-headline-sm font-headline-sm text-on-surface font-semibold">{{ $order->order_number }}</span>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-body-secondary font-body-secondary text-status-unpaid">{{ $order->items->sum('quantity') }} Item</span>
                <span class="w-1 h-1 rounded-full bg-status-unpaid/50"></span>
                <span class="text-body-lg font-body-lg font-bold text-on-surface">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="flex gap-2 items-center w-full">
            <form action="{{ route('cashier.updateStatus', $order->id) }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="status" value="preparing">
                <button type="submit" class="btn-cta w-full py-3 text-sm flex justify-center items-center gap-2">
                    <span class="material-symbols-outlined text-base">play_arrow</span> Proses Pesanan
                </button>
            </form>
```

**Penjelasan kode:**
- Filter status `active` mencakup: `paid`, `preparing`, `ready`, `unpaid`, `pending`.
- Filter `completed` hanya menampilkan order yang sudah selesai.
- Statistik dihitung untuk setiap kategori status.
- Setiap status menampilkan tombol aksi yang berbeda (konfirmasi pembayaran untuk unpaid, proses untuk paid, selesai untuk preparing/ready).

**Hubungan komponen:**
- Route (dengan middleware `role`) → `CashierController@incomingOrders` → Model `Order` → View `cashier.incoming-orders`

---

### FITUR 9: Scan Barcode / QR

**Nama dan Fungsi:** Memungkinkan kasir memindai barcode/QR code pesanan menggunakan kamera atau memasukkan kode secara manual untuk mencari pesanan.

**Alur kerja:**
1. Kasir membuka `/cashier/scan`.
2. Kamera diaktifkan menggunakan library `html5-qrcode`.
3. Jika kode ditemukan, dipanggil API `/cashier/search-barcode` untuk mencari order.
4. Jika ditemukan, redirect ke halaman update status order tersebut.

**Kode penting:**

File: `routes/web.php` (baris 30-31)
```php
Route::get('/scan', [CashierController::class, 'scanBarcode'])->name('cashier.scanBarcode');
Route::post('/search-barcode', [CashierController::class, 'searchBarcode'])->name('cashier.searchBarcode');
```

File: `app/Http/Controllers/CashierController.php` (baris 100-133)
```php
public function scanBarcode()
{
    $recentOrders = Order::latest()->take(5)->get();
    return view('cashier.scan-barcode', compact('recentOrders'));
}

public function searchBarcode(Request $request)
{
    $code = trim($request->get('code'));

    if (preg_match('/(?:order\/)([A-Za-z0-9\-]+)/', $code, $matches)) {
        $code = $matches[1];
    }

    $order = Order::where('order_number', $code)
        ->orWhere('id', $code)
        ->orWhere('barcode_hash', $code)
        ->orWhere('order_number', 'like', '%' . $code . '%')
        ->first();

    if ($order) {
        return response()->json([
            'found' => true,
            'redirect_url' => route('cashier.editStatus', $order->id),
        ]);
    }

    return response()->json([
        'found' => false,
        'message' => 'Pesanan dengan kode "' . $code . '" tidak ditemukan.',
    ]);
}
```

File: `resources/views/cashier/scan-barcode.blade.php` (baris 53-61)
```javascript
const response = await fetch("{{ route('cashier.searchBarcode') }}", {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ code: code })
});
```

**Penjelasan kode:**
- Pencarian dilakukan pada `order_number`, `id`, `barcode_hash`, atau pencarian parsial dengan `like`.
- Jika kode adalah URL lengkap, diekstrak identifier-nya dengan regex.
- CSRF token dikirim dalam header `X-CSRF-TOKEN`.

**Hubungan komponen:**
- Route → `CashierController@searchBarcode` → Model `Order` → JSON response → JavaScript redirect

---

### FITUR 10: Konfirmasi Pembayaran

**Nama dan Fungsi:** Kasir mengonfirmasi bahwa pelanggan telah melakukan pembayaran, mengubah status order dari `unpaid` ke `paid`.

**Alur kerja:**
1. Kasir klik "Konfirmasi / Scan" pada order dengan status `unpaid`.
2. Request `POST` ke `/cashier/order/{id}/confirm-payment`.
3. Controller memvalidasi apakah order bisa bertransisi (bukan terminal, belum diproses).
4. Jika valid, status diubah ke `paid` dan event `OrderStatusUpdated` dikirim.

**Kode penting:**

File: `routes/web.php` (baris 35)
```php
Route::post('/order/{id}/confirm-payment', [CashierController::class, 'confirmPayment'])->name('cashier.confirmPayment');
```

File: `app/Http/Controllers/CashierController.php` (baris 136-185)
```php
public function confirmPayment(Request $request, $id)
{
    $order = $this->findOrder((string) $id);

    if (! $order) {
        return $request->wantsJson() ? $this->orderNotFoundJson() : $this->orderNotFoundWeb();
    }

    $normalized = Order::normalizeStatus($order->status);

    if ($order->isTerminal()) {
        $message = 'Pesanan ' . $order->order_number . ' sudah ' . $order->status_label . ' dan tidak dapat diubah lagi.';
        return $request->wantsJson() ? $this->transitionRejectedJson($message) : $this->transitionRejectedWeb($order, $message);
    }

    if (in_array($normalized, ['preparing', 'ready'], true)) {
        $message = 'Pesanan ' . $order->order_number . ' sedang diproses dan tidak dapat dikonfirmasi pembayarannya lagi.';
        return $request->wantsJson() ? $this->transitionRejectedJson($message) : $this->transitionRejectedWeb($order, $message);
    }

    if ($normalized === 'paid') {
        // ... sudah dibayar, return sukses
    }

    $order->status = 'paid';
    $order->payment_status = 'paid';
    $order->save();

    event(new OrderStatusUpdated($order));
    // ... return response
}
```

**Penjelasan kode:**
- `findOrder()` — validasi input identifier (hanya alphanumeric dan strip, maks 64 karakter).
- `isTerminal()` — mencegah perubahan pada order yang sudah `completed` atau `cancelled`.
- `normalizeStatus()` — mengubah `pending` menjadi `unpaid` untuk konsistensi.
- `event(new OrderStatusUpdated($order))` — memicu broadcast real-time ke channel `orders`.

**Hubungan komponen:**
- Route → `CashierController@confirmPayment` → Model `Order` → Event `OrderStatusUpdated` → Response JSON/Redirect

---

### FITUR 11: Update Status Pesanan

**Nama dan Fungsi:** Kasir mengubah status pesanan melalui alur forward-only: `unpaid` → `paid` → `preparing` → `ready` → `completed`. Status terminal (`completed`, `cancelled`) tidak bisa diubah lagi.

**Alur kerja:**
1. Kasir membuka halaman update status melalui `/cashier/order/{id}/update-status`.
2. Memilih status baru dari opsi yang tersedia (Sudah Dibayar, Sedang Diproses, Selesai).
3. Form disubmit dengan `@csrf` dan divalidasi oleh controller.
4. Controller memeriksa transisi yang diizinkan melalui `canTransitionTo()`.
5. Jika valid, status diubah, `payment_status` disesuaikan, dan event dikirim.

**Kode penting:**

File: `routes/web.php` (baris 33-34)
```php
Route::get('/order/{id}/update-status', [CashierController::class, 'editStatus'])->name('cashier.editStatus');
Route::post('/order/{id}/update-status', [CashierController::class, 'updateStatus'])->name('cashier.updateStatus');
```

File: `app/Http/Controllers/CashierController.php` (baris 200-257)
```php
public function updateStatus(Request $request, $id)
{
    $order = $this->findOrder((string) $id);

    if (! $order) {
        return $request->wantsJson() ? $this->orderNotFoundJson() : $this->orderNotFoundWeb();
    }

    $request->validate([
        'status' => 'required|in:unpaid,pending,paid,preparing,ready,completed,cancelled',
    ]);

    $newStatus = Order::normalizeStatus($request->status);

    if ($order->isTerminal()) {
        // ... tolak transisi
    }

    if ($newStatus === $order->status) {
        // ... idempotent no-op
    }

    if (! $order->canTransitionTo($newStatus)) {
        // ... tolak transisi yang tidak diizinkan
    }

    $order->status = $newStatus;
    if (in_array($newStatus, ['paid', 'preparing', 'ready', 'completed'], true)) {
        $order->payment_status = 'paid';
    }
    $order->save();

    event(new OrderStatusUpdated($order));
    // ... return response
}
```

File: `app/Models/Order.php` (baris 48-55)
```php
public function canTransitionTo(string $newStatus): bool
{
    if ($this->isTerminal()) {
        return false;
    }

    return in_array($newStatus, self::FORWARD_STATUSES, true);
}
```

File: `resources/views/cashier/update-status.blade.php` (baris 33-34, 100-110)
```html
<form id="update-status-form" action="{{ route('cashier.updateStatus', $order->id) }}" method="POST">
    @csrf
    <!-- ... opsi status ... -->
    <div class="mt-6 flex justify-end gap-3">
        <a href="{{ route('cashier.orderDetail', $order->id) }}" class="btn-soft ...">Batal</a>
        <button type="submit" id="btn-save-status" class="btn-cta ...">Simpan</button>
    </div>
</form>
```

**Penjelasan kode:**
- Validasi status hanya mengizinkan nilai dalam daftar: `unpaid`, `pending`, `paid`, `preparing`, `ready`, `completed`, `cancelled`.
- `canTransitionTo()` memastikan transisi forward-only dan mencegah perubahan pada status terminal.
- Saat status berubah ke `paid`, `preparing`, `ready`, atau `completed`, `payment_status` juga diupdate ke `paid`.
- Event `OrderStatusUpdated` dipicu untuk notifikasi real-time.

**Hubungan komponen:**
- Route → `CashierController@updateStatus` → Model `Order` (`canTransitionTo`, `isTerminal`) → Event `OrderStatusUpdated` → View `cashier.update-status`

---

### FITUR 12: CRUD Menu (Produk & Kategori)

**Nama dan Fungsi:** Kasir dapat mengelola katalog menu: melihat daftar produk, menambah produk baru, mengedit produk, mengubah ketersediaan (tersedia/habis), dan menghapus produk.

**Alur kerja:**
1. Kasir membuka `/cashier/menu` (menu manager).
2. Produk ditampilkan dalam tabel dengan filter jenis (makanan/minuman), pencarian, dan paginasi.
3. Setiap baris memiliki aksi: toggle ketersediaan, edit, atau hapus.
4. Modal untuk menambah atau mengedit produk dengan validasi sisi server.

**Kode penting:**

File: `routes/web.php` (baris 40-44)
```php
Route::get('/menu', [CashierController::class, 'menuManager'])->name('cashier.menuManager');
Route::post('/menu/{id}/toggle-availability', [CashierController::class, 'toggleAvailability'])->name('cashier.toggleAvailability');
Route::post('/menu/{id}/update', [CashierController::class, 'updateProduct'])->name('cashier.updateProduct');
Route::post('/menu/store', [CashierController::class, 'storeProduct'])->name('cashier.storeProduct');
Route::delete('/menu/{id}', [CashierController::class, 'deleteProduct'])->name('cashier.deleteProduct');
```

File: `app/Http/Controllers/CashierController.php` (baris 283-373)
```php
public function menuManager(Request $request)
{
    $selectedType = $request->get('type', 'all');
    $selectedCategory = $request->get('category', 'all');
    $search = $request->get('search');

    $categories = Category::all();
    $query = Product::with('category');

    if ($selectedType !== 'all') {
        $query->whereHas('category', fn($q) => $q->where('type', $selectedType));
    }

    if ($selectedCategory !== 'all') {
        $query->whereHas('category', fn($q) => $q->where('slug', $selectedCategory));
    }

    if (!empty($search)) {
        $query->where('name', 'like', '%' . $search . '%');
    }

    $products = $query->orderBy('category_id')->orderBy('name')->paginate(20)->withQueryString();

    return view('cashier.menu-manager', compact('products', 'categories', 'selectedType', 'selectedCategory', 'search'));
}

public function toggleAvailability($id)
{
    $product = Product::findOrFail($id);
    $product->is_available = !$product->is_available;
    $product->save();

    return back()->with('success', 'Status ketersediaan ' . $product->name . ' diperbarui.');
}

public function updateProduct(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'tags' => 'nullable|string|max:255',
    ]);

    $product->update([
        'name' => $request->name,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'tags' => $request->tags,
        'description' => $request->description ?? '',
    ]);

    return back()->with('success', 'Menu ' . $product->name . ' berhasil diperbarui.');
}

public function storeProduct(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
    ]);

    $slug = \Illuminate\Support\Str::slug($request->name);
    if (Product::where('slug', $slug)->exists()) {
        $slug .= '-' . time();
    }

    Product::create([
        'name' => $request->name,
        'slug' => $slug,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'tags' => $request->tags ?? 'New',
        'description' => $request->description ?? '',
        'image_url' => $request->image_url ?? 'https://images.unsplash.com/...',
        'is_available' => true,
    ]);

    return back()->with('success', 'Menu baru berhasil ditambahkan.');
}

public function deleteProduct($id)
{
    $product = Product::findOrFail($id);
    $product->delete();

    return back()->with('success', 'Menu berhasil dihapus.');
}
```

File: `app/Models/Product.php` (baris 8-34)
```php
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price',
        'image_url', 'calories', 'rating', 'tags', 'is_available'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'float',
        'rating' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getPriceKAttribute(): string
    {
        $thousands = $this->price / 1000;
        return (floor($thousands) == $thousands ? number_format($thousands, 0) : number_format($thousands, 1)) . 'K';
    }

    public function getImageUrlAttribute($value): string
    {
        if (!empty($value) && $value !== '/placeholder.svg') {
            return $value;
        }
        return 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80';
    }
}
```

**Penjelasan kode:**
- `menuManager` — menampilkan produk dengan eager loading kategori, filter tipe/kategori, pencarian, dan paginasi 20 per halaman.
- `toggleAvailability` — membalik nilai boolean `is_available`.
- `updateProduct` — memvalidasi input (name, price, category_id harus ada di tabel categories).
- `storeProduct` — generate slug otomatis dari nama, menangani duplikasi slug dengan menambahkan timestamp.
- `deleteProduct` — menghapus produk (cascade akan menghapus order items terkait sesuai migration).
- Accessor `getPriceKAttribute` — memformat harga dalam notasi "K" (ribuan).
- Accessor `getImageUrlAttribute` — memberikan fallback image dari Unsplash jika tidak ada gambar.

**Hubungan komponen:**
- Route (dengan middleware `role`) → `CashierController` → Model `Product` + `Category` → View `cashier.menu-manager`

---

### FITUR 13: Authentication & Authorization (Role-based)

**Nama dan Fungsi:** Mengelola role pengguna (customer, cashier, admin) dan membatasi akses ke halaman kasir hanya untuk pengguna dengan role cashier atau admin.

**Alur kerja:**
1. Middleware `EnsureCashierRole` memeriksa user yang login.
2. Jika user tidak null dan tidak memiliki akses kasir, menerima 403 Forbidden.
3. Jika user null (belum login / kiosk mode), akses masih diizinkan (soft gate).

**Kode penting:**

File: `app/Models/User.php` (baris 51-54)
```php
public function hasCashierAccess(): bool
{
    return in_array($this->role ?? 'customer', ['admin', 'cashier'], true);
}
```

File: `app/Http/Middleware/EnsureCashierRole.php` (baris 16-32)
```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();

    if ($user !== null && ! $user->hasCashierAccess()) {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Halaman ini hanya untuk kasir atau admin.',
            ], 403);
        }

        return response()->view('errors.403', [], 403);
    }

    return $next($request);
}
```

File: `routes/web.php` (baris 28)
```php
Route::prefix('cashier')->middleware('role')->group(function () {
```

File: `database/migrations/2026_09_23_000001_add_role_to_users_table.php` (baris 11-12)
```php
$table->string('role')->default('customer')->after('password');
```

File: `config/auth.php` (baris 40-45, 64-68)
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => env('AUTH_MODEL', User::class),
    ],
],
```

**Penjelasan kode:**
- `hasCashierAccess()` — memeriksa apakah role user adalah `admin` atau `cashier`.
- Middleware `EnsureCashierRole` — soft role gate: route tetap terbuka jika belum ada login flow (kiosk mode), tapi user yang terauthentikasi tanpa role cashier/admin akan mendapat 403.
- Migration menambahkan kolom `role` dengan default `customer` pada tabel users.
- Konfigurasi auth menggunakan guard `web` dengan driver session dan provider Eloquent model `User`.

**Hubungan komponen:**
- Model `User` → Middleware `EnsureCashierRole` → Route group `cashier.*` → Controller Cashier

---

### FITUR 14: Order Status Event Broadcasting

**Nama dan Fungsi:** Mengirim notifikasi real-time setiap kali status pesanan berubah, sehingga halaman tiket dan tracking dapat memperbarui status tanpa refresh manual.

**Alur kerja:**
1. Setiap kali status order berubah (confirmPayment atau updateStatus), event `OrderStatusUpdated` dipicu.
2. Event mengimplementasi `ShouldBroadcastNow` untuk siaran instan.
3. Event di-broadcast pada channel `orders` dengan event name `status-updated`.
4. Data yang dikirim mencakup order_number, status, status_label, dan payment_status.

**Kode penting:**

File: `app/Events/OrderStatusUpdated.php` (baris 11-37)
```php
class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'status-updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'status_label' => $this->order->status_label,
            'payment_status' => $this->order->payment_status,
        ];
    }
}
```

File: `app/Http/Controllers/CashierController.php` (baris 173, 246)
```php
event(new OrderStatusUpdated($order));
```

**Penjelasan kode:**
- `ShouldBroadcastNow` — event akan segera di-broadcast tanpa antrian queue.
- `broadcastOn()` — menentukan channel broadcast adalah `orders`.
- `broadcastAs()` — nama event adalah `status-updated`.
- `broadcastWith()` — payload yang dikirim ke client.
- Dipanggil di `confirmPayment` dan `updateStatus` setelah order disimpan.

**Hubungan komponen:**
- Model `Order` → Controller `CashierController` → Event `OrderStatusUpdated` → Broadcast channel `orders` → Client (JavaScript Echo/polling)

---

### FITUR 15: Database Schema & Migration

**Nama dan Fungsi:** Mendefinisikan struktur tabel database untuk users, categories, products, orders, dan order_items dengan relasi foreign key.

**Kode penting:**

File: `database/migrations/2026_08_31_000001_create_categories_table.php` (baris 11-18)
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->enum('type', ['minuman', 'makanan'])->default('minuman');
    $table->string('icon')->default('coffee');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

File: `database/migrations/2026_08_31_000002_create_products_table.php` (baris 11-23)
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->string('image_url');
    $table->integer('calories')->nullable();
    $table->decimal('rating', 3, 1)->default(4.8);
    $table->string('tags')->nullable();
    $table->boolean('is_available')->default(true);
    $table->timestamps();
});
```

File: `database/migrations/2026_08_31_000003_create_orders_table.php` (baris 11-25)
```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique();
    $table->string('customer_name');
    $table->string('table_number')->nullable();
    $table->string('order_type')->default('dine_in');
    $table->string('status')->default('pending');
    $table->string('payment_status')->default('paid');
    $table->string('payment_method')->default('qris');
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax', 10, 2);
    $table->decimal('total', 10, 2);
    $table->string('barcode_hash')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

File: `database/migrations/2026_08_31_000004_create_order_items_table.php` (baris 11-20)
```php
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->text('options_json')->nullable();
    $table->text('item_notes')->nullable();
    $table->decimal('subtotal', 10, 2);
    $table->timestamps();
});
```

**Penjelasan kode:**
- Tabel `categories` — menyimpan kategori menu dengan tipe `minuman` atau `makanan`.
- Tabel `products` — menyimpan produk dengan foreign key ke categories, harga desimal, dan flag ketersediaan.
- Tabel `orders` — menyimpan header pesanan dengan nomor order unik, status pembayaran, dan total.
- Tabel `order_items` — menyimpan item-item dalam pesanan dengan foreign key ke orders dan products, plus opsi JSON dan catatan item.
- Semua foreign key menggunakan `onDelete('cascade')` untuk integritas referensial.

**Hubungan komponen:**
- Migration → Tabel Database → Model Eloquent (`Category`, `Product`, `Order`, `OrderItem`) → Controller

---

### FITUR 16: Seeding Data Awal

**Nama dan Fungsi:** Mengisi database dengan data kategori dan produk awal (menu lengkap Nord Cafe) saat pertama kali setup.

**Kode penting:**

File: `database/seeders/NordCafeSeeder.php` (baris 12-360)
```php
class NordCafeSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            [
                'category' => [
                    'name' => 'Non Coffee',
                    'slug' => 'non-coffee',
                    'type' => 'minuman',
                    'icon' => 'fa-blender',
                ],
                'products' => [
                    ['name' => 'Matcha', 'price' => 23000, 'tags' => 'Bestseller,Non-Coffee', 'calories' => 180, 'image_url' => '...'],
                    // ... lebih banyak produk
                ]
            ],
            // ... lebih banyak kategori
        ];

        $usedSlugs = [];

        foreach ($catalog as $section) {
            $cat = Category::firstOrCreate(
                ['slug' => $section['category']['slug']],
                $section['category']
            );

            foreach ($section['products'] as $prod) {
                $slug = Str::slug($prod['name']);
                if (isset($usedSlugs[$slug])) {
                    $slug = $slug . '-' . $cat->slug;
                }
                $usedSlugs[$slug] = true;

                Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $cat->id,
                        'name' => $prod['name'],
                        'description' => $prod['description'] ?? '',
                        'price' => $prod['price'],
                        'image_url' => $prod['image_url'],
                        'calories' => $prod['calories'] ?? 200,
                        'rating' => 4.8,
                        'tags' => $prod['tags'] ?? 'Popular',
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
```

**Penjelasan kode:**
- `firstOrCreate` — membuat kategori jika belum ada berdasarkan slug.
- `updateOrCreate` — membuat atau memperbarui produk berdasarkan slug (idempotent).
- Slug digenerate dari nama produk dengan penanganan duplikasi.
- Data mencakup 16 kategori dengan ratusan produk makanan dan minuman.

**Hubungan komponen:**
- Seeder → Model `Category` + `Product` → Tabel Database

---

### FITUR 17: Konfigurasi Laravel Penting

**Nama dan Fungsi:** Konfigurasi aplikasi untuk database, auth, dan environment.

**Kode penting:**

File: `config/database.php` (baris 20, 35-45)
```php
'default' => env('DB_CONNECTION', 'sqlite'),

'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    // ...
],
```

File: `config/auth.php` (baris 18-21, 40-45)
```php
'defaults' => [
    'guard' => env('AUTH_GUARD', 'web'),
    'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

File: `vercel.json` (baris 1-13)
```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "services": {
    "app": {
      "root": ".",
      "entrypoint": "Dockerfile",
      "runtime": "container"
    }
  },
  "rewrites": [
    { "source": "/(.*)", "destination": { "service": "app" } }
  ]
}
```

File: `.env.example` (baris 23-34)
```
DB_CONNECTION=sqlite
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

**Penjelasan kode:**
- Default database connection adalah SQLite dengan file `database.sqlite`.
- Auth guard menggunakan session dan provider Eloquent model `User`.
- `vercel.json` mengonfigurasi aplikasi sebagai container Docker pada Vercel dengan rewrite semua request ke service app.
- Session menggunakan driver database dengan lifetime 120 menit.

---

## 4. MODEL DAN RELASI DATABASE

### Model User
File: `app/Models/User.php`
- Fillable: `name`, `email`, `password`, `role`
- Hidden: `password`, `remember_token`
- Method: `hasCashierAccess()` — cek role admin/cashier

### Model Category
File: `app/Models/Category.php`
- Fillable: `name`, `slug`, `type`, `icon`, `is_active`
- Relasi: `hasMany(Product::class)` — satu kategori memiliki banyak produk

### Model Product
File: `app/Models/Product.php`
- Fillable: `category_id`, `name`, `slug`, `description`, `price`, `image_url`, `calories`, `rating`, `tags`, `is_available`
- Casts: `is_available` (boolean), `price` (float), `rating` (float)
- Relasi: `belongsTo(Category::class)` — produk milik satu kategori
- Accessor: `getPriceKAttribute()` — format harga dalam notasi K
- Accessor: `getImageUrlAttribute()` — fallback gambar dari Unsplash

### Model Order
File: `app/Models/Order.php`
- Fillable: `order_number`, `customer_name`, `table_number`, `order_type`, `status`, `payment_status`, `payment_method`, `subtotal`, `tax`, `total`, `barcode_hash`, `notes`
- Konstanta: `TERMINAL_STATUSES = ['completed', 'cancelled']`, `FORWARD_STATUSES = ['paid', 'preparing', 'ready', 'completed', 'cancelled']`
- Relasi: `hasMany(OrderItem::class)` — order memiliki banyak item
- Method: `normalizeStatus()`, `isTerminal()`, `canTransitionTo()`, `getStatusLabelAttribute()`, `getStatusBadgeClassAttribute()`

### Model OrderItem
File: `app/Models/OrderItem.php`
- Fillable: `order_id`, `product_id`, `quantity`, `unit_price`, `options_json`, `item_notes`, `subtotal`
- Casts: `options_json` (array)
- Relasi: `belongsTo(Order::class)`, `belongsTo(Product::class)`

---

## 5. ALUR PROSES APLIKASI

### Alur Pemesanan Pelanggan (Self-Order)

```
Pelanggan buka / (Katalog)
    ↓
Pilih produk → "Add to Cart" (disimpan di localStorage via NordCart JS)
    ↓
Buka /cart (lihat keranjang, ubah qty, hapus item)
    ↓
Buka /checkout (ringkasan, pilih metode pembayaran)
    ↓
Klik "Buat Pesanan" → POST /order/place (dengan CSRF token)
    ↓
Backend buat Order (status: unpaid) + OrderItem
    ↓
Return JSON: order_number + redirect_url ke /ticket/{orderNumber}
    ↓
Frontend clear cart, simpan order ke localStorage, redirect ke tiket
    ↓
Halaman tiket menampilkan QR code + polling status setiap 3 detik
    ↓
Kasir konfirmasi pembayaran → status berubah ke paid
    ↓
Tiket otomatis update: "Pesanan Diproses"
```

### Alur Manajemen Kasir (POS)

```
Kasir buka /cashier (dilindungi middleware role)
    ↓
Lihat daftar pesanan aktif (unpaid, paid, preparing, ready)
    ↓
Aksi berdasarkan status:
    • unpaid  → "Konfirmasi / Scan" → ubah ke paid
    • paid    → "Proses Pesanan" → ubah ke preparing
    • preparing/ready → "Tandai Selesai" → ubah ke completed
    • completed → lihat detail only (terminal)
    ↓
Atau gunakan scan barcode/QR untuk cari pesanan cepat
    ↓
Atau kelola menu (CRUD produk) di /cashier/menu
```

### Alur Transisi Status Order

```
unpaid (Belum Dibayar)
    ↓ [kasir konfirmasi pembayaran]
paid (Sudah Dibayar)
    ↓ [kasir memulai proses]
preparing (Sedang Diproses)
    ↓ [kasir selesai menyiapkan]
ready (Siap) — digabungkan dengan preparing di UI sebagai "Sedang Diproses"
    ↓ [kasir tandai selesai]
completed (Selesai) — TERMINAL, tidak dapat diubah lagi
```

---

## 6. KESIMPULAN

Project Nord Cafe adalah sistem manajemen restoran/cafe berbasis Laravel 12 yang memenuhi kebutuhan operasional dasar sebuah kafe dengan dua antarmuka utama:

1. **Customer Self-Order** — pelanggan dapat melakukan pemesanan mandiri melalui katalog menu, keranjang belanja (client-side via localStorage), checkout, dan pelacakan status pesanan secara real-time dengan tiket QR.

2. **Cashier POS** — kasir dapat mengelola pesanan masuk, memindai barcode/QR, mengonfirmasi pembayaran, mengupdate status pesanan melalui alur forward-only, dan mengelola katalog menu melalui CRUD.

### Komponen Kunci yang Diimplementasikan:

| Komponen | Status | Keterangan |
|----------|--------|------------|
| Routing | ✅ | `routes/web.php` dengan grouping prefix dan middleware |
| Controller | ✅ | `CustomerController` dan `CashierController` |
| Model & Relasi | ✅ | User, Category, Product, Order, OrderItem dengan Eloquent |
| Authentication | ✅ | Laravel session guard, model User dengan role |
| Authorization | ✅ | Middleware `EnsureCashierRole` untuk route cashier |
| Validasi Input | ✅ | Laravel request validation di controller |
| Keranjang Belanja | ✅ | Client-side localStorage via `NordCart` JavaScript |
| Checkout | ✅ | `placeOrder` dengan generate order number, hitung pajak, buat Order + OrderItem |
| CSRF Protection | ✅ | `@csrf` di Blade, `X-CSRF-TOKEN` header di fetch |
| Pemrosesan Pesanan | ✅ | Order status management dengan transisi forward-only |
| Pelacakan Pesanan | ✅ | API status + polling real-time di frontend |
| CRUD Produk & Kategori | ✅ | Menu manager dengan toggle availability, create, update, delete |
| Event Broadcasting | ✅ | `OrderStatusUpdated` dengan `ShouldBroadcastNow` |
| Database | ✅ | Migration untuk 5 tabel, seeder untuk data awal |
| Konfigurasi | ✅ | `config/database.php`, `config/auth.php`, `vercel.json`, `.env.example` |

### File Penting yang Dokumentasi:

```
routes/web.php
app/Http/Controllers/CustomerController.php
app/Http/Controllers/CashierController.php
app/Http/Middleware/EnsureCashierRole.php
app/Models/User.php
app/Models/Category.php
app/Models/Product.php
app/Models/Order.php
app/Models/OrderItem.php
app/Events/OrderStatusUpdated.php
config/auth.php
config/database.php
database/migrations/*_create_*.php
database/seeders/NordCafeSeeder.php
resources/views/layouts/app.blade.php
resources/views/customer/{catalog,cart,checkout,product-detail,ticket,status-tracking}.blade.php
resources/views/cashier/{incoming-orders,order-detail,update-status,menu-manager,scan-barcode}.blade.php
vercel.json
```

Dokumentasi ini dibuat berdasarkan analisis langsung terhadap source code project Nord Cafe tanpa melakukan modifikasi apapun pada kode asli.