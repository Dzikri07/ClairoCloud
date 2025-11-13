# 🐳 Panduan Setup Docker & MinIO untuk ClairoCloud

Dokumentasi lengkap untuk menjalankan ClairoCloud dengan Docker dan MinIO Object Storage.

## 📋 Prerequisites

### 1. Install Docker Desktop

**Windows:**
1. Download Docker Desktop dari [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Jalankan installer dan ikuti wizard instalasi
3. Restart komputer jika diminta
4. Buka Docker Desktop dan tunggu hingga status menunjukkan "Docker Desktop is running"

**Verifikasi Instalasi:**
```bash
docker --version
docker-compose --version
```

## 🚀 Menjalankan Aplikasi

### 1. Start Services dengan Docker Compose

Buka terminal di folder project ClairoCloud, kemudian jalankan:

```bash
docker-compose up -d
```

Perintah ini akan:
- Build image untuk aplikasi PHP
- Download image MinIO dari Docker Hub
- Membuat network untuk komunikasi antar container
- Membuat volume untuk persistensi data MinIO
- Menjalankan semua services dalam mode detached (-d)

**Output yang diharapkan:**
```
Creating network "clairocloud_clario_net" with driver "bridge"
Creating volume "clairocloud_minio_data" with local driver
Creating clario_minio ... done
Creating clairocloud_app_1 ... done
Creating clairocloud_web_1 ... done
Creating clairocloud_createbuckets_1 ... done
```

### 2. Cek Status Container

```bash
docker-compose ps
```

Semua container harus dalam status "Up":
```
Name                    Command               State           Ports
-------------------------------------------------------------------------------
clario_minio           /usr/bin/docker-entrypoint.sh  Up      0.0.0.0:9000->9000/tcp, 0.0.0.0:9001->9001/tcp
clairocloud_app_1      docker-php-entrypoint php-fpm  Up      9000/tcp
clairocloud_web_1      /docker-entrypoint.sh nginx    Up      0.0.0.0:80->80/tcp
```

### 3. Install PHP Dependencies (Pertama Kali)

Setelah container jalan, install dependencies PHP dengan Composer:

```bash
docker-compose exec app composer install
```

Ini akan menginstall AWS SDK for PHP yang diperlukan untuk berkomunikasi dengan MinIO.

## 🌐 Akses Aplikasi

Setelah semua container berjalan, Anda dapat mengakses:

| Service | URL | Credentials |
|---------|-----|-------------|
| **ClairoCloud Web** | http://localhost | admin / adminpass |
| **MinIO Console** | http://localhost:9001 | minioadmin / minioadmin123 |
| **MinIO API** | http://localhost:9000 | - |

## 🔧 Konfigurasi MinIO

### Konfigurasi Default

File `app/public/minio_config.php` berisi konfigurasi default:

```php
define('MINIO_ENABLED', true);
define('MINIO_ENDPOINT', 'http://minio:9000');
define('MINIO_ACCESS_KEY', 'minioadmin');
define('MINIO_SECRET_KEY', 'minioadmin123');
define('MINIO_BUCKET', 'clairocloud');
define('STORAGE_MODE', 'hybrid'); // 'minio', 'local', atau 'hybrid'
```

### Mode Storage

1. **`hybrid`** (Recommended): Upload ke MinIO, fallback ke local jika MinIO tidak tersedia
2. **`minio`**: Hanya gunakan MinIO (error jika MinIO tidak tersedia)
3. **`local`**: Hanya gunakan local storage (disable MinIO)

### Mengubah Credentials

Untuk keamanan production, ubah credentials di `docker-compose.yml`:

```yaml
environment:
  MINIO_ROOT_USER: your_username
  MINIO_ROOT_PASSWORD: your_secure_password
```

Kemudian update juga di `app/public/minio_config.php`.

## 📊 Menggunakan MinIO Console

1. Akses http://localhost:9001
2. Login dengan credentials: `minioadmin` / `minioadmin123`
3. Di dashboard Anda dapat:
   - Melihat semua files di bucket `clairocloud`
   - Upload/download files secara manual
   - Monitoring storage usage
   - Manage buckets dan permissions
   - View access logs

## 🔍 Monitoring & Logs

### View Logs

**Semua Services:**
```bash
docker-compose logs -f
```

**Service Tertentu:**
```bash
docker-compose logs -f minio
docker-compose logs -f app
docker-compose logs -f web
```

### Check MinIO Health

```bash
curl http://localhost:9000/minio/health/live
```

Response: `200 OK` jika MinIO healthy.

## 🛠️ Commands Berguna

### Stop Services
```bash
docker-compose stop
```

### Start Services (setelah stop)
```bash
docker-compose start
```

### Restart Services
```bash
docker-compose restart
```

### Rebuild Containers (setelah update Dockerfile)
```bash
docker-compose up -d --build
```

### Stop & Remove Containers
```bash
docker-compose down
```

### Stop & Remove Containers + Volumes (HAPUS SEMUA DATA!)
```bash
docker-compose down -v
```

### Exec ke Container
```bash
# Masuk ke container app
docker-compose exec app bash

# Masuk ke container minio
docker-compose exec minio sh
```

## 🐛 Troubleshooting

### Port Sudah Digunakan

**Error:**
```
Error: bind: address already in use
```

**Solusi:**
1. Cek aplikasi yang menggunakan port 80, 9000, atau 9001
2. Stop aplikasi tersebut atau ubah port di `docker-compose.yml`

**Windows - Cek Port:**
```cmd
netstat -ano | findstr :80
netstat -ano | findstr :9000
netstat -ano | findstr :9001
```

### MinIO Bucket Not Found

**Error dalam logs:**
```
The specified bucket does not exist
```

**Solusi:**
```bash
# Restart service createbuckets untuk membuat bucket otomatis
docker-compose restart createbuckets

# Atau buat manual via MinIO Console (http://localhost:9001)
```

### Composer Dependencies Gagal Install

**Error:**
```
Your requirements could not be resolved
```

**Solusi:**
```bash
# Hapus vendor dan composer.lock
docker-compose exec app rm -rf vendor composer.lock

# Install ulang
docker-compose exec app composer install
```

### File Upload Error

**Error:**
```
MinIO client not available
```

**Solusi:**
1. Pastikan MinIO container berjalan: `docker-compose ps`
2. Check logs: `docker-compose logs minio`
3. Pastikan composer dependencies terinstall
4. Restart app container: `docker-compose restart app`

### Permission Denied

**Error:**
```
Permission denied: /var/www/html/public/uploads
```

**Solusi:**
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html
```

## 🔄 Update & Maintenance

### Update Images
```bash
docker-compose pull
docker-compose up -d
```

### Backup MinIO Data
```bash
# Data disimpan di Docker volume
docker volume ls

# Export volume
docker run --rm -v clairocloud_minio_data:/data -v $(pwd):/backup alpine tar czf /backup/minio_backup.tar.gz /data
```

### Restore MinIO Data
```bash
docker run --rm -v clairocloud_minio_data:/data -v $(pwd):/backup alpine tar xzf /backup/minio_backup.tar.gz -C /
```

## 📈 Production Considerations

Untuk deployment production:

1. **Ubah Credentials Default**
   - MinIO root user/password
   - Database credentials
   - Session secrets

2. **Enable HTTPS**
   - Setup SSL certificates
   - Configure nginx untuk HTTPS
   - Update MinIO endpoint ke HTTPS

3. **Persistent Volumes**
   - Gunakan named volumes untuk data persistence
   - Regular backup schedule

4. **Resource Limits**
   - Set memory/CPU limits di docker-compose.yml
   - Monitor resource usage

5. **Network Security**
   - Jangan expose MinIO port secara public
   - Gunakan internal network untuk komunikasi antar services
   - Setup firewall rules

## 📞 Support

Jika masih mengalami masalah:
1. Check logs dengan `docker-compose logs`
2. Restart services dengan `docker-compose restart`
3. Rebuild containers dengan `docker-compose up -d --build`
4. Buat issue di GitHub repository

## 📚 Referensi

- [Docker Documentation](https://docs.docker.com/)
- [MinIO Documentation](https://min.io/docs/minio/linux/index.html)
- [AWS SDK for PHP - S3 Client](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-examples.html)
