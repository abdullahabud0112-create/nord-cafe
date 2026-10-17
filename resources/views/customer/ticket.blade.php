@extends('layouts.app')

@section('title', 'NORD CAFE - Tiket Pesanan Digital')

@section('content')
<div class="w-full max-w-md mx-auto py-6 flex flex-col items-center">
    <main class="w-full flex flex-col items-center justify-center px-margin-mobile py-unit-xl">
        <div class="flex flex-col items-center w-full max-w-[360px] animate-in-soft">

            <!-- Back (confirm if still waiting verification) -->
            <div class="w-full flex items-center justify-between mb-5">
                <button type="button"
                        id="btn-ticket-back"
                        class="btn-icon-back"
                        aria-label="Kembali"
                        title="Kembali">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </button>
                <span class="text-label-caps font-label-caps text-status-unpaid uppercase tracking-[0.14em]">Tiket QR</span>
            </div>

            <!-- 1. Status Badge — MENUNGGU VERIFIKASI KASIR -->
            <div id="ticket-badge"
                 data-initial-status="{{ $order->status }}"
                 class="inline-flex items-center gap-2 rounded-full border px-5 py-2 text-xs font-bold uppercase tracking-[0.1em] shadow-sm mb-6
                        @if(in_array($order->status, ['unpaid', 'pending']))
                            bg-[#1C1A17] text-[#F5E6C8] border-[#E0A15C]/40
                        @else
                            bg-[#0E8A5F]/12 text-[#0B6E4B] border-[#0E8A5F]/30
                        @endif">
                <span id="ticket-badge-dot"
                      class="inline-block h-2 w-2 rounded-full @if(in_array($order->status, ['unpaid', 'pending'])) bg-[#E8B54D] animate-pulse @else bg-[#0E8A5F] @endif"></span>
                <span id="ticket-badge-text">
                    @if(in_array($order->status, ['unpaid', 'pending']))
                        Menunggu Verifikasi Kasir
                    @else
                        Pesanan Diproses
                    @endif
                </span>
            </div>

            <!-- Ticket card -->
            <div class="card-soft w-full rounded-2xl overflow-hidden mb-4">
                <!-- 2. Header Tiket -->
                <div class="px-5 pt-5 pb-4 text-center border-b border-dashed border-on-surface/15">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-status-unpaid mb-1">Tiket Pesanan Digital</p>
                    <p class="font-mono text-on-surface font-bold tracking-widest text-lg">{{ $order->order_number }}</p>
                </div>

                <!-- 3. QR container — high contrast white + safe padding; tap = fullscreen zoom -->
                <button type="button"
                        id="btn-qr-zoom"
                        class="flex w-full items-center justify-center p-6 bg-surface-container-low/60 hover:bg-surface-container-low dark:bg-white/5 dark:hover:bg-white/10 transition-colors cursor-zoom-in"
                        aria-label="Perbesar QR Code"
                        title="Ketuk untuk memperbesar QR">
                    <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 shadow-sm ring-1 ring-on-surface/10 dark:ring-white/10">
                        <div id="qrcode-container" class="w-[180px] h-[180px] flex items-center justify-center bg-white" data-no-shimmer></div>
                    </div>
                </button>

                <!-- 4. Teks petunjuk pelanggan -->
                <div class="px-5 py-4 border-t border-dashed border-on-surface/15 text-center">
                    <p class="text-center text-status-unpaid text-body-secondary font-body-secondary leading-relaxed">
                        Tunjukkan barcode ini ke kasir untuk konfirmasi pembayaran dan mulai diproses.
                    </p>
                    <p class="mt-2 inline-flex items-center gap-1 text-[11px] font-semibold text-primary/80">
                        <span class="material-symbols-outlined text-sm">zoom_in</span>
                        Ketuk QR untuk mode layar penuh
                    </p>
                </div>
            </div>

            <!-- 3 (opsional). Panduan langkah kasir -->
            <div class="w-full card-soft rounded-2xl p-4 mb-4">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-status-unpaid mb-3">Langkah Verifikasi Kasir</p>
                <ol class="flex flex-col gap-2.5">
                    <li class="flex gap-3 items-start">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-on-primary text-[10px] font-bold">1</span>
                        <span class="text-xs text-on-surface-variant leading-relaxed">Tunjukkan QR Code ke kasir di meja pembayaran.</span>
                    </li>
                    <li class="flex gap-3 items-start">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-on-primary text-[10px] font-bold">2</span>
                        <span class="text-xs text-on-surface-variant leading-relaxed">Kasir akan melakukan scan pada QR Code untuk mencocokkan item &amp; total tagihan.</span>
                    </li>
                    <li class="flex gap-3 items-start">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-on-primary text-[10px] font-bold">3</span>
                        <span class="text-xs text-on-surface-variant leading-relaxed">Lakukan pembayaran di kasir sesuai metode yang dipilih.</span>
                    </li>
                </ol>
            </div>

            <!-- Realtime hint -->
            <p id="ticket-live-hint" class="text-center text-on-surface-variant/70 text-xs font-medium flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">sensors</span>
                Status diperbarui otomatis — tidak perlu refresh
            </p>
        </div>
    </main>
</div>

<!-- Fullscreen QR zoom — NO backdrop dismiss (anti accidental tap saat di-scan) -->
<div id="qr-fullscreen"
     class="fixed inset-0 z-[80] hidden items-center justify-center bg-black"
     role="dialog"
     aria-modal="true"
     aria-label="QR Code layar penuh"
     data-no-backdrop-dismiss>
    <div class="flex flex-col items-center gap-6 p-6">
        <div class="bg-white dark:bg-[#1E1E1E] dark:border dark:border-white/10 rounded-2xl p-6 shadow-2xl">
            <div id="qrcode-fullscreen" class="w-[min(78vw,320px)] h-[min(78vw,320px)] flex items-center justify-center bg-white" data-no-shimmer></div>
        </div>
        <p class="text-white/90 text-sm font-semibold text-center max-w-xs leading-relaxed">
            Arahkan QR ke scanner kasir.<br>
            <span class="text-white/60 text-xs font-normal">Ketuk tombol tutup untuk keluar.</span>
        </p>
        <button type="button"
                id="btn-qr-close"
                class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 border border-white/25 text-white px-6 py-3 text-sm font-bold transition-colors active:scale-95">
            <span class="material-symbols-outlined text-lg">close</span>
            Tutup
        </button>
    </div>
</div>

<!-- Confirm dialog: leave while still waiting verification -->
<div id="leave-confirm"
     class="fixed inset-0 z-[90] hidden items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="leave-confirm-title">
    <div class="absolute inset-0 bg-black/55" data-leave-cancel></div>
    <div class="relative w-full max-w-sm rounded-2xl card-soft p-5 shadow-2xl">
        <div class="flex items-start gap-3 mb-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#E0A15C]/15 text-[#96561B]">
                <span class="material-symbols-outlined text-xl">warning</span>
            </span>
            <div>
                <h3 id="leave-confirm-title" class="text-sm font-bold text-on-surface">Tutup halaman tiket?</h3>
                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                    Pesanan masih menunggu verifikasi. Yakin ingin menutup halaman ini?
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" id="btn-leave-cancel"
                    class="flex-1 rounded-xl border border-outline/25 bg-transparent px-4 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-variant/20 transition-colors">
                Batal
            </button>
            <button type="button" id="btn-leave-confirm"
                    class="flex-1 rounded-xl bg-[#B4443A] text-white px-4 py-2.5 text-xs font-bold hover:opacity-90 transition-opacity">
                Ya, tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const orderNumber = @json($order->order_number);
    const trackUrl = @json(route('customer.trackStatus', $order->order_number));
    const updateStatusUrl = @json(route('cashier.editStatus', $order->order_number));
    const catalogUrl = @json(route('customer.catalog'));
    const initialStatus = @json($order->status);
    let ticketResolved = ['paid', 'preparing', 'ready', 'completed'].includes(initialStatus);

    function renderQr(targetId, size) {
        const el = document.getElementById(targetId);
        if (!el || typeof QRCode === 'undefined') return;
        el.innerHTML = '';
        new QRCode(el, {
            text: updateStatusUrl,
            width: size,
            height: size,
            colorDark: '#000000',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderQr('qrcode-container', 170);

        // --- Fullscreen QR zoom (tap QR only; backdrop does NOT dismiss) ---
        const fs = document.getElementById('qr-fullscreen');
        const btnZoom = document.getElementById('btn-qr-zoom');
        const btnClose = document.getElementById('btn-qr-close');

        function openQrFullscreen() {
            if (!fs) return;
            fs.classList.remove('hidden');
            fs.classList.add('flex');
            document.body.style.overflow = 'hidden';
            renderQr('qrcode-fullscreen', Math.min(window.innerWidth * 0.78, 320));
        }

        function closeQrFullscreen() {
            if (!fs) return;
            fs.classList.add('hidden');
            fs.classList.remove('flex');
            document.body.style.overflow = '';
        }

        if (btnZoom) btnZoom.addEventListener('click', openQrFullscreen);
        if (btnClose) btnClose.addEventListener('click', closeQrFullscreen);

        // Explicitly ignore clicks on backdrop — anti accidental dismiss while phone handed to cashier
        if (fs) {
            fs.addEventListener('click', (e) => {
                if (e.target === fs) {
                    e.stopPropagation();
                    e.preventDefault();
                }
            });
        }

        // Escape only closes zoom (keyboard, not touch backdrop)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeQrFullscreen();
        });

        // --- Back / leave confirm (only while still waiting verification) ---
        const leaveModal = document.getElementById('leave-confirm');
        const btnBack = document.getElementById('btn-ticket-back');
        const btnCancel = document.getElementById('btn-leave-cancel');
        const btnLeaveOk = document.getElementById('btn-leave-confirm');

        function openLeaveConfirm() {
            if (!leaveModal) return;
            leaveModal.classList.remove('hidden');
            leaveModal.classList.add('flex');
        }

        function closeLeaveConfirm() {
            if (!leaveModal) return;
            leaveModal.classList.add('hidden');
            leaveModal.classList.remove('flex');
        }

        function doLeave() {
            closeLeaveConfirm();
            if (history.length > 1) {
                history.back();
            } else {
                window.location.href = catalogUrl;
            }
        }

        if (btnBack) {
            btnBack.addEventListener('click', () => {
                if (ticketResolved) {
                    doLeave();
                } else {
                    openLeaveConfirm();
                }
            });
        }
        if (btnCancel) btnCancel.addEventListener('click', closeLeaveConfirm);
        if (btnLeaveOk) btnLeaveOk.addEventListener('click', doLeave);
        // Leave modal backdrop MAY dismiss (it's a choice dialog, not scan lock)
        const leaveBackdrop = leaveModal && leaveModal.querySelector('[data-leave-cancel]');
        if (leaveBackdrop) leaveBackdrop.addEventListener('click', closeLeaveConfirm);

        // --- Realtime status polling → "PESANAN DIPROSES" without refresh ---
        const badge = document.getElementById('ticket-badge');
        const badgeText = document.getElementById('ticket-badge-text');
        const badgeDot = document.getElementById('ticket-badge-dot');

        function applyProcessedState() {
            if (ticketResolved || !badge) return;
            ticketResolved = true;
            badge.className = 'inline-flex items-center gap-2 rounded-full border px-5 py-2 text-xs font-bold uppercase tracking-[0.1em] shadow-sm mb-6 bg-[#0E8A5F]/12 text-[#0B6E4B] border-[#0E8A5F]/30';
            if (badgeText) badgeText.textContent = 'Pesanan Diproses';
            if (badgeDot) {
                badgeDot.className = 'inline-block h-2 w-2 rounded-full bg-[#0E8A5F]';
                badgeDot.style.animation = 'none';
            }
            const hint = document.getElementById('ticket-live-hint');
            if (hint) {
                hint.innerHTML = '<span class="material-symbols-outlined text-sm">check_circle</span> Pembayaran diverifikasi kasir — pesanan diproses';
                hint.classList.add('text-[#0B6E4B]', 'font-semibold');
            }
        }

        if (ticketResolved) {
            applyProcessedState();
        }

        setInterval(async () => {
            if (ticketResolved) return;
            try {
                const res = await fetch(`/api/order-status/${encodeURIComponent(orderNumber)}`, { cache: 'no-store' });
                if (!res.ok) return;
                const data = await res.json();
                if (data.is_paid || data.is_preparing || inProcessing(data.status)) {
                    applyProcessedState();
                    setTimeout(() => {
                        if (!window.location.pathname.includes('/track/')) {
                            window.location.href = trackUrl;
                        }
                    }, 1500);
                }
            } catch (e) {}
        }, 3000);

        function inProcessing(status) {
            return ['preparing', 'ready', 'completed'].includes(status);
        }
    });
</script>
@endpush
@endsection
