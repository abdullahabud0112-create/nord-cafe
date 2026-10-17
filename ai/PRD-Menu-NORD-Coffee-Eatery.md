# Product Requirements Document (PRD)
# Aplikasi/Website Menu Digital — NORD Coffee & Eatery

| | |
|---|---|
| **Produk** | Menu Digital NORD Coffee & Eatery |
| **Versi Dokumen** | 1.0 (Draft) |
| **Tanggal** | 6 September 2026 |
| **Status Gambar** | Dummy/placeholder (akan diganti foto asli pada tahap selanjutnya) |
| **Sumber Data Menu** | Buku Menu NORD Coffee & Eatery (Since 2022) — nordcafe.id |

---

## 1. Latar Belakang

NORD Coffee & Eatery saat ini memiliki buku menu cetak (fisik) yang berisi kategori minuman dan makanan. Untuk meningkatkan pengalaman pelanggan, mempermudah update harga/menu, dan mendukung operasional (pemesanan, QR menu di meja, tampilan di kasir), dibutuhkan **menu digital** dalam bentuk aplikasi web (web app) yang dapat diakses via QR code atau tautan langsung.

Pada tahap awal ini, fokus PRD adalah **struktur data menu yang akurat** sesuai buku menu fisik. Aset visual (foto produk) masih menggunakan **gambar dummy/placeholder**, karena foto asli beresolusi tinggi akan disiapkan menyusul oleh tim marketing.

---

## 2. Tujuan Produk

1. Menyediakan katalog menu digital yang **100% akurat** terhadap buku menu fisik (nama item & harga).
2. Memudahkan pelanggan menelusuri menu berdasarkan kategori (Minuman / Makanan) dan sub-kategori.
3. Mempermudah tim NORD melakukan **update harga/menu** tanpa perlu cetak ulang buku menu.
4. Menyiapkan struktur data yang siap diintegrasikan dengan foto asli & (opsional) sistem pemesanan di iterasi berikutnya.

## 3. Target Pengguna

- **Pelanggan** yang scan QR di meja atau membuka link menu sebelum/saat memesan.
- **Staf kasir/pelayan** yang butuh referensi cepat harga & nama menu.
- **Admin/Owner NORD** yang mengelola konten menu (tambah/ubah/hapus item & harga).

## 4. Ruang Lingkup (Scope) — Versi 1.0

### Termasuk (In-Scope)
- Halaman **Menu Minuman** (Non Coffee, Yaqult Series, Tea Series, Basic Coffee, By Glasses, By Bottle, Manual Brew, Signature).
- Halaman **Menu Makanan** (Premium Steak, Steak Original, Steak Crispy, Rice Bowl, Baso Aci, Nasi Goreng, Ayam Goreng, Pasta Series, Snack, Burger, Indomie Series, Salad Series).
- Navigasi kategori & sub-kategori.
- Tampilan harga dalam format "K" (ribuan Rupiah) sesuai buku menu asli.
- Placeholder gambar dummy untuk tiap kategori/produk.
- Halaman cover "Buku Menu" dengan branding NORD.
- Mode admin sederhana (CRUD) untuk update menu — opsional, tahap 2.

### Tidak Termasuk (Out-of-Scope) — Versi 1.0
- Foto produk asli (menyusul).
- Sistem pemesanan online / keranjang belanja / pembayaran.
- Multi-bahasa (saat ini hanya Bahasa Indonesia).
- Program loyalitas/promo otomatis.

## 5. Fitur Utama

| # | Fitur | Deskripsi | Prioritas |
|---|---|---|---|
| 1 | Landing/Cover Menu | Menampilkan logo NORD, tagline "Coffee & Eatery Since 2022", tombol masuk ke Menu Minuman/Makanan | Must |
| 2 | Navigasi Kategori | Tab/menu untuk berpindah antar kategori & sub-kategori | Must |
| 3 | Daftar Item Menu | Nama item, harga, gambar dummy, per sub-kategori | Must |
| 4 | Pencarian Menu | Cari item berdasarkan nama | Should |
| 5 | Highlight Signature/Best Seller | Badge khusus untuk item unggulan (mis. Signature Series) | Could |
| 6 | Panel Admin (CRUD Menu) | Tambah/edit/hapus item, ubah harga, ubah status tersedia/habis | Should |
| 7 | Link Footer "Nordcafe.id" | Tautan ke website resmi | Must |
| 8 | Responsive Design | Optimal di HP (mode scan QR di meja) dan desktop | Must |

## 6. Model Data Menu

Setiap item menu memiliki struktur data sebagai berikut:

```json
{
  "id": "string",
  "nama_item": "string",
  "kategori": "Minuman | Makanan",
  "sub_kategori": "string (contoh: Non Coffee, Rice Bowl, dst)",
  "harga": "number (dalam Rupiah, disimpan utuh, ditampilkan sebagai xxK)",
  "gambar_url": "string (dummy placeholder di v1.0)",
  "status": "tersedia | habis",
  "deskripsi": "string (opsional, kosong di v1.0)"
}
```

## 7. Daftar Menu Lengkap (Sumber Kebenaran / Source of Truth)

> Catatan: Harga ditulis dalam format "K" = ribuan Rupiah, persis seperti buku menu fisik (misal 23K = Rp23.000).

### 7.1 MENU MINUMAN

**Non Coffee**
| Item | Harga |
|---|---|
| Matcha | 23K |
| Redvelvet | 21K |
| Tiramisu | 21K |
| Cookies & Cream | 21K |
| Taro | 21K |
| Vanilla | 21K |
| Choco Oreo | 21K |
| Chocolate Pistacio | 21K |
| Thai Tea | 21K |
| Green Tea | 21K |
| Bubble Gum | 21K |
| Chocolate | 21K |
| Choco Silverking | 21K |
| Milo Saurus | 21K |
| Air Mineral | 6K |

**Yaqult Series**
| Item | Harga |
|---|---|
| Mango Yaqult | 22K |
| Mandarin Yaqult | 22K |
| Banana Yaqult | 22K |
| Lychee Yaqult | 22K |
| Peach Yaqult | 22K |

**Tea Series**
| Item | Harga |
|---|---|
| Lychee Tea | 20K |
| Lemon Tea | 20K |
| Strawberry Tea | 20K |

**Basic Coffee**
| Item | Harga |
|---|---|
| Espresso | 16K |
| Americano | 21K |
| Peach Americano | 23K |
| Aligator (Sparkling) | 23K |
| Caffe Latte | 23K |
| Cappucino | 23K |
| Mochaccino | 23K |

**Signature**
| Item | Harga |
|---|---|
| Pistachio Latte | 23K |
| Nord Palm Sugar | 23K |
| Butterscotch Latte | 23K |
| Hazelnut Latte | 23K |
| Vanilla Latte | 23K |
| Caramel Latte | 23K |
| Caramel Machiato | 23K |
| Banana Latte | 23K |
| Salted Caramel | 23K |
| Avocado Coffee | 23K |
| Spanish Latte | 23K |

**By Glasses**
| Item | Harga |
|---|---|
| Mont Blanc | 25K |
| ButterScotch Salt Latte | 25K |
| Nordic Latte | 23K |

**By Bottle**
| Item | Harga |
|---|---|
| 1000 ML Nord Palm Sugar | 85K |
| 500 ML Nord Palm Sugar | 45K |

**Manual Brew**
| Item | Harga |
|---|---|
| Nord V60 | 25K |
| Japanese | 25K |
| Vietnam Drip | 21K |
| Kopi Tubruk | 17K |

---

### 7.2 MENU MAKANAN

**Premium Steak**
| Item | Harga |
|---|---|
| Tenderloin Wagyu 200gr | 69K |
| Sirloin Wagyu 200gr | 66K |
| Chicken Ori 200gr | 39K |

**Steak Original**
| Item | Harga |
|---|---|
| Sirloin Ori | 33K |
| Tenderloin Ori | 35K |

**Steak Crispy**
| Item | Harga |
|---|---|
| Sirloin Crispy | 27K |
| Tenderloin Crispy | 28K |
| Chicken Crispy | 25K |

**Rice Bowl**
| Item | Harga |
|---|---|
| Chicken Katsu | 20K |
| Chicken Teriyaki | 20K |
| Chicken Blackpaper | 20K |
| Chicken Barbeque | 20K |
| Chicken Wings Ori | 25K |
| Chicken Wings Spicy | 27K |
| Chicken Wings Sambal | 27K |
| Beef Slice Blackpaper | 32K |
| Beef Slice Barbeque | 32K |
| Beef Slice Sambal B | 32K |

**Baso Aci**
| Item | Harga |
|---|---|
| Baso Aci Ori | 19K |
| Baso Aci Standard | 21K |
| Baso Aci Urat | 23K |
| Baso Aci Mercon | 23K |

**Nasi Goreng**
| Item | Harga |
|---|---|
| Nasgor Kampung | 26K |
| Nasgor Nord | 26K |

**Ayam Goreng**
| Item | Harga |
|---|---|
| Ayam Goreng Laos | 31K |

**Pasta Series**
| Item | Harga |
|---|---|
| Spaghetti Bolognese | 29K |
| Spaghetti Carbonara | 29K |
| Spaghetti Aglio Olio | 29K |

**Snack**
| Item | Harga |
|---|---|
| Kentang Goreng Ori | 16K |
| Kentang Bucket | 18K |
| Roti Maryam Ori | 16K |
| Roti Maryam Coklat | 17K |
| Roti Maryam Coklat Keju | 18K |
| Roti Bakar Coklat | 16K |
| Roti Bakar Coklat Keju | 18K |
| Pisang Bakar Coklat | 16K |
| Pisang Bakar Coklat Keju | 18K |
| Singkong Goreng Ori | 17K |
| Singkong Goreng Keju | 19K |
| Dimsum Mixs | 21K |
| Martabak Mesir | 21K |
| Otak Otak | 18K |
| Cireng Sambal Rujak | 17K |

**Burger**
| Item | Harga |
|---|---|
| Beef Burger | 27K |
| Chicken Burger | 25K |
| Beef Buritos | 28K |
| Chicken Buritos | 27K |
| Hotdog Supreme | 33K |

**Indomie Series**
| Item | Harga |
|---|---|
| Indomie Goreng Ori | 17K |
| Indomie Goreng Telur | 19K |
| Indomie Goreng Spesial | 21K |
| Indomie Rebus Ori | 17K |
| Indomie Rebus Telur | 19K |
| Indomie Rebus Spesial | 21K |
| Mie Nyemek | 25K |
| Mie Lada Hitam | 25K |

**Salad Series**
| Item | Harga |
|---|---|
| Chicken Salad | 26K |
| Maryam Salad | 26K |
| Kentang Bolognese | 26K |

> **Total kategori: 19 sub-kategori | Total item menu: ±120 item**

## 8. Kebutuhan Non-Fungsional

- **Akurasi data**: Nama item & harga wajib sinkron 1:1 dengan buku menu fisik; perubahan harga fisik harus di-update di sistem maksimal H+1.
- **Placeholder gambar**: Semua gambar produk pada v1.0 menggunakan dummy image (rasio 1:1 atau 4:3, warna netral) dengan label kategori, agar mudah diganti nanti tanpa mengubah layout.
- **Performa**: Halaman menu harus termuat < 2 detik pada koneksi 4G.
- **Desain**: Mengikuti identitas visual NORD — dominan warna biru tua (#0C2A5E area) dan tipografi serif untuk judul, sesuai buku menu asli.
- **Kompatibilitas**: Mobile-first (mayoritas akses via QR code di meja).

## 9. Asumsi & Catatan Terbuka

- Beberapa penulisan di buku menu asli mengandung typo (contoh: "Srrawberry Tea", "Dimsum Mixs") — di sistem digital penulisan **dikoreksi menjadi ejaan baku** ("Strawberry Tea", "Dimsum Mix"), namun harga dan urutan tetap mengikuti sumber asli. *(Perlu konfirmasi ke tim NORD apakah koreksi ejaan ini disetujui, atau ingin dibiarkan sesuai buku menu asli.)*
- Kategori "Baso Aci" dan "Indomie Series" dikelompokkan dalam Menu Makanan sesuai buku menu.
- Gambar dummy akan diberi label kategori (mis. "Foto Matcha — Placeholder") agar mudah ditelusuri saat replace ke foto asli.

## 10. Roadmap Singkat

| Tahap | Deliverable |
|---|---|
| Tahap 1 (sekarang) | Struktur data menu akurat + gambar dummy + tampilan dasar |
| Tahap 2 | Panel admin CRUD menu, pencarian |
| Tahap 3 | Foto produk asli menggantikan dummy |
| Tahap 4 (opsional) | Integrasi pemesanan/QR order |

## 11. Kriteria Sukses (Definition of Done — v1.0)

- [ ] Seluruh 19 sub-kategori & ±120 item tampil dengan nama & harga sesuai tabel Bab 7.
- [ ] Setiap item memiliki gambar dummy (tidak ada gambar kosong/broken).
- [ ] Navigasi kategori berfungsi di mobile & desktop.
- [ ] QA menu fisik vs digital dilakukan minimal 1x sebelum rilis (cross-check manual).
