<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NORD CAFE')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Material Symbols & Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>

    <!-- Immediate Theme Lock: Dark Mode is permanent (prevents flash of light theme) -->
    <script>
        document.documentElement.classList.add('dark');
        document.documentElement.style.colorScheme = 'dark';
        localStorage.removeItem('nord_theme');
    </script>
    
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "surface-dim": "#DBD4C9",
                      "on-secondary": "#FFFFFF",
                      "surface-bright": "#FCFAF7",
                      "on-secondary-container": "#6B3312",
                      "secondary": "#B4633A",
                      "tertiary-fixed": "#EDE6DC",
                      "status-unpaid": "#7A7268",
                      "secondary-fixed-dim": "#FFCDA9",
                      "on-tertiary-fixed-variant": "#4E463A",
                      "surface-variant": "#EDE7DE",
                      "status-completed": "#0E8A5F",
                      "on-secondary-fixed-variant": "#7A3A14",
                      "background-warm": "#F7F2EC",
                      "inverse-primary": "#CFC8BD",
                      "error-container": "#F9DEDA",
                      "tertiary-fixed-dim": "#D2CCC0",
                      "on-tertiary-container": "#4E463A",
                      "outline": "#8B8377",
                      "surface-container-high": "#EAE4DB",
                      "on-error-container": "#7A241D",
                      "on-surface": "#1C1A17",
                      "surface-container-low": "#F6F2EC",
                      "surface-container-lowest": "#FFFFFF",
                      "on-primary": "#FFFFFF",
                      "inverse-on-surface": "#F4F0EA",
                      "status-processing": "#D98E28",
                      "error": "#B3453C",
                      "on-background": "#1C1A17",
                      "surface": "#FBF9F7",
                      "background": "#F7F2EC",
                      "on-error": "#FFFFFF",
                      "outline-variant": "#D6CEC2",
                      "tertiary": "#6B5B3F",
                      "on-primary-fixed-variant": "#4A3222",
                      "primary-fixed-dim": "#D2CCC0",
                      "surface-tint": "#6E675C",
                      "secondary-fixed": "#FFDCC4",
                      "primary": "#A05B28",
                      "surface-container-highest": "#E4DDD2",
                      "on-tertiary": "#FFFFFF",
                      "secondary-container": "#F3D9C6",
                      "primary-fixed": "#EBE6DD",
                      "on-tertiary-fixed": "#261A0C",
                      "on-primary-container": "#F5E4D4",
                      "surface-container": "#F0EBE4",
                      "on-surface-variant": "#5F594F",
                      "tertiary-container": "#F0E8D4",
                      "on-primary-fixed": "#1C1A17",
                      "on-secondary-fixed": "#3A1A08",
                      "inverse-surface": "#312D28"
              },
              "borderRadius": {
                      "DEFAULT": "0.375rem",
                      "lg": "0.75rem",
                      "xl": "0.875rem",
                      "2xl": "1rem",
                      "full": "9999px"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "unit-xl": "40px",
                      "gutter": "16px",
                      "margin-mobile": "20px",
                      "unit-sm": "8px",
                      "unit-xs": "4px",
                      "unit-lg": "24px",
                      "unit-md": "16px"
              },
              "fontFamily": {
                      "headline-lg": ["'Plus Jakarta Sans'", "Inter", "sans-serif"],
                      "body-lg": ["'Plus Jakarta Sans'", "Inter", "sans-serif"],
                      "label-caps": ["'Plus Jakarta Sans'", "Inter", "sans-serif"],
                      "body-secondary": ["'Plus Jakarta Sans'", "Inter", "sans-serif"],
                      "headline-sm": ["'Plus Jakarta Sans'", "Inter", "sans-serif"],
                      "body-md": ["'Plus Jakarta Sans'", "Inter", "sans-serif"],
                      "headline-md": ["'Plus Jakarta Sans'", "Inter", "sans-serif"]
              },
              "fontSize": {
                      "headline-lg": ["32px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "body-lg": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                      "label-caps": ["12px", {"lineHeight": "1", "letterSpacing": "0.05em", "fontWeight": "600"}],
                      "body-secondary": ["14px", {"lineHeight": "1.6", "fontWeight": "400"}],
                      "headline-sm": ["20px", {"lineHeight": "1.4", "fontWeight": "600"}],
                      "body-md": ["14px", {"lineHeight": "1.6", "fontWeight": "400"}],
                      "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "700"}]
              }
            },
          },
        }
    </script>

    <!-- SweetAlert2 for notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- QR Code Generator & Scanner Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body {
            background-color: #F7F2EC;
            font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
            min-height: max(884px, 100dvh);
            -webkit-tap-highlight-color: transparent;
        }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 16px); }
        .pt-safe { padding-top: env(safe-area-inset-top, 0px); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Cashier Desktop Sidebar */
        @media (min-width: 768px) {
            body.cashier-layout .cashier-sidebar { display: flex; }
            body.cashier-layout .cashier-main-content { margin-left: 16rem; }
        }
        .scanner-line {
            animation: scan 2s infinite linear;
        }
        @keyframes scan {
            0% { transform: translateY(0); }
            50% { transform: translateY(200px); }
            100% { transform: translateY(0); }
        }

        /* Transparent Glassmorphism Cart Toast */
        .swal2-container.swal2-top {
            top: 24px !important;
            z-index: 99999 !important;
        }
        .nord-transparent-toast {
            background: rgba(30, 26, 22, 0.88) !important;
            backdrop-filter: blur(16px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 9999px !important;
            box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.35) !important;
            color: #FFFFFF !important;
            padding: 8px 18px 8px 14px !important;
            display: inline-flex !important;
            align-items: center !important;
        }
        .nord-transparent-toast .swal2-title {
            color: #FFFFFF !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif !important;
            letter-spacing: -0.01em !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .nord-transparent-toast .swal2-icon {
            border: none !important;
            background: transparent !important;
            margin: 0 8px 0 0 !important;
            width: auto !important;
            height: auto !important;
            box-shadow: none !important;
        }
        .nord-transparent-toast .swal2-icon .swal2-success-circular-line-left,
        .nord-transparent-toast .swal2-icon .swal2-success-circular-line-right,
        .nord-transparent-toast .swal2-icon .swal2-success-fix {
            display: none !important;
        }

        /* ============ Shared Components ============ */
        /* Primary CTA — warm amber/espresso gradient with tactile feedback */
        .btn-cta {
            background: linear-gradient(135deg, #B4672F 0%, #8F4F22 100%);
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 0.875rem;
            box-shadow: 0 8px 20px -6px rgba(143, 79, 34, 0.45);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }
        .btn-cta:hover { filter: brightness(1.06); box-shadow: 0 12px 26px -6px rgba(143, 79, 34, 0.55); }
        .btn-cta:active { transform: scale(0.98); box-shadow: 0 4px 12px -4px rgba(143, 79, 34, 0.4); }
        .btn-cta:disabled { opacity: 0.7; cursor: not-allowed; }

        /* Success CTA — deep emerald, clear contrast */
        .btn-cta-success {
            background: linear-gradient(135deg, #12A573 0%, #0B7A56 100%);
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 0.875rem;
            box-shadow: 0 8px 20px -6px rgba(11, 122, 86, 0.4);
            transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
        }
        .btn-cta-success:hover { filter: brightness(1.07); box-shadow: 0 12px 26px -6px rgba(11, 122, 86, 0.5); }
        .btn-cta-success:active { transform: scale(0.98); }

        /* Soft secondary button */
        .btn-soft {
            background: var(--btn-soft-bg, rgba(255, 255, 255, 0.7));
            border: 1px solid rgba(28, 26, 23, 0.1);
            color: #1C1A17;
            font-weight: 600;
            border-radius: 0.875rem;
            transition: background 0.18s ease, transform 0.18s ease, border-color 0.18s ease;
        }
        .btn-soft:hover { background: rgba(160, 91, 40, 0.08); border-color: rgba(160, 91, 40, 0.3); }
        .btn-soft:active { transform: scale(0.98); }

        /* Outline/ghost secondary button — e.g. "Tambah Pesanan" next to Checkout */
        .btn-outline {
            background: transparent;
            border: 1.5px solid rgba(160, 91, 40, 0.4);
            color: #A05B28;
            font-weight: 600;
            border-radius: 0.875rem;
            transition: background 0.18s ease, transform 0.18s ease, border-color 0.18s ease;
        }
        .btn-outline:hover { background: rgba(160, 91, 40, 0.08); border-color: rgba(160, 91, 40, 0.65); }
        .btn-outline:active { transform: scale(0.98); }
        html.dark .btn-outline {
            border-color: rgba(227, 161, 92, 0.45);
            color: #E3A15C;
        }
        html.dark .btn-outline:hover { background: rgba(227, 161, 92, 0.1); border-color: rgba(227, 161, 92, 0.7); }

        /* Round icon back button — top-left corner nav (checkout / QR ticket) */
        .btn-icon-back {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(28, 26, 23, 0.1);
            color: #1C1A17;
            transition: background 0.18s ease, transform 0.18s ease, border-color 0.18s ease;
        }
        .btn-icon-back:hover { background: rgba(160, 91, 40, 0.08); border-color: rgba(160, 91, 40, 0.3); }
        .btn-icon-back:active { transform: scale(0.94); }
        html.dark .btn-icon-back {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.12);
            color: #F2EFE9;
        }
        html.dark .btn-icon-back:hover {
            background: rgba(227, 161, 92, 0.12);
            border-color: rgba(227, 161, 92, 0.35);
        }

        /* Soft card with glassy hover */
        .card-soft {
            background: #FFFFFF;
            border: 1px solid rgba(28, 26, 23, 0.06);
            border-radius: 1rem;
            box-shadow: 0 4px 16px -6px rgba(60, 40, 20, 0.08);
        }
        .card-hover { transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease; }
        .card-hover:hover {
            transform: translateY(-3px);
            border-color: rgba(160, 91, 40, 0.22);
            box-shadow: 0 18px 40px -14px rgba(60, 40, 20, 0.22);
        }

        /* Subtle divider */
        .divider-soft { border: 0; border-top: 1px solid rgba(28, 26, 23, 0.08); }

        /* Status pills — color-coded, modern rounded */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            border: 1px solid transparent;
            white-space: nowrap;
            line-height: 1.4;
        }
        .status-pill .pill-dot {
            width: 6px; height: 6px; border-radius: 9999px;
            background: currentColor; flex-shrink: 0;
        }
        .pill-unpaid { background: rgba(122, 114, 104, 0.12); color: #6E665C; border-color: rgba(122, 114, 104, 0.25); }
        .pill-paid { background: rgba(224, 161, 92, 0.16); color: #96561B; border-color: rgba(224, 161, 92, 0.35); }
        .pill-preparing { background: rgba(217, 142, 40, 0.15); color: #A0610D; border-color: rgba(217, 142, 40, 0.35); }
        .pill-done { background: rgba(14, 138, 95, 0.12); color: #0B6E4B; border-color: rgba(14, 138, 95, 0.3); }

        /* Pastel product badges */
        .badge-soft { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 9999px; font-size: 10px; font-weight: 700; letter-spacing: 0.03em; line-height: 1.5; }
        .badge-rose { background: rgba(237, 168, 156, 0.95); color: #6A2418; }

        /* ============ Dark Mode Theme ============ */
        html.dark body {
            background-color: #121212 !important;
            color: #F2EFE9 !important;
        }
        html.dark header,
        html.dark nav.fixed,
        html.dark aside.cashier-sidebar {
            background-color: rgba(23, 23, 23, 0.92) !important;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-color: rgba(255, 255, 255, 0.06) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.45) !important;
        }
        html.dark .bg-surface-container-lowest {
            background-color: #1E1E1E !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
            color: #F2EFE9 !important;
        }
        html.dark .bg-surface-container-low { background-color: #232323 !important; color: #F2EFE9 !important; }
        html.dark .bg-surface-container { background-color: #262626 !important; color: #F2EFE9 !important; }
        html.dark .bg-surface-container-high { background-color: #2A2A2A !important; color: #F2EFE9 !important; }
        html.dark .bg-surface-container-highest { background-color: #303030 !important; color: #F2EFE9 !important; }
        html.dark .bg-surface-variant {
            background-color: #2E2E2E !important;
            color: #F2EFE9 !important;
        }
        html.dark .bg-background-warm { background-color: #121212 !important; }
        html.dark .card-soft {
            background: #1E1E1E;
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: 0 4px 18px -8px rgba(0, 0, 0, 0.6);
        }
        html.dark .card-hover:hover {
            border-color: rgba(227, 161, 92, 0.3);
            box-shadow: 0 20px 44px -16px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.04);
            background: rgba(36, 34, 31, 0.88);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        html.dark .divider-soft { border-top-color: rgba(255, 255, 255, 0.08); }
        html.dark .btn-cta {
            background: linear-gradient(135deg, #E8AC6B 0%, #D08B45 100%);
            color: #241505;
            box-shadow: 0 8px 22px -6px rgba(208, 139, 69, 0.35);
        }
        html.dark .btn-cta:hover { box-shadow: 0 12px 28px -6px rgba(208, 139, 69, 0.45); }
        html.dark .btn-cta-success {
            background: linear-gradient(135deg, #1CB884 0%, #0F8F66 100%);
            color: #04231A;
            box-shadow: 0 8px 22px -6px rgba(28, 184, 132, 0.3);
        }
        html.dark .btn-soft {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.1);
            color: #F2EFE9;
        }
        html.dark .btn-soft:hover { background: rgba(227, 161, 92, 0.12); border-color: rgba(227, 161, 92, 0.35); }

        html.dark .pill-unpaid { background: rgba(255, 255, 255, 0.07); color: #A8A096; border-color: rgba(255, 255, 255, 0.12); }
        html.dark .pill-paid { background: rgba(227, 161, 92, 0.14); color: #EBB87D; border-color: rgba(227, 161, 92, 0.32); }
        html.dark .pill-preparing { background: rgba(232, 162, 61, 0.14); color: #F1C078; border-color: rgba(232, 162, 61, 0.32); }
        html.dark .pill-done { background: rgba(58, 199, 150, 0.12); color: #4FD6A4; border-color: rgba(58, 199, 150, 0.3); }
        html.dark .badge-rose { background: rgba(230, 150, 136, 0.9); color: #3F170F; }

        html.dark .text-on-surface,
        html.dark h1, html.dark h2, html.dark h3, html.dark h4 {
            color: #F2EFE9 !important;
        }
        html.dark .text-on-surface-variant,
        html.dark .text-status-unpaid,
        html.dark .text-on-tertiary-fixed-variant {
            color: #A29A8F !important;
        }
        html.dark .text-status-completed { color: #4FD6A4 !important; }
        html.dark .text-status-processing { color: #F1C078 !important; }
        html.dark .text-outline { color: #6B655C !important; }
        html.dark .border-on-surface\/5,
        html.dark .border-on-surface\/10,
        html.dark .border-outline-variant\/40,
        html.dark .border-outline-variant\/30,
        html.dark .border-outline-variant\/20,
        html.dark .border-outline\/20 {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark hr { border-color: rgba(255, 255, 255, 0.08) !important; }
        html.dark input,
        html.dark select,
        html.dark textarea {
            background-color: #242424 !important;
            color: #FFFFFF !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        html.dark input::placeholder {
            color: #85807A !important;
        }
        html.dark .bg-[#18181b] {
            background-color: #1E1E1E !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark .bg-primary {
            background-color: #E3A15C !important;
            color: #221505 !important;
        }
        html.dark .text-primary { color: #E3A15C !important; }
        html.dark .text-on-primary { color: #221505 !important; }
        html.dark .bg-zinc-800\/80 {
            background-color: #2A2A2A !important;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }
        html.dark .border-primary\/20 { border-color: rgba(227, 161, 92, 0.3) !important; }
        html.dark .bg-primary\/5 { background-color: rgba(227, 161, 92, 0.1) !important; }
        html.dark .shadow-\[0px_4px_12px_rgba\(26\,26\,26\,0\.05\)\] {
            box-shadow: 0 4px 18px -6px rgba(0, 0, 0, 0.55) !important;
        }
        html.dark ::-webkit-scrollbar-track { background: #121212; }
        html.dark ::-webkit-scrollbar-thumb { background: #3A3733; }
        html.dark ::-webkit-scrollbar-thumb:hover { background: #E3A15C; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CFC6B8; border-radius: 8px; }
        ::-webkit-scrollbar-thumb:hover { background: #A05B28; }

        /* ============ Motion System: smooth, warm, professional ============ */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        :root {
            --ease-out-soft: cubic-bezier(0.22, 1, 0.36, 1);
            --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Page transition — gentle fade-in on every navigation (opacity-only so
           position:fixed children inside main keep anchoring to the viewport) */
        body > main {
            animation: nordPageIn 0.45s ease both;
        }
        @keyframes nordPageIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Generic entrance for card lists only (staggered via inline animation-delay).
           fill-mode backwards: shows start state during delay, then releases
           transform/opacity back to base styles so hover-lift still works. */
        .animate-in-soft {
            animation: nordSoftIn 0.4s var(--ease-out-soft) backwards;
        }
        @keyframes nordSoftIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Fade-in for cart/checkout list rows — first paint only (no slide to
           avoid horizontal jitter when rows re-render on qty change) */
        .list-item-in {
            animation: nordListIn 0.3s var(--ease-out-soft) backwards;
        }
        @keyframes nordListIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Shimmer skeleton while product images load (no blank flashes) */
        .nord-img {
            background-color: #E9E1D6;
            background-image: linear-gradient(100deg, rgba(255,255,255,0) 20%, rgba(255,255,255,0.65) 50%, rgba(255,255,255,0) 80%);
            background-size: 200% 100%;
            background-repeat: no-repeat;
            animation: nordShimmer 1.3s ease-in-out infinite;
            color: transparent;
            transition: background-color 0.4s ease;
        }
        .nord-img.is-loaded {
            background-image: none;
            background-color: transparent;
            color: inherit;
            animation: none;
        }
        html.dark .nord-img { background-color: #2A2A2A; }
        @keyframes nordShimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Ripple ink on CTA / soft buttons (sits above button background, behind label) */
        .btn-cta, .btn-cta-success, .btn-soft, .btn-outline { position: relative; overflow: hidden; isolation: isolate; }
        .ripple-ink {
            position: absolute;
            border-radius: 9999px;
            transform: scale(0);
            background: rgba(255, 255, 255, 0.4);
            pointer-events: none;
            animation: nordRipple 0.6s ease-out forwards;
            z-index: -1;
        }
        html.dark .ripple-ink { background: rgba(0, 0, 0, 0.28); }
        @keyframes nordRipple {
            to { transform: scale(4); opacity: 0; }
        }

        /* Modal open animation — works with existing `hidden` toggle logic */
        #addModal:not(.hidden),
        #editModal:not(.hidden) {
            animation: nordFadeIn 0.25s ease both;
        }
        #addModal:not(.hidden) > div,
        #editModal:not(.hidden) > div {
            animation: nordModalIn 0.4s var(--ease-out-soft) both;
        }
        /* (modal panel has no hover transforms — `both` keeps final state stable) */
        @keyframes nordFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes nordModalIn {
            from { opacity: 0; transform: translateY(26px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Cart badge pop when count changes */
        .badge-pop { animation: nordPop 0.4s var(--ease-spring); }
        @keyframes nordPop {
            0% { transform: scale(0.5); }
            60% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        /* Status pills — smooth color crossfade on state change + live pulse */
        .status-pill {
            transition: background-color 0.45s ease, color 0.45s ease, border-color 0.45s ease, box-shadow 0.45s ease;
        }
        .pill-preparing .pill-dot,
        .pill-paid .pill-dot {
            animation: nordPulse 1.7s ease-in-out infinite;
        }
        @keyframes nordPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.45; transform: scale(1.5); }
        }

        /* Timeline (status tracking) — staggered entrance */
        .timeline-item { animation: nordSoftIn 0.4s var(--ease-out-soft) backwards; }
        .timeline-item:nth-child(1) { animation-delay: 0.05s; }
        .timeline-item:nth-child(2) { animation-delay: 0.12s; }
        .timeline-item:nth-child(3) { animation-delay: 0.19s; }
        .timeline-item:nth-child(4) { animation-delay: 0.26s; }
        .timeline-item .w-6.h-6 { transition: transform 0.35s var(--ease-spring), box-shadow 0.35s ease; }

        /* Quantity stepper feedback */
        .qty-pop { animation: nordPop 0.28s var(--ease-spring); display: inline-block; }

        /* Clean focus ring (warm amber, accessible) */
        :focus-visible {
            outline: 2px solid #E3A15C;
            outline-offset: 2px;
        }

        /* Typography polish */
        h1, h2, h3 { text-wrap: balance; }

        /* Subtle soft divider used across checkout / summary */
        .divider-fine { border: 0; border-top: 1px solid rgba(28, 26, 23, 0.07); }
        html.dark .divider-fine { border-top-color: rgba(255, 255, 255, 0.07); }
    </style>
</head>

@php
    $isCashier = request()->routeIs('cashier.*');
    $hideBottomNav = request()->routeIs('customer.productDetail')
        || request()->routeIs('customer.checkout')
        || request()->routeIs('customer.ticket')
        || request()->routeIs('cashier.orderDetail')
        || request()->routeIs('cashier.editStatus');
    $hideTopHeader = request()->routeIs('customer.productDetail')
        || request()->routeIs('customer.ticket')
        || request()->routeIs('cashier.orderDetail');
@endphp

<body class="bg-background-warm font-body-md text-on-surface antialiased flex flex-col min-h-screen relative {{ $isCashier ? 'cashier-layout' : '' }} {{ !$hideBottomNav ? 'pb-24' : '' }}">

    {{-- Top AppBar Header (hidden on pages with custom header) --}}
    @unless($hideTopHeader)
    <header class="fixed top-0 left-0 right-0 z-40 bg-surface-container-lowest shadow-[0px_4px_12px_rgba(26,26,26,0.05)] w-full border-b border-on-surface/5">
        <div class="flex justify-between items-center w-full px-margin-mobile h-16 max-w-7xl mx-auto {{ $isCashier ? 'md:ml-64 md:max-w-none' : '' }}">
            
            @if($isCashier)
                <!-- Cashier Brand Header -->
                <a href="{{ route('cashier.incomingOrders') }}" class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-2xl" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
                    <h1 class="text-headline-md font-headline-md font-bold text-on-surface tracking-tight">NORD CAFE</h1>
                </a>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:flex items-center gap-1 text-xs font-medium text-status-unpaid">
                        <span class="material-symbols-outlined text-sm">badge</span> Kasir
                    </span>
                    <a href="{{ route('cashier.menuManager') }}" class="hidden sm:flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border border-on-surface/10 hover:bg-surface-variant/20 text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-sm">restaurant_menu</span> Kelola Menu
                    </a>
                    <div class="flex items-center justify-center w-9 h-9 rounded-full bg-surface-variant border border-on-surface/10 text-label-caps font-label-caps text-on-surface font-bold">
                        A2
                    </div>
                </div>
            @else
                <!-- Customer Brand Header -->
                <a href="{{ route('customer.catalog') }}" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-2xl" data-icon="local_cafe">local_cafe</span>
                    <h1 class="text-headline-md font-headline-md font-bold text-on-surface">NORD CAFE</h1>
                </a>

                <!-- Desktop / Header Actions -->
                <div class="flex items-center gap-2.5">
                    <nav class="hidden md:flex items-center gap-2">
                        <a href="{{ route('customer.catalog') }}" class="px-3.5 py-1.5 rounded-full text-body-md font-body-md font-semibold {{ request()->routeIs('customer.catalog') ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-variant/20' }} transition-colors">
                            Menu
                        </a>
                        <a href="{{ route('customer.cart') }}" class="relative px-3.5 py-1.5 rounded-full text-body-md font-body-md font-semibold {{ request()->routeIs('customer.cart') ? 'bg-primary text-on-primary' : 'text-on-surface-variant hover:bg-surface-variant/20' }} transition-colors">
                            Keranjang
                            <span id="nav-cart-badge-desktop" class="hidden absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full bg-secondary text-on-secondary text-[10px] font-bold">0</span>
                        </a>
                    </nav>

                    <a href="{{ route('customer.cart') }}" class="md:hidden relative w-9 h-9 rounded-full bg-surface-variant flex items-center justify-center text-on-surface cursor-pointer hover:opacity-80 transition-opacity">
                        <span class="material-symbols-outlined text-xl">shopping_basket</span>
                        <span id="nav-cart-badge-mobile" class="hidden absolute -top-1 -right-1 w-4 h-4 rounded-full bg-secondary text-on-secondary text-[10px] font-bold flex items-center justify-center">0</span>
                    </a>
                </div>
            @endif

        </div>
    </header>
    @endunless

    {{-- Cashier Desktop Sidebar --}}
    @if($isCashier)
    @php
        $sidebarScanActive = request()->routeIs('cashier.scanBarcode');
        $sidebarOrdersActive = request()->routeIs('cashier.incomingOrders') && request('status') !== 'completed';
        $sidebarHistoryActive = request('status') === 'completed';
        $sidebarMenuActive = request()->routeIs('cashier.menuManager');
        $fillStyle = "font-variation-settings: 'FILL' 1;";
    @endphp
    <aside class="cashier-sidebar hidden flex-col w-64 bg-surface-container-lowest h-screen fixed left-0 top-0 border-r border-on-surface/5 pt-20 px-4 gap-1 z-30 shadow-[4px_0_12px_rgba(26,26,26,0.02)]">
        <a href="{{ route('cashier.scanBarcode') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors w-full text-left relative {{ $sidebarScanActive ? 'text-primary bg-primary/5 font-bold' : 'text-on-surface-variant hover:bg-surface-variant/20 font-semibold' }}">
            @if($sidebarScanActive)<div class="absolute left-0 top-2 bottom-2 w-1 bg-primary rounded-r-md"></div>@endif
            <span class="material-symbols-outlined" @if($sidebarScanActive) style="{{ $fillStyle }}" @endif>qr_code_scanner</span>
            <span class="font-body-md">Pindai</span>
        </a>
        <a href="{{ route('cashier.incomingOrders') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors w-full text-left relative {{ $sidebarOrdersActive ? 'text-primary bg-primary/5 font-bold' : 'text-on-surface-variant hover:bg-surface-variant/20 font-semibold' }}">
            @if($sidebarOrdersActive)<div class="absolute left-0 top-2 bottom-2 w-1 bg-primary rounded-r-md"></div>@endif
            <span class="material-symbols-outlined" @if($sidebarOrdersActive) style="{{ $fillStyle }}" @endif>pending_actions</span>
            <span class="font-body-md">Pesanan Masuk</span>
        </a>
        <a href="{{ route('cashier.incomingOrders', ['status' => 'completed']) }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors w-full text-left relative {{ $sidebarHistoryActive ? 'text-primary bg-primary/5 font-bold' : 'text-on-surface-variant hover:bg-surface-variant/20 font-semibold' }}">
            @if($sidebarHistoryActive)<div class="absolute left-0 top-2 bottom-2 w-1 bg-primary rounded-r-md"></div>@endif
            <span class="material-symbols-outlined" @if($sidebarHistoryActive) style="{{ $fillStyle }}" @endif>history</span>
            <span class="font-body-md">Riwayat</span>
        </a>
        
        <div class="mt-4 border-t border-on-surface/5 pt-4">
            <a href="{{ route('cashier.menuManager') }}" 
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors w-full text-left relative {{ $sidebarMenuActive ? 'text-primary bg-primary/5 font-bold' : 'text-on-surface-variant hover:bg-surface-variant/20 font-semibold' }}">
                @if($sidebarMenuActive)<div class="absolute left-0 top-2 bottom-2 w-1 bg-primary rounded-r-md"></div>@endif
                <span class="material-symbols-outlined" @if($sidebarMenuActive) style="{{ $fillStyle }}" @endif>restaurant_menu</span>
                <span class="font-body-md">Kelola Menu</span>
            </a>
        </div>
    </aside>
    @endif


    <!-- Main Content Container -->
    <main class="flex-grow {{ !$hideTopHeader ? 'pt-20' : '' }} px-margin-mobile max-w-7xl mx-auto w-full {{ $isCashier ? 'md:ml-64 md:max-w-none md:px-8' : '' }}">
        @yield('content')
    </main>

    {{-- Bottom Navigation Bar (Mobile) — hidden on specific pages --}}
    @unless($hideBottomNav)
    @php
        $navFill = "font-variation-settings: 'FILL' 1;";
        $navScanActive = request()->routeIs('cashier.scanBarcode');
        $navOrdersActive = request()->routeIs('cashier.incomingOrders') && request('status') !== 'completed';
        $navHistoryActive = request('status') === 'completed';
        $navCatalogActive = request()->routeIs('customer.catalog');
        $navCartActive = request()->routeIs('customer.cart');
        $navStatusActive = request()->routeIs('customer.trackStatus');
    @endphp
    <nav class="fixed bottom-0 left-0 w-full z-50 flex justify-around items-center px-4 py-3 pb-safe bg-surface-container-lowest border-t border-on-surface/10 rounded-t-xl shadow-[0px_-4px_12px_rgba(26,26,26,0.05)] md:hidden">
        @if($isCashier)
            <a href="{{ route('cashier.scanBarcode') }}" class="flex flex-col items-center justify-center {{ $navScanActive ? 'text-primary font-bold border-t-2 border-primary pt-1' : 'text-on-surface-variant opacity-60' }} text-label-caps font-label-caps w-20 transition-all">
                <span class="material-symbols-outlined text-[24px] mb-1" @if($navScanActive) style="{{ $navFill }}" @endif>qr_code_scanner</span>
                <span>Pindai</span>
            </a>
            <a href="{{ route('cashier.incomingOrders') }}" class="flex flex-col items-center justify-center {{ $navOrdersActive ? 'text-primary font-bold border-t-2 border-primary pt-1' : 'text-on-surface-variant opacity-60' }} text-label-caps font-label-caps w-24 transition-all relative">
                <span class="material-symbols-outlined text-[24px] mb-1" @if($navOrdersActive) style="{{ $navFill }}" @endif>pending_actions</span>
                <span class="whitespace-nowrap">Pesanan Masuk</span>
            </a>
            <a href="{{ route('cashier.incomingOrders', ['status' => 'completed']) }}" class="flex flex-col items-center justify-center {{ $navHistoryActive ? 'text-primary font-bold border-t-2 border-primary pt-1' : 'text-on-surface-variant opacity-60' }} text-label-caps font-label-caps w-20 transition-all">
                <span class="material-symbols-outlined text-[24px] mb-1" @if($navHistoryActive) style="{{ $navFill }}" @endif>history</span>
                <span>Riwayat</span>
            </a>
        @else
            <a href="{{ route('customer.catalog') }}" class="flex flex-col items-center justify-center text-on-surface-variant opacity-60 hover:text-primary transition-colors text-label-caps font-label-caps w-16 group">
                <span class="material-symbols-outlined text-[24px] mb-1 group-hover:scale-110 transition-transform">home</span>
                <span>Beranda</span>
            </a>
            <a href="{{ route('customer.catalog') }}" class="flex flex-col items-center justify-center {{ $navCatalogActive ? 'text-primary scale-110' : 'text-on-surface-variant opacity-60' }} text-label-caps font-label-caps w-16 group">
                <span class="material-symbols-outlined text-[24px] mb-1" @if($navCatalogActive) style="{{ $navFill }}" @endif>restaurant_menu</span>
                <span class="{{ $navCatalogActive ? 'font-bold' : '' }}">Menu</span>
            </a>
            <a href="{{ route('customer.cart') }}" class="flex flex-col items-center justify-center {{ $navCartActive ? 'text-primary scale-110' : 'text-on-surface-variant opacity-60' }} text-label-caps font-label-caps w-16 group relative">
                <span class="material-symbols-outlined text-[24px] mb-1" @if($navCartActive) style="{{ $navFill }}" @endif>shopping_basket</span>
                <span class="{{ $navCartActive ? 'font-bold' : '' }}">Keranjang</span>
                <span id="bottom-cart-badge" class="hidden absolute top-0 right-3 w-4 h-4 rounded-full bg-secondary text-on-secondary text-[9px] font-bold flex items-center justify-center">0</span>
            </a>
            <a id="customer-status-tab" href="{{ route('customer.trackStatus') }}" class="flex flex-col items-center justify-center {{ $navStatusActive ? 'text-primary scale-110' : 'text-on-surface-variant opacity-60' }} text-label-caps font-label-caps w-16 group">
                <span class="material-symbols-outlined text-[24px] mb-1" @if($navStatusActive) style="{{ $navFill }}" @endif>receipt_long</span>
                <span class="{{ $navStatusActive ? 'font-bold' : '' }}">Status</span>
            </a>
        @endif
    </nav>
    @endunless


    <!-- Global LocalStorage Cart Management Script -->
    <script>
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
                Swal.fire({
                    toast: true,
                    position: 'top',
                    iconHtml: '<span class="material-symbols-outlined text-emerald-400 text-xl font-bold flex items-center justify-center">check_circle</span>',
                    title: `${product.name} dimasukkan ke keranjang!`,
                    showConfirmButton: false,
                    timer: 2000,
                    customClass: {
                        popup: 'nord-transparent-toast',
                        icon: 'border-0 m-0 p-0 shadow-none bg-transparent'
                    }
                });
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
                const desktopBadge = document.getElementById('nav-cart-badge-desktop');
                const mobileBadge = document.getElementById('nav-cart-badge-mobile');
                const bottomBadge = document.getElementById('bottom-cart-badge');

                [desktopBadge, mobileBadge, bottomBadge].forEach(badge => {
                    if (badge) {
                        if (totalCount > 0) {
                            badge.textContent = totalCount;
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                });
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            NordCart.updateBadge();
            const lastOrder = localStorage.getItem('nord_last_order');
            const statusTab = document.getElementById('customer-status-tab');
            if (lastOrder && statusTab) {
                statusTab.href = '/track/' + lastOrder;
            }

            /* Ripple feedback on primary/secondary buttons */
            document.addEventListener('pointerdown', (e) => {
                const btn = e.target.closest('.btn-cta, .btn-cta-success, .btn-soft, .btn-outline');
                if (!btn || btn.disabled) return;
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const ink = document.createElement('span');
                ink.className = 'ripple-ink';
                ink.style.width = ink.style.height = size + 'px';
                ink.style.left = (e.clientX - rect.left - size / 2) + 'px';
                ink.style.top = (e.clientY - rect.top - size / 2) + 'px';
                btn.appendChild(ink);
                setTimeout(() => ink.remove(), 650);
            });

            /* Shimmer skeleton until every image finishes loading (incl. dynamic renders) */
            const markLoaded = (img) => img.classList.add('is-loaded');
            const bindImg = (img) => {
                if (img.dataset.nordBound) return;
                img.dataset.nordBound = '1';
                /* Skip QR codes / explicitly excluded images — shimmer flickers on them */
                if (img.closest('#qrcode-container, [data-no-shimmer]')) return;
                img.classList.add('nord-img');
                if (img.complete && img.naturalWidth > 0) {
                    markLoaded(img);
                } else {
                    img.addEventListener('load', () => markLoaded(img), { once: true });
                    img.addEventListener('error', () => setTimeout(() => markLoaded(img), 120), { once: true });
                    setTimeout(() => markLoaded(img), 8000);
                }
            };
            document.querySelectorAll('img').forEach(bindImg);
            new MutationObserver((mutations) => {
                mutations.forEach(m => m.addedNodes.forEach(node => {
                    if (node.nodeType !== 1) return;
                    if (node.tagName === 'IMG') bindImg(node);
                    node.querySelectorAll && node.querySelectorAll('img').forEach(bindImg);
                }));
            }).observe(document.body, { childList: true, subtree: true });

            /* Pop cart badges whenever the count updates */
            const originalUpdateBadge = NordCart.updateBadge.bind(NordCart);
            NordCart.updateBadge = function () {
                const before = [document.getElementById('nav-cart-badge-desktop'), document.getElementById('nav-cart-badge-mobile'), document.getElementById('bottom-cart-badge')].map(b => b ? b.textContent : null);
                originalUpdateBadge();
                [document.getElementById('nav-cart-badge-desktop'), document.getElementById('nav-cart-badge-mobile'), document.getElementById('bottom-cart-badge')].forEach((badge, i) => {
                    if (badge && !badge.classList.contains('hidden') && badge.textContent !== before[i]) {
                        badge.classList.remove('badge-pop');
                        void badge.offsetWidth;
                        badge.classList.add('badge-pop');
                    }
                });
            };
        });
    </script>

    @if(session('success') || session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                toast: true,
                position: 'top',
                icon: @json(session('error') ? 'error' : 'success'),
                title: @json(session('error') ?: session('success')),
                showConfirmButton: false,
                timer: 3200,
                timerProgressBar: true,
                customClass: {
                    popup: 'nord-transparent-toast',
                    icon: 'border-0 m-0 p-0 shadow-none bg-transparent'
                }
            });
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
