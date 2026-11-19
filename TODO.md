# VideoEarn TODO

## Fondasi portable
- [x] Proyek dapat dijalankan pada PHP 8.1+ tanpa cPanel.
- [x] Konfigurasi development SQLite dan produksi MySQL dipisahkan dari source code.
- [x] Credential Google Drive/Firebase tidak masuk repository.

## Akun dan data
- [x] User dapat register, login, logout, dan kembali menemukan data akunnya.
- [x] Password di-hash dan session ID diregenerasi setelah autentikasi.
- [x] Role user/admin membatasi panel moderasi.

## Video
- [x] User dapat mengunggah video MP4, WEBM, atau MOV dengan caption dan batas ukuran.
- [x] Metadata video tersimpan dan status awal `pending`.
- [x] Video approved tampil di feed publik dan dapat ditonton tanpa login.
- [x] Google Drive admin menjadi penyimpanan utama setelah OAuth dikonfigurasi.

## Moderasi dan wallet
- [x] Admin dapat preview, approve, dan reject upload memakai POST + CSRF.
- [x] Halaman profil menampilkan status upload user.
- [x] Wallet dan ledger menampilkan saldo tanpa mengklaim payout sebelum sistem pembayaran tersedia.

## Validasi dan delivery
- [x] Basic security headers, escaping, CSRF, rate limiting, dan proteksi file konfigurasi aktif.
- [ ] Preview lokal berjalan; PHP CLI belum tersedia pada sandbox.
- [x] Repository GitHub private dibuat setelah source siap.
- [x] Panduan migrasi cPanel tersedia.
