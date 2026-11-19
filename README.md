# VideoEarn

MVP feed video publik bergaya TikTok. User memiliki akun persisten, upload video masuk moderasi, dan video approved dapat ditonton publik. Video produksi disimpan ke Google Drive admin melalui OAuth; metadata disimpan pada database.

File video tetap privat selama statusnya `pending` atau `rejected`. Saat admin menekan approve, aplikasi membuat izin `anyone/reader` di Google Drive dan video mulai tampil di feed publik. User tidak perlu memiliki akun Google untuk menonton melalui website.

## Development

Gunakan PHP 8.1+ dengan PDO SQLite, cURL, Fileinfo, dan OpenSSL. Salin `.env.example` menjadi `.env`, lalu jalankan `php -S 0.0.0.0:3000 -t .`.

Tanpa credential Google Drive, halaman dan database lokal tetap dapat diuji; upload ke Drive baru aktif setelah OAuth dikonfigurasi. Jangan commit `.env`.

## Integrasi

Firebase bersifat opsional untuk tahap lanjutan. MVP sengaja mempertahankan login PHP session agar source mudah dimigrasikan ke cPanel. Detail migrasi ada di `CPANEL_MIGRATION.md`.
