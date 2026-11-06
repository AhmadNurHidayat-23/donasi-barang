# Donasi Barang

**Beri Lagi** mempertemukan barang layak pakai dengan penerima baru, melalui pengajuan privat dan konfirmasi serah terima oleh kedua pihak.

## Fitur

- Landing page, katalog, pencarian, filter kategori/kota, dan status ketersediaan.
- Registrasi serta login; nama/email akun tidak ditampilkan pada halaman donasi publik.
- Posting barang dengan kategori, kondisi, kota, deskripsi, dan foto.
- Foto JPG/PNG/WebP diperkecil bila perlu dan dikodekan ulang ke JPEG tanpa metadata asli.
- Pengajuan kebutuhan hanya terlihat oleh pemohon dan donatur terkait.
- Donatur memilih satu penerima secara atomik; pengajuan lain otomatis tidak dipilih.
- Petunjuk pengambilan privat hanya dapat dibaca donatur dan penerima terpilih.
- Konfirmasi serah terima dua pihak; satu konfirmasi tidak menyelesaikan donasi.
- Membuka kembali pencadangan sebelum ada konfirmasi, riwayat donasi/pengajuan, serta catatan serah terima.
- Validasi, CSRF, pembatasan request, loading, pesan hasil, keadaan kosong, dan tampilan responsif.

## Teknologi

Laravel 13, Blade, Tailwind CSS 4, Vite 7, SQLite, PHPUnit, dan Playwright. Memerlukan PHP 8.3+ dengan GD, Composer, dan Node.js 22.12+. Versi dependensi dikunci di lockfile.

## Instalasi dan menjalankan

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite -ErrorAction SilentlyContinue
php artisan migrate
php artisan storage:link
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8041
```

Buka `http://127.0.0.1:8041`. Pengguna mendaftar sendiri; tidak ada akun atau kata sandi bawaan. Seeder tidak membuat donasi maupun pengguna fiktif. Jalankan `npm run dev` di terminal terpisah untuk pengembangan frontend.

Database ada di `database/database.sqlite`; foto barang tersimpan pada disk publik `storage/app/public/donations`. Keduanya diabaikan Git. Gunakan database baru khusus proyek jika beralih ke MySQL; jangan hubungkan aplikasi dengan database proyek lain.

## Alur dan privasi

Donatur memublikasikan informasi barang dengan kota sebagai lokasi umum. Formulir publik menolak pola email, telepon, dan penanda alamat; tetap periksa teks dan isi foto secara manual sebelum membagikan. Pengodean ulang foto membuang metadata, tetapi tidak menyembunyikan informasi yang terlihat pada gambar. Jangan unggah foto dokumen pribadi, label alamat, atau wajah tanpa izin.

Penerima mengirim alasan kebutuhan secara privat. Setelah donatur memilih penerima, petunjuk pertemuan dapat dibagikan pada ruang serah terima privat. Pilih tempat pertemuan yang disepakati. Jangan cantumkan alamat atau kontak pada deskripsi publik.

Barang selesai diserahterimakan hanya setelah donatur dan penerima sama-sama mengonfirmasi. Pencadangan dapat dibuka kembali sebelum salah satu pihak mengonfirmasi; penerima dan petunjuk lama dihapus dari pencadangan. Akun yang pernah mengajukan barang yang sama tidak dapat mengirim pengajuan berulang. Donasi barang tidak memproses pembayaran atau ongkos kirim.

## Pengujian

```powershell
php artisan route:list --except-vendor
php artisan test
npm run build
npm run test:e2e
```

PHPUnit memakai SQLite dalam memori. Playwright memakai Microsoft Edge terpasang, port 8042, akun sementara dengan kata sandi acak, dan `database/.browser-test.sqlite` yang dibuat ulang khusus tes. Database aplikasi tidak dipakai pengujian. Screenshot/trace hanya lokal di `test-results/` yang diabaikan Git. Tes viewport desktop/tablet/ponsel tidak merupakan pengujian perangkat fisik.

## Operasional

Gunakan HTTPS, `APP_DEBUG=false`, document root `public/`, pencadangan database/foto, serta tata kelola konten untuk penggunaan publik nyata. Foto buku pada landing adalah ilustrasi dari https://images.unsplash.com/photo-1507842217343-583bb7270b66 dengan ketentuan https://unsplash.com/license. Kredensial, database, screenshot, build, vendor, dan node_modules tidak disertakan ke repository. Maksimal 300 baris per berkas kode aplikasi. Tidak ada CSS atau JavaScript inline pada Blade.
