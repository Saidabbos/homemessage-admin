# 📋 Sprint 1: Foundation - Trello Tasks

## Sprint Info
- **Sprint:** 1
- **Focus:** Foundation + APIs
- **Dates:** 3-9 Fevral 2025
- **Delivery:** 9-Fevral (Yakshanba)
- **Hours:** ~22h work + ~3h buffer

---

## GT-001: Laravel Project Setup

**📝 Description:**
Laravel 11 loyihasini yaratish va asosiy konfiguratsiyalarni sozlash.

**✅ Acceptance Criteria:**
- [ ] Laravel 11 proyekt yaratilgan
- [ ] Git repository initialized
- [ ] .env.example barcha variables bilan
- [ ] Directory structure (Services, Enums)
- [ ] `php artisan serve` ishlaydi

**🔧 Technical:**
```
Directory: app/Enums/, app/Services/, app/Http/Controllers/{Api,Admin,Webhook}
```

**⏱ Estimate:** 2h | **🏷 Labels:** `foundation`, `setup`, `P0`

---

## GT-002: Database Migrations - Users & Masters

**📝 Description:**
Admin users va Masters jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [ ] `users` table (id, name, email, password, role, phone, is_active)
- [ ] `masters` table (id, name, phone, bio, photo, token, is_active)
- [ ] `php artisan migrate` xatosiz

**⏱ Estimate:** 1.5h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-003: Database Migrations - Slots

**📝 Description:**
Time slots jadvalini yaratish.

**✅ Acceptance Criteria:**
- [ ] `slots` table (master_id, date, start_time, end_time, status)
- [ ] Unique: master + date + start_time
- [ ] Status: FREE, PENDING, RESERVED, BLOCKED

**⏱ Estimate:** 1h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-004: Database Migrations - Customers

**📝 Description:**
Customers va addresses jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [ ] `customers` table (phone unique, name, telegram_id)
- [ ] `customer_addresses` table (address, entrance, floor, apartment, landmark)
- [ ] `otp_codes` table (phone, code, attempts, expires_at)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-005: Database Migrations - Orders

**📝 Description:**
Orders va order_confirmations jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [ ] `orders` table (order_number, customer_id, master_id, slot_id, massage_type, price, status, public_token)
- [ ] `order_confirmations` table (confirmed_address, call_outcome, has_pi_pi)
- [ ] Order number format: GT-YYYYMMDD-XXX

**⏱ Estimate:** 2h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-006: Database Migrations - Payments

**📝 Description:**
Payments jadvalini yaratish.

**✅ Acceptance Criteria:**
- [ ] `payments` table (order_id, provider, status, amount, transaction_id)
- [ ] Provider: payme, click
- [ ] Status: PENDING, PAID, CANCELLED, FAILED

**⏱ Estimate:** 1h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-007: Database Migrations - QA & Audit

**📝 Description:**
Quality control va audit jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [ ] `quality_controls` table (ratings, checks, feedback)
- [ ] `audit_logs` table (auditable_type, action, old/new values)
- [ ] `telegram_messages` table (order_id, type, status)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-008: Enums

**📝 Description:**
Status enumlarini yaratish.

**✅ Acceptance Criteria:**
- [ ] SlotStatus: FREE, PENDING, RESERVED, BLOCKED
- [ ] OrderStatus: NEW, CONFIRMING, WAITING_PAYMENT, PAID, RESERVED, COMPLETED, CANCELLED
- [ ] PaymentStatus: PENDING, PAID, CANCELLED, FAILED
- [ ] MassageType: traditional, relax_oil

**⏱ Estimate:** 1h | **🏷 Labels:** `backend`, `enums`, `P0`

---

## GT-009: Models - User & Master

**📝 Description:**
User va Master modellarini yaratish.

**✅ Acceptance Criteria:**
- [ ] User model (relationships, role check)
- [ ] Master model (token generation, slots/orders relations)
- [ ] Fillable, casts to'g'ri

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `models`, `P0`

---

## GT-010: Models - Slot, Customer, Order

**📝 Description:**
Asosiy modellarni yaratish.

**✅ Acceptance Criteria:**
- [ ] Slot model (status casting, scopes)
- [ ] Customer model (addresses relation)
- [ ] Order model (all relations, order_number generation)

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `models`, `P0`

---

## GT-011: Vue 3 + Vite Setup

**📝 Description:**
Vue 3 frontend proyektini yaratish.

**✅ Acceptance Criteria:**
- [ ] Vue 3 + Vite proyekt
- [ ] Tailwind CSS configured
- [ ] Pinia store setup
- [ ] Vue Router setup
- [ ] API service (axios)

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `setup`, `P0`

---

## GT-012: Telegram Mini App Integration

**📝 Description:**
TMA SDK integratsiyasi.

**✅ Acceptance Criteria:**
- [ ] useTelegramMiniApp composable
- [ ] Theme sync
- [ ] MainButton, BackButton
- [ ] User data access

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `telegram`, `P0`

---

## GT-013: API Routes Structure

**📝 Description:**
API routes strukturasini yaratish.

**✅ Acceptance Criteria:**
- [ ] Public routes (/masters, /orders)
- [ ] Admin routes (auth required)
- [ ] Webhook routes (/payme, /click)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-014: Sanctum Authentication

**📝 Description:**
Admin authentication setup.

**✅ Acceptance Criteria:**
- [ ] Sanctum installed
- [ ] Login/Logout/Me endpoints
- [ ] Token-based auth
- [ ] Admin seeder

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `auth`, `P0`

---

## GT-014A: Docker Setup - Backend

**📝 Description:**
Laravel backend uchun Docker containerlar sozlash.

**✅ Acceptance Criteria:**
- [ ] docker-compose.yml yaratilgan
- [ ] PHP 8.2-FPM container
- [ ] Nginx container
- [ ] MySQL 8 container
- [ ] Redis container
- [ ] Network sozlangan
- [ ] Volumes (code, mysql data)
- [ ] `docker-compose up -d` ishlaydi

**🔧 docker-compose.yml:**
```yaml
version: '3.8'

services:
  # PHP-FPM
  app:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: golden-touch-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./backend:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - golden-touch-network
    depends_on:
      - mysql
      - redis

  # Nginx
  nginx:
    image: nginx:alpine
    container_name: golden-touch-nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./backend:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
    networks:
      - golden-touch-network
    depends_on:
      - app

  # MySQL
  mysql:
    image: mysql:8.0
    container_name: golden-touch-mysql
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: golden_touch
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_USER: golden_touch
      MYSQL_PASSWORD: secret
    ports:
      - "3306:3306"
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - golden-touch-network

  # Redis
  redis:
    image: redis:alpine
    container_name: golden-touch-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - golden-touch-network

  # Queue Worker
  queue:
    build:
      context: ./backend
      dockerfile: Dockerfile
    container_name: golden-touch-queue
    restart: unless-stopped
    working_dir: /var/www
    command: php artisan queue:work redis --sleep=3 --tries=3
    volumes:
      - ./backend:/var/www
    networks:
      - golden-touch-network
    depends_on:
      - app
      - redis

networks:
  golden-touch-network:
    driver: bridge

volumes:
  mysql-data:
  redis-data:
```

**⏱ Estimate:** 2h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014B: Docker Setup - PHP Dockerfile

**📝 Description:**
PHP-FPM Dockerfile yaratish.

**✅ Acceptance Criteria:**
- [ ] PHP 8.2-FPM base image
- [ ] Required extensions installed
- [ ] Composer installed
- [ ] Working directory set
- [ ] User permissions

**🔧 backend/Dockerfile:**
```dockerfile
FROM php:8.2-fpm

# Arguments
ARG user=golden
ARG uid=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

USER $user
```

**⏱ Estimate:** 1h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014C: Docker Setup - Nginx Config

**📝 Description:**
Nginx konfiguratsiyasi Docker uchun.

**✅ Acceptance Criteria:**
- [ ] Laravel routing ishlaydi
- [ ] PHP-FPM upstream
- [ ] Static files served
- [ ] Security headers

**🔧 docker/nginx/conf.d/app.conf:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    # Max upload size
    client_max_body_size 20M;

    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**🔧 docker/php/local.ini:**
```ini
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 60
```

**⏱ Estimate:** 0.5h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014D: Docker Setup - Frontend

**📝 Description:**
Vue frontend uchun Docker setup (development).

**✅ Acceptance Criteria:**
- [ ] Node.js container
- [ ] Hot reload ishlaydi
- [ ] Port 5173 exposed
- [ ] Volume mount for code

**🔧 docker-compose.yml ga qo'shish:**
```yaml
  # Frontend (Development)
  frontend:
    image: node:20-alpine
    container_name: golden-touch-frontend
    working_dir: /app
    ports:
      - "5173:5173"
    volumes:
      - ./frontend:/app
      - /app/node_modules
    command: sh -c "npm install && npm run dev -- --host 0.0.0.0"
    networks:
      - golden-touch-network
```

**🔧 frontend/Dockerfile (Production build):**
```dockerfile
# Build stage
FROM node:20-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Production stage
FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY docker/nginx/frontend.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
```

**⏱ Estimate:** 1h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014E: Docker - Make Commands

**📝 Description:**
Docker uchun Makefile yaratish.

**✅ Acceptance Criteria:**
- [ ] make up - start containers
- [ ] make down - stop containers
- [ ] make build - rebuild
- [ ] make shell - bash into app
- [ ] make migrate - run migrations
- [ ] make seed - run seeders
- [ ] make test - run tests
- [ ] make logs - view logs

**🔧 Makefile:**
```makefile
.PHONY: up down build shell migrate seed test logs fresh

# Start containers
up:
	docker-compose up -d

# Stop containers
down:
	docker-compose down

# Rebuild containers
build:
	docker-compose build --no-cache

# Shell into app container
shell:
	docker-compose exec app bash

# Run migrations
migrate:
	docker-compose exec app php artisan migrate

# Run seeders
seed:
	docker-compose exec app php artisan db:seed

# Run tests
test:
	docker-compose exec app php artisan test

# View logs
logs:
	docker-compose logs -f

# Fresh database
fresh:
	docker-compose exec app php artisan migrate:fresh --seed

# Composer install
composer-install:
	docker-compose exec app composer install

# NPM install
npm-install:
	docker-compose exec frontend npm install

# Clear all caches
cache-clear:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Full setup
setup: build up composer-install migrate seed
	@echo "✅ Setup complete!"
```

**⏱ Estimate:** 0.5h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014F: Docker - Environment Setup

**📝 Description:**
Docker environment fayllarini sozlash.

**✅ Acceptance Criteria:**
- [ ] .env.docker template
- [ ] Database connection via container name
- [ ] Redis connection configured
- [ ] Queue connection = redis

**🔧 backend/.env.docker:**
```env
APP_NAME="Golden Touch"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (Docker)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=golden_touch
DB_USERNAME=golden_touch
DB_PASSWORD=secret

# Redis (Docker)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=redis
```

**🔧 .dockerignore:**
```
.git
.gitignore
.env
.env.backup
node_modules
vendor
storage/*.key
*.log
.docker
docker-compose.override.yml
```

**⏱ Estimate:** 0.5h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## 📊 Summary

| ID | Task | Hours |
|----|------|-------|
| GT-001 | Laravel Setup | 2h |
| GT-002 | Users & Masters | 1.5h |
| GT-003 | Slots | 1h |
| GT-004 | Customers | 1.5h |
| GT-005 | Orders | 2h |
| GT-006 | Payments | 1h |
| GT-007 | QA & Audit | 1.5h |
| GT-008 | Enums | 1h |
| GT-009 | User & Master Models | 1.5h |
| GT-010 | Core Models | 2h |
| GT-011 | Vue 3 Setup | 2h |
| GT-012 | TMA Integration | 1.5h |
| GT-013 | API Routes | 1.5h |
| GT-014 | Authentication | 1.5h |
| GT-014A | Docker - docker-compose | 2h |
| GT-014B | Docker - PHP Dockerfile | 1h |
| GT-014C | Docker - Nginx Config | 0.5h |
| GT-014D | Docker - Frontend | 1h |
| GT-014E | Docker - Makefile | 0.5h |
| GT-014F | Docker - Environment | 0.5h |

**Total: ~27.5h (Docker bilan)**

**Note:** Docker setup parallel ravishda yoki dastlabki setup bilan birga amalga oshirilishi mumkin.
