# Migrasi ke cPanel

1. Upload seluruh isi repository ke `public_html`.
2. Buat database MySQL dan import `schema.sql`.
3. Salin `.env.example` menjadi konfigurasi server yang tidak dapat diakses web, lalu ubah `DB_DRIVER=mysql` dan isi `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
4. Isi `BASE_URL` memakai HTTPS domain cPanel.
5. Di Google Cloud, tambahkan callback `https://domainanda.com/google.php?action=callback`.
6. Isi `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di environment/config server, bukan GitHub.
7. Pastikan PHP 8.1+, PDO MySQL, cURL, Fileinfo, OpenSSL, dan HTTPS aktif.
8. Daftarkan akun admin, lalu set role `admin` pada database.

Development memakai SQLite agar dapat berjalan tanpa cPanel. Produksi memakai MySQL dengan schema yang sama secara logis.
