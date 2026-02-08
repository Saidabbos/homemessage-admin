# ТЗ: Механизм расчёта доступных слотов прибытия

## 1. Цель и общий принцип

Система показывает клиенту только доступные **"слоты прибытия"** (arrival windows) с шагом 30 минут.

При бронировании блокируется полный интервал занятости мастера:
- **Travel** — дорога к клиенту (30 мин)
- **Pre** — подготовка на месте (10 мин)
- **Massage** — услуга (60/90/120)
- **Post** — сборы (10 мин)

**ИТОГО:**
- 60 мин → 30+10+60+10 = **110 мин**
- 90 мин → 30+10+90+10 = **140 мин**
- 120 мин → 30+10+120+10 = **170 мин**

## 2. Константы

```php
const SLOT_STEP = 30;  // минут
const TRAVEL = 30;     // минут
const PRE = 10;        // минут
const POST = 10;       // минут
const INTER_CLIENT_BUFFER = 10; // пауза между клиентами при 2+
```

## 3. Модель времени заказа

### 3.1 Худший случай (Worst-case)
Для окна W = [Wstart, Wend]:
```
arrival_latest = Wend  // мастер может приехать в конце окна
```

### 3.2 Расчёт занятости
```
service_start_latest = arrival_latest + PRE
service_end_latest = service_start_latest + D
ready_to_leave_latest = service_end_latest + POST
```

### 3.3 Проверки

**После предыдущего заказа:**
```
prev_ready_to_leave + TRAVEL <= arrival_latest
```

**До следующего заказа:**
```
arrival_latest + PRE + D + POST + TRAVEL <= next_arrival_latest
```

**В рамках смены (если нет следующего):**
```
arrival_latest + PRE + D + POST <= shift_end
```

## 4. Формула для 2+ людей

```
massage_total = D * N + B * (N - 1)
visit_core = PRE + massage_total + POST
TOTAL_BUSY = TRAVEL + visit_core
```

**Примеры:**
- 2×60: 30+10+130+10 = **180 мин**
- 3×90: 30+10+290+10 = **340 мин**

## 5. Статусы слотов (для админки)

| Статус | Описание |
|--------|----------|
| **Поступил заказ** | На этот слот оформлен заказ |
| **Занят** | Мастер занят (Travel/Pre/Massage/Post) |
| **Свободен** | Можно принять заказ |

## 6. Pre/Post детализация

### Pre-buffer (10 мин)
- Вход/ориентация — 3 мин
- Гигиена рук + подготовка места — 3 мин
- Разложить стол/валики — 2 мин
- Материалы + мини-опрос — 2 мин

### Post-buffer (10 мин)
- Завершение + рекомендации — 1 мин
- Свернуть материалы — 1 мин
- Антисептик — 2 мин
- Сложить стол — 3 мин
- Отдых мастера — 2 мин
- Выход — 1 мин

## 7. Алгоритм генерации слотов

```php
public function generateAvailableSlots(Master $master, Carbon $date, int $duration, int $peopleCount = 1): array
{
    $slots = [];
    $shiftStart = $master->getShiftStart($date);
    $shiftEnd = $master->getShiftEnd($date);
    
    // Генерируем кандидаты с шагом 30 мин
    $current = $shiftStart->copy()->ceilMinutes(30);
    
    while ($current->lt($shiftEnd)) {
        $windowStart = $current->copy();
        $windowEnd = $windowStart->copy()->addMinutes(self::SLOT_STEP);
        
        if ($this->isSlotAvailable($master, $date, $windowStart, $windowEnd, $duration, $peopleCount)) {
            $slots[] = [
                'start' => $windowStart->format('H:i'),
                'end' => $windowEnd->format('H:i'),
                'available_durations' => $this->getAvailableDurations($master, $date, $windowEnd)
            ];
        }
        
        $current->addMinutes(self::SLOT_STEP);
    }
    
    return $slots;
}

private function isSlotAvailable(Master $master, Carbon $date, Carbon $windowStart, Carbon $windowEnd, int $duration, int $peopleCount): bool
{
    $arrivalLatest = $windowEnd;
    $visitCore = $this->calculateVisitCore($duration, $peopleCount);
    
    // Проверка предыдущего заказа
    $prevOrder = $this->getPreviousOrder($master, $date, $windowStart);
    if ($prevOrder) {
        $prevReadyToLeave = $prevOrder->service_end->addMinutes(self::POST);
        if ($prevReadyToLeave->addMinutes(self::TRAVEL)->gt($arrivalLatest)) {
            return false;
        }
    }
    
    // Проверка следующего заказа
    $nextOrder = $this->getNextOrder($master, $date, $windowEnd);
    if ($nextOrder) {
        $nextArrivalLatest = $nextOrder->arrival_window_end;
        $mustLeaveBy = $arrivalLatest->copy()->addMinutes($visitCore)->addMinutes(self::TRAVEL);
        if ($mustLeaveBy->gt($nextArrivalLatest)) {
            return false;
        }
    } else {
        // Проверка конца смены
        $endTime = $arrivalLatest->copy()->addMinutes($visitCore);
        if ($endTime->gt($master->getShiftEnd($date))) {
            return false;
        }
    }
    
    return true;
}

private function calculateVisitCore(int $duration, int $peopleCount): int
{
    $massageTotal = $duration * $peopleCount + self::INTER_CLIENT_BUFFER * ($peopleCount - 1);
    return self::PRE + $massageTotal + self::POST;
}
```

## 8. Пример расписания

| Слот | Статус | Что происходит |
|------|--------|----------------|
| 08:00–08:30 | Занят | Travel к заказу A |
| 08:30–09:00 | Поступил заказ | Заказ A — 60 мин |
| 09:00–09:30 | Занят | Massage A |
| 09:30–10:00 | Занят | Massage + Post + Travel |
| 10:00–10:30 | Свободен | Можно: 60/90/120 |
| ... | ... | ... |
