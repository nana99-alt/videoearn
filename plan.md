# VideoEarn — Rencana Implementasi

## Tujuan
Membangun MVP feed video publik bergaya TikTok dengan akun persisten, upload ke Google Drive admin, moderasi, dan wallet token dasar. Proyek dikerjakan sekarang dalam struktur portable; target deployment berikutnya adalah cPanel.

## Arsitektur
- PHP 8.1+ native sebagai backend dan server-rendered UI.
- PDO dengan SQLite untuk development lokal dan MySQL/MariaDB untuk cPanel melalui konfigurasi environment.
- Google Drive OAuth `drive.file` untuk penyimpanan video milik admin.
- Firebase disiapkan sebagai integrasi opsional di tahap berikutnya; login utama MVP tetap portable PHP session agar migrasi cPanel tidak terkunci pada vendor.
- Credential hanya melalui `.env`/config lokal yang di-ignore Git.

## Fitur inti
1. Register, login, logout, session persistence, password hashing.
2. Feed video approved dengan pemutar publik.
3. Upload video dengan validasi MIME, ukuran, caption, dan status pending.
4. Google Drive OAuth admin dan proxy streaming.
5. Admin moderation approve/reject memakai POST + CSRF.
6. Profil, wallet token, dan ledger read-only.
7. Basic security headers, session regeneration, authorization, CSRF, escaping, dan rate limiting sederhana.

## Desain
Tiga arah: (1) dark vertical creator feed 0.08, (2) clean editorial video board 0.06, (3) neon social arcade 0.86. Dipilih **neon social arcade**: feed vertikal gelap, aksen coral-magenta, panel glass, CTA besar, dan navigasi bawah yang terasa seperti aplikasi mobile.

Prinsip: fokus media, kontras tinggi, interaksi singkat, dan status moderasi yang jelas. Typography memakai system sans; brand mark berupa wordmark `VideoEarn` dengan aksen coral. Brand voice singkat, ramah, dan langsung: “Upload sekali. Tampil setelah disetujui.” / “Kontenmu sedang ditinjau.”

## Struktur
- `index.php`: route UI dan feed.
- `auth.php`, `upload.php`, `admin.php`, `google.php`, `stream.php`: action endpoints.
- `app.php`: config, DB, auth, security helpers, Google API helpers.
- `schema.sql`: schema portable.
- `assets/style.css`: UI responsive.
- `.env.example`: daftar konfigurasi tanpa secret.
- `storage/`: development-only local fallback, ditolak dari web.

## Migrasi cPanel
Salin `.env.example` menjadi konfigurasi cPanel, gunakan MySQL DSN, import `schema.sql`, set HTTPS/base URL, isi OAuth redirect Google sesuai domain, dan pindahkan storage fallback ke Google Drive-only. Tidak ada credential yang boleh masuk GitHub.
