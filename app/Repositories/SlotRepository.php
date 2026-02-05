<?php

namespace App\Repositories;

use App\Models\Slot;
use App\Models\MasterSlotBooking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class SlotRepository extends BaseRepository
{
    protected function getModelClass(): string
    {
        return Slot::class;
    }

    public function getFilteredPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('start_time', 'like', "%{$search}%")
                  ->orWhere('end_time', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        return $this->paginate($query->ordered(), $perPage);
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->active()
            ->ordered()
            ->get();
    }

    public function getAll(): Collection
    {
        return $this->query()
            ->ordered()
            ->get();
    }

    /**
     * Get available slots for a specific master and date
     */
    public function getAvailableSlotsForDate(int $masterId, string $date): array
    {
        // Get all active slots
        $slots = $this->getActive();

        // Get booked slots for this master on this date
        $bookedSlotIds = MasterSlotBooking::where('master_id', $masterId)
            ->where('date', $date)
            ->whereIn('status', [
                MasterSlotBooking::STATUS_PENDING,
                MasterSlotBooking::STATUS_RESERVED,
                MasterSlotBooking::STATUS_BLOCKED,
            ])
            ->pluck('slot_id')
            ->toArray();

        // Check minimum booking time (2 hours before)
        $now = Carbon::now();
        $bookingDate = Carbon::parse($date);
        $isToday = $bookingDate->isToday();

        return $slots->map(function ($slot) use ($bookedSlotIds, $isToday, $now) {
            $isBooked = in_array($slot->id, $bookedSlotIds);

            // If today, check if slot time has passed or is within 2 hours
            $isAvailable = !$isBooked;
            if ($isToday && $isAvailable) {
                $slotTime = Carbon::parse($slot->start_time);
                $slotDateTime = Carbon::today()->setTime($slotTime->hour, $slotTime->minute);
                $hoursUntilSlot = $now->diffInHours($slotDateTime, false);
                $isAvailable = $hoursUntilSlot >= 2;
            }

            return [
                'id' => $slot->id,
                'start_time' => substr($slot->start_time, 0, 5),
                'end_time' => substr($slot->end_time, 0, 5),
                'is_available' => $isAvailable,
            ];
        })->toArray();
    }

    /**
     * Get available slots for a master over multiple days
     */
    public function getAvailableSlotsForMaster(int $masterId, string $startDate, int $days = 7): array
    {
        $result = [];
        $date = Carbon::parse($startDate);

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $date->copy()->addDays($i);
            $dateStr = $currentDate->toDateString();

            $slots = $this->getAvailableSlotsForDate($masterId, $dateStr);
            $availableCount = collect($slots)->where('is_available', true)->count();

            $result[] = [
                'date' => $dateStr,
                'day_name' => $currentDate->locale('uz')->dayName,
                'day_short' => $currentDate->locale('uz')->shortDayName,
                'is_today' => $currentDate->isToday(),
                'available_slots_count' => $availableCount,
                'slots' => $slots,
            ];
        }

        return $result;
    }
}
