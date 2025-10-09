# Clario Cloud Storage (Frontend)
Proyek ini adalah tampilan awal aplikasi penyimpanan cloud pribadi.

## Menjalankan
1. Pastikan Docker & docker-compose terinstal.
2. Jalankan:
   ```bash
   docker-compose up -d --build
   ```
3. Buka http://localhost di browser.

## Struktur Folder
- `app/public` → berisi tampilan utama (index.php, CSS, dsb)
- `app/src` → berisi file backend (Database.php, dll)
- `nginx/` → konfigurasi server
