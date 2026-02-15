# Mock Payment Services

To'lov integratsiyasini test qilish uchun mock servislar.

## Umumiy Ma'lumot

Mock servislar real Payme va Click provayderlarini simulyatsiya qiladi. Ular webhook endpointlariga request yuborib, to'liq payment flow'ni test qilish imkonini beradi.

**Muhim:** Production muhitda `PAYME_MOCK_ENABLED=true` va `CLICK_MOCK_ENABLED=true` bo'lmasa, mock servislar ishlamaydi.

## Environment Sozlamalari

`.env` faylga qo'shing:

```env
# Payme Mock
PAYME_MOCK_ENABLED=true
PAYME_KEY=test_key

# Click Mock
CLICK_MOCK_ENABLED=true
CLICK_SERVICE_ID=12345
CLICK_SECRET_KEY=test_secret
```

## API Endpoints

### Payme Mock

| Method | Endpoint | Tavsif |
|--------|----------|--------|
| POST | `/api/mock/payme/simulate` | To'lovni simulyatsiya qilish |
| GET | `/api/mock/payme/status` | Order to'lov statusini ko'rish |
| POST | `/api/mock/payme/trigger` | Alohida Payme method chaqirish |
| POST | `/api/mock/payme/reset` | Order to'lov holatini tozalash |

### Click Mock

| Method | Endpoint | Tavsif |
|--------|----------|--------|
| POST | `/api/mock/click/simulate` | To'lovni simulyatsiya qilish |
| GET | `/api/mock/click/status` | Order to'lov statusini ko'rish |
| POST | `/api/mock/click/trigger` | Alohida Click method chaqirish |
| POST | `/api/mock/click/reset` | Order to'lov holatini tozalash |

## Ishlatish

### 1. To'lovni Simulyatsiya Qilish

**Payme - Muvaffaqiyatli to'lov:**
```bash
curl -X POST https://hm.make-it.uz/api/mock/payme/simulate \
  -H "Content-Type: application/json" \
  -d '{"order_id": 123, "scenario": "success"}'
```

**Click - Muvaffaqiyatli to'lov:**
```bash
curl -X POST https://hm.make-it.uz/api/mock/click/simulate \
  -H "Content-Type: application/json" \
  -d '{"order_id": 123, "scenario": "success"}'
```

### 2. Scenariolar

| Scenario | Tavsif |
|----------|--------|
| `success` | To'lov muvaffaqiyatli o'tkaziladi |
| `cancel` | To'lov bekor qilinadi |
| `timeout` | Tranzaksiya yaratiladi, lekin to'lov qilinmaydi (faqat Payme) |
| `error` | Xatolik bilan tugaydi (faqat Click) |

**Bekor qilish:**
```bash
curl -X POST https://hm.make-it.uz/api/mock/payme/simulate \
  -H "Content-Type: application/json" \
  -d '{"order_id": 123, "scenario": "cancel"}'
```

### 3. Status Tekshirish

```bash
curl "https://hm.make-it.uz/api/mock/payme/status?order_id=123"
```

**Javob:**
```json
{
  "success": true,
  "order": {
    "id": 123,
    "order_number": "HM-20260215-001",
    "total_amount": 150000,
    "payment_status": "PAID"
  },
  "payment": {
    "id": 45,
    "transaction_id": "PAY-ABC123XYZ",
    "external_id": "mock_uuid",
    "status": "PAID",
    "amount": 150000,
    "provider": "payme",
    "paid_at": "2026-02-15T13:45:00Z"
  }
}
```

### 4. Qayta Test Uchun Reset

```bash
curl -X POST https://hm.make-it.uz/api/mock/payme/reset \
  -H "Content-Type: application/json" \
  -d '{"order_id": 123}'
```

Bu order uchun barcha to'lovlarni o'chirib, `payment_status`ni `UNPAID`ga qaytaradi.

### 5. Debug Uchun Alohida Method Chaqirish

**Payme CheckPerformTransaction:**
```bash
curl -X POST https://hm.make-it.uz/api/mock/payme/trigger \
  -H "Content-Type: application/json" \
  -d '{
    "method": "CheckPerformTransaction",
    "params": {
      "amount": 15000000,
      "account": {"order_id": 123}
    }
  }'
```

**Click Prepare:**
```bash
curl -X POST https://hm.make-it.uz/api/mock/click/trigger \
  -H "Content-Type: application/json" \
  -d '{
    "method": "prepare",
    "params": {
      "click_trans_id": "test_123",
      "service_id": "12345",
      "merchant_trans_id": 123,
      "amount": 150000,
      "sign_time": "2026-02-15 13:45:00"
    }
  }'
```

## Payment Flow

### Payme Flow

```
1. CheckPerformTransaction → Order mavjudligini tekshirish
2. CreateTransaction → Payment record yaratish
3. PerformTransaction → To'lovni amalga oshirish
```

### Click Flow

```
1. Prepare → Payment record yaratish
2. Complete → To'lovni amalga oshirish
```

## Loglar

Barcha mock requestlar `storage/logs/laravel.log` faylga yoziladi:

```
[2026-02-15 13:45:00] local.INFO: Mock Payme: Starting simulation {"order_id":123,"scenario":"success"}
[2026-02-15 13:45:00] local.INFO: Mock Payme: Sending request {"url":"https://hm.make-it.uz/api/webhook/payme","method":"CheckPerformTransaction"}
[2026-02-15 13:45:00] local.INFO: Mock Payme: Response received {"method":"CheckPerformTransaction","status":200}
```

## Xavfsizlik

- Production muhitda mock servislar faqat `*_MOCK_ENABLED=true` bo'lganda ishlaydi
- `reset` endpoint faqat non-production muhitda ishlaydi
- Barcha requestlar logga yoziladi

## Tegishli Fayllar

- `app/Http/Controllers/Api/MockPaymeController.php`
- `app/Http/Controllers/Api/MockClickController.php`
- `app/Http/Controllers/Webhook/PaymeController.php`
- `app/Http/Controllers/Webhook/ClickController.php`
- `config/services.php`
