# VideoEarn TODO

## Fondasi portable
- [ ] Proyek dapat dijalankan pada PHP 8.1+ tanpa cPanel.
- [ ] Konfigurasi development SQLite dan produksi MySQL dipisahkan dari source code.
- [ ] Credential Google Drive/Firebase tidak masuk repository.

## Akun dan data
- [ ] User dapat register, login, logout, dan kembali menemukan data akunnya.
- [ ] Password di-hash dan session ID diregenerasi setelah autentikasi.
- [ ] Role user/admin membatasi panel moderasi.

## Video
- [ ] User dapat mengunggah video MP4, WEBM, atau MOV dengan caption dan batas ukuran.
- [ ] Metadata video tersimpan dan status awal `pending`.
- [ ] Video approved tampil di feed publik dan dapat ditonton tanpa login.
- [ ] Google Drive admin menjadi penyimpanan utama setelah OAuth dikonfigurasi.

## Moderasi dan wallet
- [ ] Admin dapat preview, approve, dan reject upload memakai POST + CSRF.
- [ ] Halaman profil menampilkan status upload user.
- [ ] Wallet dan ledger menampilkan saldo tanpa mengklaim payout sebelum sistem pembayaran tersedia.

## Validasi dan delivery
- [ ] Basic security headers, escaping, CSRF, rate limiting, dan proteksi file konfigurasi aktif.
- [ ] Preview lokal berjalan.
- [ ] Repository GitHub private dibuat setelah source siap.
- [ ] Panduan migrasi cPanel tersedia.
