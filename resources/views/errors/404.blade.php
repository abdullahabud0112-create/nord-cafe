<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NORD CAFE - Halaman Tidak Ditemukan</title>
    <script>
        document.documentElement.classList.add('dark');
        document.documentElement.style.colorScheme = 'dark';
        localStorage.removeItem('nord_theme');
    </script>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #121212; color: #F1EDE7; font-family: ui-sans-serif, system-ui, sans-serif; text-align: center; padding: 24px; }
        .box { max-width: 420px; }
        .code { font-size: 72px; font-weight: 800; letter-spacing: -2px; color: #D97742; margin: 0; }
        h1 { font-size: 20px; margin: 8px 0 4px; }
        p { color: #A9A29B; font-size: 15px; line-height: 1.6; margin: 0 0 24px; }
        a { display: inline-block; background: #D97742; color: #121212; text-decoration: none; font-weight: 700; padding: 12px 28px; border-radius: 999px; }
        a:hover { filter: brightness(1.08); }
    </style>
</head>
<body>
    <div class="box">
        <p class="code">404</p>
        <h1>Halaman tidak ditemukan</h1>
        <p>Halaman atau pesanan yang kamu cari tidak tersedia. Mungkin sudah dihapus atau tautannya salah.</p>
        <a href="{{ url('/') }}">Kembali ke Katalog</a>
    </div>
</body>
</html>
