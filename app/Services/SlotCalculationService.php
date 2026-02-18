<?php

namespace App\Services;

use App\Models\Master;
use App\Models\Order;
use App\Models\ServiceType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * SlotCalculationService
 * 
 * TZ asosida arrival window (kelish oynasi) hisoblash.
 * 
 * Mijoz 30 daqiqalik "kelish oynasini" tanlaydi (masalan, 10:00-10:30).
 * Master shu oraliqda yetib keladi.
 * 
 * Formulalar:
 * - TOTAL_BUSY = TRAVEL + PRE + MASSAGE + POST
 * - 2+ kishi: massage_total = D×N + BUFFER×(N-1)
 */
class SlotCalculationService
{
    // Konstantalar (daqiqalarda)
    public const SLOT_WINDOW = 30;            // Kelish oynasi kengligi (30 daqiqa)
    public const SLOT_INTERVAL = 60;          // Slotlar orasidagi interval (har soat boshida)
    public const TRAVEL = 30;                 // Yo'l vaqti
    public const PRE = 10;                    // Tayyorgarlik (stol, yog', opros)
    public const POST = 10;                   // Yig'ishtirish
    public const INTER_CLIENT_BUFFER = 10;    // 2+ kishi orasidagi pauza
    
    // Minimal oldindan buyurtma (soatlarda)
    public const MIN_LEAD_TIME_HOURS = 2;

    /**
     * Berilgan sana va parametrlar uchun mavjud slotlarni qaytaradi
     * 
     * @param Master|null $master Konkret master (null = barcha masterlar)
     * @param Carbon $date Sana
     * @param array $serviceParams ['duration' => 60, 'people_count' => 1, 'massage_type' => 'relax', 'pressure_level' => 'medium']
     * @return array
     */
    public function getAvailableSlots(?Master $master, Carbon $date, array $serviceParams): array
    {
        $duration = $serviceParams['duration'] ?? 60;
        $peopleCount = $serviceParams['people_count'] ?? 1;
        $massageType = $serviceParams['massage_type'] ?? null;
        $pressureLevel = $serviceParams['pressure_level'] ?? 'any';
        
        // Agar konkret master tanlangan
        if ($master) {
            return $this->getSlotsForMaster($master, $date, $duration, $peopleCount);
        }
        
        // "Hammasi" rejimi - barcha masterlardan slotlarni yig'amiz
        return $this->getSlotsForAllMasters($date, $duration, $peopleCount, $massageType, $pressureLevel);
    }

    /**
     * Konkret master uchun mavjud slotlar
     */
    public function getSlotsForMaster(Master $master, Carbon $date, int $duration, int $peopleCount = 1): array
    {
        $slots = [];
        $shiftStart = $this->parseTime($date, $master->shift_start ?? '08:00');
        $shiftEnd = $this->parseTime($date, $master->shift_end ?? '22:00');
        
        // Slotlarni har soat boshida generatsiya qilamiz (09:00, 10:00, 11:00...)
        // Smena boshini eng yaqin soatga oshiramiz
        $current = $shiftStart->copy()->ceilHour();
        
        while ($current->lt($shiftEnd)) {
            $windowStart = $current->copy();
            $windowEnd = $windowStart->copy()->addMinutes(self::SLOT_WINDOW);
            
            // Slot oynasi smena ichida bo'lishi kerak
            if ($windowEnd->gt($shiftEnd)) {
                break;
            }
            
            // Slotni tekshiramiz
            $availability = $this->checkSlotAvailability(
                $master, 
                $date, 
                $windowStart, 
                $windowEnd, 
                $duration, 
                $peopleCount
            );
            
            $slots[] = [
                'start' => $windowStart->format('H:i'),
                'end' => $windowEnd->format('H:i'),
                'label' => $windowStart->format('H:i'),
                'window_start' => $windowStart->format('H:i'),
                'window_end' => $windowEnd->format('H:i'),
                'display' => $windowStart->format('H:i') . '–' . $windowEnd->format('H:i'),
                'available' => $availability['available'],
                'disabled' => !$availability['available'],
                'reason' => $availability['reason'] ?? null,
                'available_durations' => $availability['available_durations'] ?? [],
                'master_id' => $master->id,
            ];
            
            $current->addMinutes(self::SLOT_INTERVAL);
        }
        
        return $slots;
    }

    /**
     * Barcha masterlar uchun slotlarni yig'ish
     */
    public function getSlotsForAllMasters(
        Carbon $date, 
        int $duration, 
        int $peopleCount, 
        ?string $massageType, 
        string $pressureLevel
    ): array {
        $masters = $this->getEligibleMasters($massageType, $pressureLevel);
        $allSlots = [];
        
        foreach ($masters as $master) {
            $masterSlots = $this->getSlotsForMaster($master, $date, $duration, $peopleCount);
            
            foreach ($masterSlots as $slot) {
                $key = $slot['window_start'];
                
                if (!isset($allSlots[$key])) {
                    $allSlots[$key] = [
                        'window_start' => $slot['window_start'],
                        'window_end' => $slot['window_end'],
                        'display' => $slot['display'],
                        'available_masters_count' => 0,
                        'master_ids' => [],
                    ];
                }
                
                $allSlots[$key]['available_masters_count']++;
                $allSlots[$key]['master_ids'][] = $master->id;
            }
        }
        
        return array_values($allSlots);
    }

    /**
     * Slot mavjudligini tekshirish (TZ algoritmi)
     */
    public function checkSlotAvailability(
        Master $master,
        Carbon $date,
        Carbon $windowStart,
        Carbon $windowEnd,
        int $duration,
        int $peopleCount = 1
    ): array {
        $arrivalLatest = $windowEnd->copy();
        $visitCore = $this->calculateVisitCore($duration, $peopleCount);
        
        // 6 soatlik qoida - minimal oldindan buyurtma
        // Kelish vaqtigacha kamida 6 soat qolishi kerak
        $hoursUntilArrival = now()->diffInHours($arrivalLatest, false);
        if ($hoursUntilArrival < self::MIN_LEAD_TIME_HOURS) {
            return ['available' => false, 'reason' => 'too_soon'];
        }
        
        // Master buyurtmalarini olish
        $orders = $this->getMasterOrdersForDate($master, $date);
        
        // A. Oldingi buyurtmadan keyin yetib kelish
        $prevOrder = $this->findPreviousOrder($orders, $windowStart);
        if ($prevOrder) {
            $prevReadyToLeave = $this->getOrderEndTime($prevOrder, $date)->addMinutes(self::POST);
            $canArriveBy = $prevReadyToLeave->copy()->addMinutes(self::TRAVEL);
            
            if ($canArriveBy->gt($arrivalLatest)) {
                return ['available' => false, 'reason' => 'conflict_with_previous'];
            }
        }
        
        // B. Keyingi buyurtmaga yetishish
        $nextOrder = $this->findNextOrder($orders, $windowEnd);
        if ($nextOrder) {
            $nextArrivalLatest = Carbon::parse($date->format('Y-m-d') . ' ' . $nextOrder->arrival_window_end);
            $mustLeaveBy = $arrivalLatest->copy()
                ->addMinutes($visitCore)
                ->addMinutes(self::TRAVEL);
            
            if ($mustLeaveBy->gt($nextArrivalLatest)) {
                return ['available' => false, 'reason' => 'conflict_with_next'];
            }
        } else {
            // Smena oxirini tekshirish
            $shiftEnd = $this->parseTime($date, $master->shift_end ?? '22:00');
            $endTime = $arrivalLatest->copy()->addMinutes($visitCore);
            
            if ($endTime->gt($shiftEnd)) {
                return ['available' => false, 'reason' => 'exceeds_shift'];
            }
        }
        
        // C. Mavjud buyurtma bilan to'qnashuv
        foreach ($orders as $order) {
            if ($this->slotsOverlap($windowStart, $windowEnd, $order, $date)) {
                return ['available' => false, 'reason' => 'slot_occupied'];
            }
        }
        
        // Slotda qaysi davomiyliklar sig'ishini hisoblaymiz
        $availableDurations = $this->getAvailableDurations($master, $date, $windowEnd, $orders, $peopleCount);
        
        if (!in_array($duration, $availableDurations)) {
            return ['available' => false, 'reason' => 'duration_not_available'];
        }
        
        return [
            'available' => true,
            'available_durations' => $availableDurations,
        ];
    }

    /**
     * Visit core hisoblash (PRE + MASSAGE + POST)
     */
    public function calculateVisitCore(int $duration, int $peopleCount = 1): int
    {
        $massageTotal = $this->calculateMassageTotal($duration, $peopleCount);
        return self::PRE + $massageTotal + self::POST;
    }

    /**
     * Massaj vaqtini hisoblash (2+ kishi uchun bufferlar bilan)
     */
    public function calculateMassageTotal(int $duration, int $peopleCount = 1): int
    {
        if ($peopleCount <= 1) {
            return $duration;
        }
        
        // D×N + BUFFER×(N-1)
        return ($duration * $peopleCount) + (self::INTER_CLIENT_BUFFER * ($peopleCount - 1));
    }

    /**
     * To'liq band vaqtni hisoblash (TRAVEL + visit_core)
     */
    public function calculateTotalBusy(int $duration, int $peopleCount = 1): int
    {
        return self::TRAVEL + $this->calculateVisitCore($duration, $peopleCount);
    }

    /**
     * Slotda qaysi davomiyliklar sig'ishini aniqlash
     */
    protected function getAvailableDurations(
        Master $master, 
        Carbon $date, 
        Carbon $windowEnd, 
        Collection $orders, 
        int $peopleCount
    ): array {
        $shiftEnd = $this->parseTime($date, $master->shift_end ?? '22:00');
        $nextOrder = $this->findNextOrder($orders, $windowEnd);
        
        $availableDurations = [];
        
        foreach ([30, 45, 60, 90, 120] as $duration) {
            $visitCore = $this->calculateVisitCore($duration, $peopleCount);
            $endTime = $windowEnd->copy()->addMinutes($visitCore);
            
            // Smena ichida sig'adimi?
            if ($endTime->gt($shiftEnd)) {
                continue;
            }
            
            // Keyingi buyurtmadan oldin sig'adimi?
            if ($nextOrder) {
                $nextArrivalLatest = Carbon::parse($date->format('Y-m-d') . ' ' . $nextOrder->arrival_window_end);
                $mustLeaveBy = $endTime->copy()->addMinutes(self::TRAVEL);
                
                if ($mustLeaveBy->gt($nextArrivalLatest)) {
                    continue;
                }
            }
            
            $availableDurations[] = $duration;
        }
        
        return $availableDurations;
    }

    /**
     * Massage type va pressure level bo'yicha mos masterlarni olish
     */
    protected function getEligibleMasters(?string $massageType, string $pressureLevel): Collection
    {
        $query = Master::where('status', true);
        
        // Massage type bo'yicha filtrlash
        if ($massageType) {
            $query->whereHas('serviceTypes', function ($q) use ($massageType) {
                $q->where('slug', $massageType);
            });
        }
        
        // Pressure level bo'yicha filtrlash
        if ($pressureLevel && $pressureLevel !== 'any') {
            $query->where(function ($q) use ($pressureLevel) {
                $q->whereJsonContains('pressure_levels', $pressureLevel)
                  ->orWhereNull('pressure_levels');
            });
        }
        
        return $query->get();
    }

    /**
     * Master buyurtmalarini olish
     */
    protected function getMasterOrdersForDate(Master $master, Carbon $date): Collection
    {
        return Order::where('master_id', $master->id)
            ->whereDate('booking_date', $date)
            ->whereNotIn('status', [Order::STATUS_CANCELLED])
            ->get();
    }

    /**
     * Berilgan vaqtdan oldingi buyurtmani topish
     */
    protected function findPreviousOrder(Collection $orders, Carbon $time): ?Order
    {
        return $orders
            ->filter(function ($order) use ($time) {
                $orderDate = Carbon::parse($order->booking_date)->format('Y-m-d');
                $orderEnd = Carbon::parse($orderDate . ' ' . $order->arrival_window_end);
                return $orderEnd->lt($time);
            })
            ->sortByDesc('arrival_window_end')
            ->first();
    }

    /**
     * Berilgan vaqtdan keyingi buyurtmani topish
     */
    protected function findNextOrder(Collection $orders, Carbon $time): ?Order
    {
        return $orders
            ->filter(function ($order) use ($time) {
                $orderDate = Carbon::parse($order->booking_date)->format('Y-m-d');
                $orderStart = Carbon::parse($orderDate . ' ' . $order->arrival_window_start);
                return $orderStart->gte($time);
            })
            ->sortBy('arrival_window_start')
            ->first();
    }

    /**
     * Buyurtma tugash vaqtini olish
     * 
     * Hisoblash: arrival_window_end + PRE + massage_total
     * 
     * Misol (90 daqiqalik massaj, 1 kishi):
     * - arrival_window_end: 13:30
     * - PRE: 10 daqiqa
     * - massage_total: 90 daqiqa
     * - Natija: 13:30 + 10 + 90 = 15:10
     */
    protected function getOrderEndTime(Order $order, ?Carbon $date = null): Carbon
    {
        // Use order's booking_date if date not provided
        $bookingDate = $date ?? Carbon::parse($order->booking_date);
        $arrivalLatest = Carbon::parse($bookingDate->format('Y-m-d') . ' ' . $order->arrival_window_end);
        
        // duration field is 'duration' not 'minutes' in ServiceTypeDuration model
        $duration = $order->duration?->duration ?? $order->serviceType?->duration ?? 60;
        $peopleCount = $order->people_count ?? 1;
        
        return $arrivalLatest->copy()->addMinutes(self::PRE + $this->calculateMassageTotal($duration, $peopleCount));
    }

    /**
     * Ikki slot oralig'ida overlap bormi?
     */
    protected function slotsOverlap(Carbon $start1, Carbon $end1, Order $order, Carbon $date): bool
    {
        $orderStart = Carbon::parse($date->format('Y-m-d') . ' ' . $order->arrival_window_start);
        $orderBusyEnd = $this->getOrderEndTime($order, $date)->addMinutes(self::POST);
        
        // Travel vaqtini ham hisobga olamiz
        $orderBusyStart = $orderStart->copy()->subMinutes(self::TRAVEL);
        
        // Overlap tekshirish
        return $start1->lt($orderBusyEnd) && $end1->gt($orderBusyStart);
    }

    /**
     * Vaqtni parse qilish
     */
    protected function parseTime(Carbon $date, string $time): Carbon
    {
        return Carbon::parse($date->format('Y-m-d') . ' ' . $time);
    }

    /**
     * Sanalar uchun slot mavjudligini tekshirish (14 kun)
     */
    public function getDateAvailability(
        ?Master $master, 
        array $serviceParams, 
        int $daysAhead = 14
    ): array {
        $dates = [];
        $today = Carbon::today();
        
        for ($i = 0; $i < $daysAhead; $i++) {
            $date = $today->copy()->addDays($i);
            $slots = $this->getAvailableSlots($master, $date, $serviceParams);
            
            $dates[] = [
                'date' => $date->toDateString(),
                'display' => $date->locale('uz')->isoFormat('D MMM'),
                'day_name' => $date->locale('uz')->dayName,
                'is_today' => $date->isToday(),
                'is_tomorrow' => $date->isTomorrow(),
                'has_slots' => count($slots) > 0,
                'slots_count' => count($slots),
            ];
        }
        
        return $dates;
    }
}
