@extends('layouts.app')

@section('title', 'NORD CAFE - Cashier Pindai')

@section('content')
<main class="flex-grow px-margin-mobile pt-unit-xl pb-32 max-w-2xl mx-auto w-full flex flex-col items-center justify-center">
    <div class="card-soft w-full max-w-md p-unit-lg flex flex-col items-center text-center">
        
        <!-- Camera QR Scanner Viewport (HTML5-QRCode Library) -->
        <div id="qr-reader-wrapper" class="w-full mb-5">
            <div id="qr-reader" class="w-full rounded-2xl overflow-hidden border-2 border-dashed border-primary/30 bg-surface-container"></div>
        </div>

        <h2 class="text-headline-sm font-headline-sm font-bold text-on-surface mb-unit-sm tracking-tight">Pindai Barcode / QR Code</h2>
        <p class="text-body-secondary font-body-secondary text-status-unpaid mb-6">Arahkan kamera atau ketik Kode Pesanan pelanggan untuk verifikasi.</p>

        <!-- Toggle Camera Button -->
        <button id="toggle-camera-btn" type="button" class="btn-soft w-full mb-4 py-2.5 px-4 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-lg">videocam</span>
            <span id="camera-btn-text font-medium text-sm">Mulai Kamera Scanner</span>
        </button>

        <!-- Manual Input Fallback -->
        <form id="barcode-form" class="w-full flex gap-2">
            <input type="text" id="barcode-input" placeholder="Contoh: ORD-240815-012" 
                   class="flex-1 px-4 py-2.5 rounded-xl border border-on-surface/10 bg-surface-container-low text-on-surface text-body-md focus:outline-none focus:border-primary transition-colors">
            <button type="submit" class="btn-cta px-5 py-2.5 text-body-md shrink-0 flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">search</span>
                Cari
            </button>
        </form>
    </div>
</main>

@push('scripts')
<script>
    let html5QrcodeScanner = null;
    let isScanning = false;

    async function processOrderCode(code) {
        if (!code) return;

        // If code is a direct URL link to cashier order detail
        if (code.includes('/cashier/order/')) {
            if (html5QrcodeScanner && isScanning) {
                try { await html5QrcodeScanner.stop(); } catch (e) {}
            }
            window.location.href = code;
            return;
        }

        try {
            const response = await fetch("{{ route('cashier.searchBarcode') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: code })
            });

            const res = await response.json();
            if (res.found) {
                if (html5QrcodeScanner && isScanning) {
                    html5QrcodeScanner.stop();
                }
                window.location.href = res.redirect_url;
            } else {
                Swal.fire('Tidak Ditemukan', res.message || 'Kode pesanan tidak ditemukan.', 'warning');
            }
        } catch (err) {
            Swal.fire('Error', 'Gagal memproses pencarian barcode.', 'error');
        }
    }

    document.getElementById('barcode-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const code = document.getElementById('barcode-input').value.trim();
        processOrderCode(code);
    });

    const toggleBtn = document.getElementById('toggle-camera-btn');
    toggleBtn.addEventListener('click', () => {
        if (typeof Html5Qrcode === 'undefined') {
            Swal.fire('Info', 'Library Html5Qrcode belum dimuat.', 'info');
            return;
        }

        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
        }

        if (isScanning) {
            html5QrcodeScanner.stop().then(() => {
                isScanning = false;
                toggleBtn.querySelector('span:last-child').textContent = "Mulai Kamera Scanner";
            });
        } else {
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText, decodedResult) => {
                    processOrderCode(decodedText);
                },
                (errorMessage) => {
                    // Ignore frame scan failures
                }
            ).then(() => {
                isScanning = true;
                toggleBtn.querySelector('span:last-child').textContent = "Hentikan Kamera";
            }).catch(err => {
                Swal.fire('Kamera Tidak Dapat Diumpankan', 'Pastikan Anda memberikan izin akses kamera.', 'error');
            });
        }
    });
</script>
@endpush
@endsection
