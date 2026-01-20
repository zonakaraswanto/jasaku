# Jasaku — Aplikasi Servis & POS

Aplikasi web untuk mengelola tiket servis, penjualan (POS), pembelian (Purchase Order), inventori, dan laporan. Dibangun dengan PHP + MySQL, menggunakan PDO dan Bootstrap untuk antarmuka.

## Fitur Utama

### Akses & Keamanan
- **Multi-Role Access**: Login terpisah untuk `admin` (akses penuh), `kasir` (fokus penjualan), dan `teknisi` (fokus servis).
- **Audit Log**: Pencatatan riwayat aktivitas pengguna untuk keamanan dan transparansi.

### Layanan Servis & Perbaikan
- **Manajemen Tiket**: Pencatatan masuk, diagnosa, pengerjaan, hingga penyelesaian servis.
- **Penugasan Teknisi**: Distribusi tiket servis ke teknisi yang tersedia.
- **Garansi**: Cetak kartu garansi dan pelacakan masa berlaku garansi.
- **Cek Status Online**: Halaman publik untuk pelanggan melacak status servis (`track.php`) dan cek garansi (`warranty.php`) tanpa login.

### Penjualan & Inventori (POS)
- **Point of Sale (POS)**: Antarmuka kasir untuk transaksi cepat, support scan barcode, dan cetak struk.
- **Manajemen Inventori**: Stok barang real-time, penyesuaian stok, dan riwayat pergerakan barang.
- **Pricelist**: Daftar harga jasa dan sparepart yang terkelola.
- **Manajemen Pelanggan**: Database pelanggan lengkap dengan riwayat transaksi.

### Pengadaan (Purchasing)
- **Purchase Order (PO)**: Pembuatan pesanan pembelian ke supplier.
- **Penerimaan Barang**: Validasi barang masuk dan update stok otomatis.
- **Manajemen Supplier**: Database pemasok dan riwayat pembelian.

### Laporan & Analisa
- **Laporan Keuangan**: Laporan Penjualan dan Pembelian dengan filter periode.
- **Grafik & Statistik**: Visualisasi tren penjualan di dashboard.
- **Ekspor Data**: Dukungan ekspor laporan ke format CSV/Excel.

### Fitur Tambahan (Admin)
- **Manajemen Pengguna**: Tambah, edit, dan atur hak akses pengguna.
- **Pengaturan Toko**: Konfigurasi identitas toko, logo, dan preferensi sistem.
- **Notifikasi**: Sistem pemberitahuan internal.
- **Backup & Integrasi**: Fitur ekspor/impor data dan pengaturan integrasi.

## Kebutuhan Sistem
- PHP 8.x
- MySQL/MariaDB
- Web server (XAMPP/Apache) di `htdocs`

## Konfigurasi Database
Letakkan berkas `.env` di root proyek (`c:\xampp\htdocs\jasaku\jasaku\.env`) dengan isi seperti:
```
DB_HOST=localhost
DB_NAME=jasaku_pos
DB_USER=root
DB_PASS=
DB_PORT=3306
```
Konfigurasi dibaca oleh `config/db.php` saat aplikasi berjalan.

## Instalasi
1. Salin proyek ke `c:\xampp\htdocs\jasaku`
2. Buat `.env` seperti di atas
3. Jalankan Apache & MySQL (XAMPP)
4. Buka `http://localhost/jasaku/index.php`

Tabel akan dibuat otomatis saat API/halaman terkait diakses (mis. `sales`, `sale_items`, `purchase_orders`, `purchase_items`, `stock_movements`, `settings`).

## Navigasi Utama
- `index.php?r=pos/index` — POS, buat transaksi penjualan
- `index.php?r=report/sales` — Laporan Penjualan (filter tanggal, metode bayar, kode transaksi; ekspor CSV)
- `index.php?r=report/purchase` — Laporan Pembelian (filter tanggal, status, supplier, kode PO; ekspor CSV)
- `index.php?r=customer/index` — Manajemen pelanggan
- `index.php?r=purchase/index` — Buat PO (admin)
- `index.php?r=inventory/index` — Inventori

## Ekspor CSV
- Penjualan: `api/sales.php?format=csv&from=YYYY-MM-DD&to=YYYY-MM-DD&payment_method=...`
- Pembelian: `api/purchases.php?format=csv&from=YYYY-MM-DD&to=YYYY-MM-DD&supplier_id=...`

## Tips Penggunaan
- Cari transaksi spesifik dengan memasukkan `Kode Transaksi` (contoh: `SL-XXXXXX`) atau `Kode PO` (contoh: `PO-XXXXXX`) di form laporan.
- Pencarian berdasarkan kode mengabaikan filter tanggal, sehingga transaksi/PO tetap muncul.
- Jika tabel laporan tampak kosong, tunggu sejenak: halaman akan memuat data terbaru dari API dan menampilkan rincian item.
- Area tabel laporan dibatasi tinggi sekitar 10 baris dan dapat di-scroll untuk melihat lebih banyak.

## Struktur Direktori Singkat
- `controllers/` — Logika halaman (Report, Pos, Purchase, dll.)
- `api/` — Endpoint JSON (penjualan, pembelian, items, customers, dll.)
- `views/` — Tampilan HTML/Bootstrap
- `config/` — DB, crypto, audit
- `core/` — Kelas dasar Controller

## Troubleshooting
- Data muncul di CSV tetapi tidak di UI: kosongkan filter tanggal atau gunakan pencarian berdasarkan kode transaksi/PO.
- Pastikan `.env` terbaca dan koneksi DB aktif.

## Noted
- Aplikasi ini dirancang untuk penggunaan pribadi dan tidak disarankan untuk digunakan di lingkungan produksi tanpa modifikasi.
- Pastikan server memiliki cukup sumber daya (CPU, RAM, disk) untuk menjalankan aplikasi.
- Data pengguna dan transaksi disimpan dalam database, sehingga pastikan backup regularitasnya.
- Tidak boleh diperjual belikan, dibuat untuk pemakaian pribadi dan untuk pembelajaran jika ingin dikembangkan.

## Pembuat
- Segi Tiga Creative 2025 😎 (Z0N4)