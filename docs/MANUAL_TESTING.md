# HomeMessage - Manual Testing Guide

## Muhitlar

| Muhit | URL |
|-------|-----|
| Production | https://hm.make-it.uz |
| MiniApp | https://hm.make-it.uz/app |
| Admin | https://hm.make-it.uz/admin |
| Telegram Bot | @h_m_UZ_bot |

---

## 1. Landing Page (Public)

### 1.1 Asosiy sahifa
- [ ] Sahifa to'liq yuklanadi
- [ ] Logo va navigatsiya ko'rinadi
- [ ] Hero section to'g'ri chiqadi
- [ ] Xizmatlar ro'yxati ko'rinadi
- [ ] Masterlar slider ishlaydi
- [ ] Testimonials ko'rinadi
- [ ] Footer linklar ishlaydi
- [ ] Mobil versiya responsive

### 1.2 Til almashtirish
- [ ] UZ → RU → EN almashtirish ishlaydi
- [ ] Barcha matnlar tarjima qilingan
- [ ] Til tanlov cookie'da saqlanadi

### 1.3 Masterlar sahifasi
- [ ] `/masters` - ro'yxat chiqadi
- [ ] Har bir master kartasi bosilganda detail ochildi
- [ ] Filter ishlaydi (xizmat turi, jins)
- [ ] Rasm yuklanmasa placeholder chiqadi

---

## 2. Telegram MiniApp

### 2.1 Kirish
- [ ] Telegram'dan bot ochiladi (@h_m_UZ_bot)
- [ ] "Bron qilish" tugmasi MiniApp ochadi
- [ ] Agar Telegram ID bog'langan → avtomatik kirish
- [ ] Agar yangi user → Login sahifasi

### 2.2 Login Flow
- [ ] Telefon raqam kiritish (+998 format)
- [ ] Agar PIN bor → usul tanlash (PIN yoki SMS)
- [ ] SMS kod yuboriladi
- [ ] Kod tekshiriladi
- [ ] **Yangi user** → Ism kiritish modal
- [ ] Ism saqlangandan keyin Home'ga redirect

### 2.3 Home Page
- [ ] Foydalanuvchi ismi ko'rinadi
- [ ] Xizmatlar ro'yxati chiqadi
- [ ] "Seans bron qilish" tugmasi ishlaydi
- [ ] Sidebar menu ochiladi
- [ ] Statistika ko'rinadi

### 2.4 Booking Flow
1. **Xizmat tanlash**
   - [ ] Xizmat turlari ro'yxati
   - [ ] Davomiylik tanlash (60/90/120 min)
   - [ ] Narx to'g'ri ko'rinadi

2. **Master tanlash**
   - [ ] Masterlar ro'yxati (faqat tanlangan xizmat uchun)
   - [ ] "Ixtiyoriy" variant bor
   - [ ] Master foto va reyting ko'rinadi

3. **Sana va vaqt**
   - [ ] Kalendar ko'rinadi
   - [ ] O'tgan kunlar disabled
   - [ ] Bo'sh slotlar ko'rinadi
   - [ ] Band slotlar disabled
   - [ ] 2 soat oldindan bron qilish mumkin emas

4. **Manzil**
   - [ ] Saqlangan manzillar ro'yxati
   - [ ] Yangi manzil qo'shish
   - [ ] Xaritada tanlash ishlaydi
   - [ ] Default manzil belgilash

5. **Tasdiqlash**
   - [ ] Buyurtma ma'lumotlari to'g'ri
   - [ ] Narx kalkulyatsiyasi to'g'ri
   - [ ] "Tasdiqlash" tugmasi ishlaydi
   - [ ] Success sahifasi chiqadi

### 2.5 Buyurtmalar
- [ ] `/app/orders` - buyurtmalar ro'yxati
- [ ] Status badge to'g'ri rang
- [ ] Buyurtma detail sahifasi
- [ ] Bekor qilish ishlaydi (agar mumkin bo'lsa)

### 2.6 Profil
- [ ] `/app/profile` - profil sahifasi
- [ ] Ism tahrirlash
- [ ] Telefon ko'rinadi (o'zgartirib bo'lmaydi)
- [ ] PIN kod o'rnatish/o'chirish
- [ ] Chiqish tugmasi

### 2.7 Manzillar
- [ ] `/app/addresses` - manzillar ro'yxati
- [ ] Yangi manzil qo'shish
- [ ] Manzil tahrirlash
- [ ] Manzil o'chirish
- [ ] Default qilish

### 2.8 Baholar
- [ ] `/app/ratings` - baholar tarixi
- [ ] Baholanmagan buyurtmalar ko'rinadi
- [ ] Baho qo'yish ishlaydi (1-5 yulduz + izoh)

---

## 3. Admin Panel

### 3.1 Kirish
- [ ] `/admin/login` - login sahifasi
- [ ] Email + parol bilan kirish
- [ ] Noto'g'ri ma'lumotlarda xato

### 3.2 Dashboard
- [ ] Statistika kartlari to'g'ri
- [ ] Bugungi buyurtmalar
- [ ] Yaqinlashayotgan buyurtmalar
- [ ] Grafiklar ishlaydi

### 3.3 Buyurtmalar (Orders)
- [ ] Ro'yxat pagination bilan
- [ ] Filter: status, sana, master
- [ ] Qidiruv: buyurtma raqami, telefon
- [ ] Buyurtma detail:
  - [ ] Status o'zgartirish
  - [ ] Qayta rejalashtirish
  - [ ] Bekor qilish (sabab bilan)
  - [ ] Izoh qo'shish
  - [ ] To'lov linki generatsiya
- [ ] Multi-master buyurtmalar to'g'ri ko'rinadi

### 3.4 Masterlar
- [ ] Ro'yxat
- [ ] Yangi master qo'shish
- [ ] Master tahrirlash
- [ ] Rasm yuklash
- [ ] Xizmat turlari bog'lash
- [ ] Ish jadvali (schedule)
- [ ] Faollashtirish/O'chirish

### 3.5 Xizmat turlari
- [ ] Ro'yxat
- [ ] Yangi xizmat qo'shish
- [ ] Davomiyliklar va narxlar
- [ ] Rasm yuklash
- [ ] Tartib (order) o'zgartirish

### 3.6 Moylar (Oils)
- [ ] CRUD ishlaydi
- [ ] Rasm yuklash

### 3.7 Mijozlar
- [ ] Ro'yxat
- [ ] Qidiruv
- [ ] Detail: buyurtmalar tarixi
- [ ] Tahrirlash

### 3.8 Dispatcherlar
- [ ] CRUD ishlaydi
- [ ] Telegram ID bog'lash

### 3.9 Sozlamalar
- [ ] Umumiy sozlamalar
- [ ] Slot parametrlari
- [ ] SMS sozlamalari

### 3.10 Hisobotlar
- [ ] Asosiy hisobot
- [ ] Masterlar bo'yicha
- [ ] Export (Excel)

### 3.11 Scheduler Monitor
- [ ] `/admin/scheduler` sahifasi ochiladi
- [ ] Statistika kartlari
- [ ] Qo'lda ishga tushirish tugmalari
- [ ] Tarix jadvali

---

## 4. Master View (Token-based)

### 4.1 Kunlik ko'rinish
- [ ] `/m/{token}/day/{date}` ishlaydi
- [ ] Bugungi buyurtmalar ro'yxati
- [ ] Status, vaqt, manzil ko'rinadi
- [ ] Boshqa kunlarga o'tish

### 4.2 Buyurtma ko'rinishi
- [ ] `/o/{order_number}` ishlaydi
- [ ] To'liq ma'lumot
- [ ] Mijoz telefoni (qo'ng'iroq qilish)
- [ ] Manzil (xaritada ko'rish)

---

## 5. Rating System

### 5.1 Baho qo'yish
- [ ] `/rate/{token}` ishlaydi
- [ ] Buyurtma ma'lumoti ko'rinadi
- [ ] 1-5 yulduz tanlash
- [ ] Izoh kiritish
- [ ] Yuborish
- [ ] Rahmat sahifasi

---

## 6. API Testing

### 6.1 Booking API
```bash
# Slotlarni olish
GET /api/slots?date=2026-02-22&service_type_id=1&duration_id=1

# Buyurtma yaratish
POST /api/booking/submit
```

### 6.2 Auth API
```bash
# OTP yuborish
POST /auth/otp/send
{phone: "+998901234567"}

# OTP tekshirish
POST /auth/otp/verify
{phone: "+998901234567", code: "123456"}
```

---

## 7. Edge Cases

### 7.1 Slot Availability
- [ ] O'tgan vaqt slotlari ko'rinmaydi
- [ ] 2 soatdan kam qolgan slotlar disabled
- [ ] Bir vaqtda 2 ta bron - xato beradi
- [ ] Master band paytida slot ko'rinmaydi

### 7.2 Multi-Master Booking
- [ ] 2+ kishi uchun bron
- [ ] Har bir master alohida buyurtma oladi
- [ ] booking_group_id bir xil
- [ ] Total narx to'g'ri

### 7.3 Order Status Flow
```
NEW → CONFIRMED → IN_PROGRESS → COMPLETED
         ↓              ↓
     CANCELLED      CANCELLED
```

### 7.4 Auto Status Change (Scheduler)
- [ ] CONFIRMED → IN_PROGRESS (vaqti kelganda)
- [ ] IN_PROGRESS → COMPLETED (1 soat o'tgach)

---

## 8. Xatolar va Xabarlar

### 8.1 Validation Errors
- [ ] Bo'sh maydon - xato chiqadi
- [ ] Noto'g'ri format - xato chiqadi
- [ ] Xato matnlari tushunarli

### 8.2 Network Errors
- [ ] Internet yo'q - xabar chiqadi
- [ ] Server 500 - foydalanuvchiga tushunarli xato

### 8.3 Permission Errors
- [ ] Admin bo'lmagan user `/admin` ga kira olmaydi
- [ ] Boshqaning buyurtmasini ko'ra olmaydi

---

## 9. Performance

- [ ] Sahifalar 3 sekundda yuklanadi
- [ ] Rasmlar optimized
- [ ] Mobilda tez ishlaydi

---

## 10. Mobile Responsiveness

- [ ] Landing - mobil moslashgan
- [ ] MiniApp - Telegram'da to'g'ri ko'rinadi
- [ ] Admin - planshet/mobilda ishlaydi

---

## Test Accountlar

| Role | Login | Password |
|------|-------|----------|
| Admin | admin@homemassage.uz | ******* |
| Test Customer | +998900000000 | OTP: 223445 |

---

## Bug Report Template

```
**Sahifa:** /app/booking
**Qadam:** 
1. Xizmat tanladim
2. Master tanladim
3. Sana tanladim

**Kutilgan natija:** Slotlar ko'rinishi kerak
**Haqiqiy natija:** Sahifa loading'da qoldi

**Screenshot:** [rasm]
**Browser:** Chrome 120
**Device:** iPhone 14
```

---

*Oxirgi yangilash: 2026-02-22*
