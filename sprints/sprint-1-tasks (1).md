# 📋 Sprint 1: Foundation - Trello Tasks

## Sprint Info
- **Sprint:** 1
- **Focus:** Foundation + APIs
- **Dates:** 3-9 Fevral 2025
- **Delivery:** 9-Fevral (Yakshanba)
- **Hours:** ~22h work + ~3h buffer
- **Status:** ✅ BAJARILGAN

---

## GT-001: Laravel Project Setup ✅

**📝 Description:**
Laravel 11 loyihasini yaratish va asosiy konfiguratsiyalarni sozlash.

**✅ Acceptance Criteria:**
- [x] Laravel 11 proyekt yaratilgan
- [x] Git repository initialized
- [x] .env.example barcha variables bilan
- [x] Directory structure (Services, Enums)
- [x] `php artisan serve` ishlaydi

**⏱ Estimate:** 2h | **🏷 Labels:** `foundation`, `setup`, `P0`

---

## GT-002: Database Migrations - Users & Masters ✅

**📝 Description:**
Admin users va Masters jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [x] `users` table (id, name, email, password, role, phone, is_active)
- [x] `masters` table (id, name, phone, bio, photo, token, is_active)
- [x] `php artisan migrate` xatosiz

**⏱ Estimate:** 1.5h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-003: Database Migrations - Slots ✅

**📝 Description:**
Time slots jadvalini yaratish.

**✅ Acceptance Criteria:**
- [x] `slots` table (master_id, date, start_time, end_time, status)
- [x] Unique: master + date + start_time
- [x] Status: FREE, PENDING, RESERVED, BLOCKED

**⏱ Estimate:** 1h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-004: Database Migrations - Customers ✅

**📝 Description:**
Customers va addresses jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [x] `customers` table (phone unique, name, telegram_id)
- [x] `customer_addresses` table (address, entrance, floor, apartment, landmark)
- [x] `otp_codes` table (phone, code, attempts, expires_at)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-005: Database Migrations - Orders ✅

**📝 Description:**
Orders va order_confirmations jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [x] `orders` table (order_number, customer_id, master_id, slot_id, massage_type, price, status, public_token)
- [x] `order_confirmations` table (confirmed_address, call_outcome, has_pi_pi)
- [x] Order number format: GT-YYYYMMDD-XXX

**⏱ Estimate:** 2h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-006: Database Migrations - Payments ✅

**📝 Description:**
Payments jadvalini yaratish.

**✅ Acceptance Criteria:**
- [x] `payments` table (order_id, provider, status, amount, transaction_id)
- [x] Provider: payme, click
- [x] Status: PENDING, PAID, CANCELLED, FAILED

**⏱ Estimate:** 1h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-007: Database Migrations - QA & Audit ✅

**📝 Description:**
Quality control va audit jadvallarini yaratish.

**✅ Acceptance Criteria:**
- [x] `quality_controls` table (ratings, checks, feedback)
- [x] `audit_logs` table (auditable_type, action, old/new values)
- [x] `telegram_messages` table (order_id, type, status)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `database`, `migration`, `P0`

---

## GT-008: Enums ✅

**📝 Description:**
Status enumlarini yaratish.

**✅ Acceptance Criteria:**
- [x] SlotStatus: FREE, PENDING, RESERVED, BLOCKED
- [x] OrderStatus: NEW, CONFIRMING, WAITING_PAYMENT, PAID, RESERVED, COMPLETED, CANCELLED
- [x] PaymentStatus: PENDING, PAID, CANCELLED, FAILED
- [x] MassageType: traditional, relax_oil

**⏱ Estimate:** 1h | **🏷 Labels:** `backend`, `enums`, `P0`

---

## GT-009: Models - User & Master ✅

**📝 Description:**
User va Master modellarini yaratish.

**✅ Acceptance Criteria:**
- [x] User model (relationships, role check)
- [x] Master model (token generation, slots/orders relations)
- [x] Fillable, casts to'g'ri

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `models`, `P0`

---

## GT-010: Models - Slot, Customer, Order ✅

**📝 Description:**
Asosiy modellarni yaratish.

**✅ Acceptance Criteria:**
- [x] Slot model (status casting, scopes)
- [x] Customer model (addresses relation)
- [x] Order model (all relations, order_number generation)

**⏱ Estimate:** 2h | **🏷 Labels:** `backend`, `models`, `P0`

---

## GT-011: Vue 3 + Vite Setup ✅

**📝 Description:**
Vue 3 frontend proyektini yaratish.

**✅ Acceptance Criteria:**
- [x] Vue 3 + Vite proyekt
- [x] Tailwind CSS configured
- [x] Pinia store setup
- [x] Vue Router setup
- [x] API service (axios)

**⏱ Estimate:** 2h | **🏷 Labels:** `frontend`, `setup`, `P0`

---

## GT-012: Telegram Mini App Integration ✅

**📝 Description:**
TMA SDK integratsiyasi.

**✅ Acceptance Criteria:**
- [x] useTelegramMiniApp composable
- [x] Theme sync
- [x] MainButton, BackButton
- [x] User data access

**⏱ Estimate:** 1.5h | **🏷 Labels:** `frontend`, `telegram`, `P0`

---

## GT-013: API Routes Structure ✅

**📝 Description:**
API routes strukturasini yaratish.

**✅ Acceptance Criteria:**
- [x] Public routes (/masters, /orders)
- [x] Admin routes (auth required)
- [x] Webhook routes (/payme, /click)

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `api`, `P0`

---

## GT-014: Sanctum Authentication ✅

**📝 Description:**
Admin authentication setup.

**✅ Acceptance Criteria:**
- [x] Sanctum installed
- [x] Login/Logout/Me endpoints
- [x] Token-based auth
- [x] Admin seeder

**⏱ Estimate:** 1.5h | **🏷 Labels:** `backend`, `auth`, `P0`

---

## GT-014A: Docker Setup - Backend ✅

**✅ Acceptance Criteria:**
- [x] docker-compose.yml yaratilgan
- [x] PHP 8.2-FPM container
- [x] Nginx container
- [x] MySQL 8 container
- [x] Redis container
- [x] Network sozlangan
- [x] Volumes (code, mysql data)
- [x] `docker-compose up -d` ishlaydi

**⏱ Estimate:** 2h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014B: Docker Setup - PHP Dockerfile ✅

**✅ Acceptance Criteria:**
- [x] PHP 8.2-FPM base image
- [x] Required extensions installed
- [x] Composer installed
- [x] Working directory set
- [x] User permissions

**⏱ Estimate:** 1h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014C: Docker Setup - Nginx Config ✅

**✅ Acceptance Criteria:**
- [x] Laravel routing ishlaydi
- [x] PHP-FPM upstream
- [x] Static files served
- [x] Security headers

**⏱ Estimate:** 0.5h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014D: Docker Setup - Frontend ✅

**✅ Acceptance Criteria:**
- [x] Node.js container
- [x] Hot reload ishlaydi
- [x] Port 5173 exposed
- [x] Volume mount for code

**⏱ Estimate:** 1h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014E: Docker - Make Commands ✅

**✅ Acceptance Criteria:**
- [x] make up - start containers
- [x] make down - stop containers
- [x] make build - rebuild
- [x] make shell - bash into app
- [x] make migrate - run migrations
- [x] make seed - run seeders
- [x] make test - run tests
- [x] make logs - view logs

**⏱ Estimate:** 0.5h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## GT-014F: Docker - Environment Setup ✅

**✅ Acceptance Criteria:**
- [x] .env.docker template
- [x] Database connection via container name
- [x] Redis connection configured
- [x] Queue connection = redis

**⏱ Estimate:** 0.5h | **🏷 Labels:** `devops`, `docker`, `P0`

---

## 📊 Summary

| ID | Task | Hours | Status |
|----|------|-------|--------|
| GT-001 | Laravel Setup | 2h | ✅ |
| GT-002 | Users & Masters | 1.5h | ✅ |
| GT-003 | Slots | 1h | ✅ |
| GT-004 | Customers | 1.5h | ✅ |
| GT-005 | Orders | 2h | ✅ |
| GT-006 | Payments | 1h | ✅ |
| GT-007 | QA & Audit | 1.5h | ✅ |
| GT-008 | Enums | 1h | ✅ |
| GT-009 | User & Master Models | 1.5h | ✅ |
| GT-010 | Core Models | 2h | ✅ |
| GT-011 | Vue 3 Setup | 2h | ✅ |
| GT-012 | TMA Integration | 1.5h | ✅ |
| GT-013 | API Routes | 1.5h | ✅ |
| GT-014 | Authentication | 1.5h | ✅ |
| GT-014A | Docker - docker-compose | 2h | ✅ |
| GT-014B | Docker - PHP Dockerfile | 1h | ✅ |
| GT-014C | Docker - Nginx Config | 0.5h | ✅ |
| GT-014D | Docker - Frontend | 1h | ✅ |
| GT-014E | Docker - Makefile | 0.5h | ✅ |
| GT-014F | Docker - Environment | 0.5h | ✅ |

**Total: ~27.5h — ALL DONE ✅**
