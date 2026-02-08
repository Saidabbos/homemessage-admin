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
    public const SLOT_STEP = 30;              // Kelish oynasi qadami
    public const TRAVEL = 30;                 // Yo'l vaqti
    public const PRE = 10;                    // Tayyorgarlik (stol, yog', opros)
    public const POST = 10;                   // Yig'ishtirish
    public const INTER_CLIENT_BUFFER = 10;    // 2+ kishi orasidagi pauza
    
    // Minimal oldindan buyurtma (soatlarda)
    public const MIN_LEAD_TIME_HOURS = 6;

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
        
        // Slotlarni 30 daqiqalik qadamda generatsiya qilamiz
        $current = $shiftStart->copy()->ceilMinutes(self::SLOT_STEP);
        
        while ($current->lt($shiftEnd)) {
            $windowStart = $current->copy();
            $windowEnd = $windowStart->copy()->addMinutes(self::SLOT_STEP);
            
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
            
            if ($availability['available']) {
                $slots[] = [
                    'window_start' => $windowStart->format('H:i'),
                    'window_end' => $windowEnd->format('H:i'),
                    'display' => $windowStart->format('H:i') . '–' . $windowEnd->format('H:i'),
                    'available_durations' => $availability['available_durations'],
                    'master_id' => $master->id,
                ];
            }
            
            $current->addMinutes(self::SLOT_STEP);
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
            $prevReadyToLeave = $this->getOrderEndTime($prevOrder)->addMinutes(self::POST);
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
        
        foreach ([60, 90, 120] as $duration) {
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
            ->filter(fn ($order) => Carbon::parse($order->arrival_window_end)->lt($time))
            ->sortByDesc('arrival_window_end')
            ->first();
    }

    /**
     * Berilgan vaqtdan keyingi buyurtmani topish
     */
    protected function findNextOrder(Collection $orders, Carbon $time): ?Order
    {
        return $orders
            ->filter(fn ($order) => Carbon::parse($order->arrival_window_start)->gte($time))
            ->sortBy('arrival_window_start')
            ->first();
    }

    /**
     * Buyurtma tugash vaqtini olish
     */
    protected function getOrderEndTime(Order $order): Carbon
    {
        $arrivalLatest = Carbon::parse($order->arrival_window_end);
        $duration = $order->serviceType?->duration ?? 60;
        $peopleCount = $order->people_count ?? 1;
        
        $visitCore = $this->calculateVisitCore($duration, $peopleCount);
        
        return $arrivalLatest->copy()->addMinutes(self::PRE + $this->calculateMassageTotal($duration, $peopleCount));
    }

    /**
     * Ikki slot oralig'ida overlap bormi?
     */
    protected function slotsOverlap(Carbon $start1, Carbon $end1, Order $order, Carbon $date): bool
    {
        $orderStart = Carbon::parse($date->format('Y-m-d') . ' ' . $order->arrival_window_start);
        $orderBusyEnd = $this->getOrderEndTime($order)->addMinutes(self::POST);
        
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
