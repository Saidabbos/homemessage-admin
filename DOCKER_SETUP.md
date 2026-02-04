# Laravel Docker Setup

Bu loyiha Docker va Docker Compose orqali ishlaydigan Laravel applikasiyasi.

## Tuzilish

- **PHP-FPM 8.2** - Laravel applikasiyasi uchun
- **Nginx** - Web server (port 80)
- **MySQL 8.0** - Database (port 3306)

## Faylar tavsifi

- `docker-compose.yml` - Docker containerlarining konfiguratsiyasi
- `Dockerfile` - Laravel applikasiyani build qilish uchun
- `docker/nginx/conf.d/app.conf` - Nginx konfiguratsiyasi
- `.env` - Environment o'zgaruvchilar

## Boshlash

### 1. Containerlarni build qilish va ishga tushirish

```bash
docker-compose up -d --build
```

Bu buyruq:
- Barcha container imagelarini build qiladi
- Containerlarni ishga tushiradi
- `-d` flag fonda ishga tushirish uchun

### 2. Databaseni migrate qilish

```bash
docker-compose exec app php artisan migrate
```

### 3. Applikasiyani ko'rish

Browser orqali quyidagi manzilga o'ting:
```
http://localhost
```

## Foydali buyruqlar

### Containerlarni to'xtatish
```bash
docker-compose down
```

### Loglarni ko'rish
```bash
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Containerga kirish
```bash
docker-compose exec app bash
```

### Artisan buyruqlarini ishga tushirish
```bash
docker-compose exec app php artisan tinker
docker-compose exec app php artisan make:model Post
```

### Database ga ulanish
```bash
docker-compose exec mysql mysql -uroot -proot_password -h localhost laravel
```

## Environment o'zgaruvchilar

Quyidagi ma'lumotlar `.env` faylida sozlangan:

- **Database**: laravel
- **Username**: laravel_user
- **Password**: laravel_password
- **Root Password**: root_password

## Muammolarni hal qilish

### Port band bo'lsa
Agar 80 port band bo'lsa, `docker-compose.yml` da nginxning porti o'zgartirilishi mumkin:
```yaml
ports:
  - "8080:80"  # Tashqi port:ichki port
```

### Volume muammolari
Agar fayllar ko'rinmasa, ruxsatlarni tekshiring:
```bash
ls -la
```

### Container ishlamasa
```bash
docker-compose logs app
```
