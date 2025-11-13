# 🎉 Integrasi Docker & MinIO untuk ClairoCloud

## ✅ Yang Sudah Diimplementasikan

### 1. **Docker Configuration**
- ✅ `docker-compose.yml` - Konfigurasi lengkap dengan MinIO service
- ✅ `app/Dockerfile` - Updated dengan Composer dan dependencies
- ✅ MinIO Console (Web UI) di port 9001
- ✅ Auto-create bucket "clairocloud" saat startup
- ✅ Health check untuk MinIO

### 2. **PHP Dependencies**
- ✅ `app/composer.json` - AWS SDK for PHP (S3-compatible)
- ✅ Auto-install dependencies saat build Docker image

### 3. **MinIO Integration Files**
- ✅ `app/public/minio_config.php` - Konfigurasi koneksi MinIO
- ✅ `app/public/minio_functions.php` - Helper functions untuk MinIO:
  - Upload file ke MinIO
  - Download file dari MinIO
  - Delete file dari MinIO
  - Check file exists
  - Get file metadata
  - Generate presigned URLs
  - List files in bucket
  - Copy files
  - Stream files

### 4. **Updated Core Functions**
- ✅ `app/public/file_functions.php` - Handle upload dengan MinIO
  - Hybrid mode: Upload ke local + MinIO
  - Auto-fallback ke local storage jika MinIO unavailable
  - Storage type tracking (local/minio)

- ✅ `app/public/delete_file.php` - Delete dari MinIO juga
- ✅ `app/public/preview_file.php` - Preview dari MinIO dengan presigned URLs

### 5. **Documentation**
- ✅ `docs/DOCKER_MINIO_SETUP.md` - Panduan lengkap setup dan troubleshooting

## 🚀 Quick Start Guide

### Step 1: Install Docker Desktop

**Windows:**
1. Download dari https://www.docker.com/products/docker-desktop
2. Install dan restart komputer
3. Buka Docker Desktop, tunggu hingga running

**Verify:**
```bash
docker --version
docker-compose --version
```

### Step 2: Start Services

Di folder project ClairoCloud:

```bash
docker-compose up -d
```

Tunggu beberapa menit untuk download images dan build.

### Step 3: Install PHP Dependencies

```bash
docker-compose exec app composer install
```

### Step 4: Akses Aplikasi

- **ClairoCloud**: http://localhost
  - Login: `admin` / `adminpass`
  
- **MinIO Console**: http://localhost:9001
  - Login: `minioadmin` / `minioadmin123`

## 📊 Cara Kerja Integrasi

### Upload Flow

```
User Upload File
    ↓
1. Simpan ke local storage (/app/public/uploads)
    ↓
2. Check: MinIO available?
    ├─ YES → Upload ke MinIO
    │         ├─ Success → Mark as 'minio' storage
    │         └─ Fail → Keep as 'local' storage
    └─ NO → Keep as 'local' storage
    ↓
3. Update database with storage_type
```

### Preview/Download Flow

```
User Request File
    ↓
1. Check: File exists in MinIO?
    ├─ YES → Generate presigned URL (1 hour expiry)
    │         └─ Serve from MinIO
    └─ NO → Serve from local storage
```

### Delete Flow

```
User Delete File
    ↓
1. Check: File exists in MinIO?
    └─ YES → Delete from MinIO
    ↓
2. Move local file to trash
```

## ⚙️ Konfigurasi Storage Mode

Edit `app/public/minio_config.php`:

```php
define('STORAGE_MODE', 'hybrid'); // Ubah sesuai kebutuhan
```

**Mode Options:**

1. **`hybrid`** (Recommended - Default)
   - Upload ke MinIO jika available
   - Fallback ke local jika MinIO down
   - Local file tetap disimpan sebagai backup
   - ✅ High availability
   - ✅ Best for production

2. **`minio`** (MinIO Only)
   - Hanya gunakan MinIO
   - Error jika MinIO tidak available
   - Local file bisa dihapus setelah upload sukses
   - ✅ Scalable storage
   - ⚠️ Requires MinIO always running

3. **`local`** (Local Only)
   - Disable MinIO completely
   - Semua file di local storage
   - ✅ Simple, no external dependencies
   - ⚠️ Limited scalability

## 🔍 Monitoring

### Check Container Status
```bash
docker-compose ps
```

### View Logs
```bash
# All services
docker-compose logs -f

# MinIO only
docker-compose logs -f minio

# App only
docker-compose logs -f app
```

### Check MinIO Health
```bash
curl http://localhost:9000/minio/health/live
```

### Access MinIO Console
1. Go to http://localhost:9001
2. Login: `minioadmin` / `minioadmin123`
3. View bucket "clairocloud" untuk melihat uploaded files

## 🎯 Features

### ✅ Implemented
- [x] Docker containerization
- [x] MinIO object storage integration
- [x] Hybrid storage mode (MinIO + Local)
- [x] Auto-create bucket on startup
- [x] Presigned URLs for secure file access
- [x] MinIO Console for management
- [x] Health monitoring
- [x] Delete sync (MinIO + Local)
- [x] Preview support for MinIO files
- [x] Comprehensive documentation

### 🔜 Future Enhancements
- [ ] Database schema update untuk storage_type column
- [ ] Migration script untuk move existing files ke MinIO
- [ ] Storage analytics dashboard
- [ ] Multi-bucket support
- [ ] File versioning
- [ ] CDN integration
- [ ] Automatic cleanup old files
- [ ] File encryption at rest

## 📝 Database Schema (Optional)

Untuk tracking storage lebih baik, update table `files`:

```sql
ALTER TABLE files 
ADD COLUMN storage_type VARCHAR(20) DEFAULT 'local',
ADD COLUMN minio_key VARCHAR(255),
ADD COLUMN minio_url TEXT;
```

Code sudah support kolom ini, tinggal jalankan SQL di atas.

## 🔧 Troubleshooting

### Port Conflict
```bash
# Check what's using port 80
netstat -ano | findstr :80

# Check what's using port 9000
netstat -ano | findstr :9000
```

### MinIO Not Accessible
```bash
# Restart MinIO
docker-compose restart minio

# Check logs
docker-compose logs minio

# Recreate bucket
docker-compose restart createbuckets
```

### Upload to MinIO Fails
```bash
# Check composer dependencies
docker-compose exec app composer show

# Reinstall
docker-compose exec app composer install

# Check MinIO connectivity from app container
docker-compose exec app curl http://minio:9000/minio/health/live
```

### Permission Issues
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html
```

## 📚 Documentation

Lihat `docs/DOCKER_MINIO_SETUP.md` untuk:
- Panduan setup detail
- Troubleshooting lengkap
- Production deployment guide
- Backup & restore procedures
- Security considerations

## 🎓 Learn More

- [MinIO Documentation](https://min.io/docs)
- [AWS SDK for PHP](https://docs.aws.amazon.com/sdk-for-php/)
- [Docker Compose](https://docs.docker.com/compose/)

## 💡 Tips

1. **Development**: Gunakan mode `hybrid` untuk fleksibilitas
2. **Production**: Ubah credentials default di `docker-compose.yml`
3. **Backup**: MinIO data ada di Docker volume `clairocloud_minio_data`
4. **Performance**: MinIO lebih cepat untuk serve static files
5. **Scalability**: MinIO support distributed mode untuk HA

## 🤝 Contributing

Feel free to contribute! Areas yang bisa dikembangkan:
- Migration script untuk existing files
- Storage analytics
- Multi-region support
- Automatic file compression
- Thumbnail generation

---

**Developed with ❤️ for ClairoCloud**
