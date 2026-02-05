<?php

namespace App\Services;

use App\Models\Slot;
use App\Models\Master;
use App\Models\MasterSlotBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SlotService
{
    public function create(array $data): Slot
    {
        return Slot::create($data);
    }

    public function update(Slot $slot, array $data): Slot
    {
        $slot->update($data);
        return $slot;
    }

    public function delete(Slot $slot): bool
    {
        return $slot->delete();
    }

    /**
     * Generate default slots (09:00 - 21:00, 1 hour each, 30 min gap)
     */
    public function generateDefaultSlots(): void
    {
        $slots = [
            ['start' => '09:00', 'end' => '10:00'],
            ['start' => '10:30', 'end' => '11:30'],
            ['start' => '12:00', 'end' => '13:00'],
            ['start' => '13:30', 'end' => '14:30'],
            ['start' => '15:00', 'end' => '16:00'],
            ['start' => '16:30', 'end' => '17:30'],
            ['start' => '18:00', 'end' => '19:00'],
            ['start' => '19:30', 'end' => '20:30'],
        ];

        foreach ($slots as $index => $slot) {
            Slot::firstOrCreate(
                ['start_time' => $slot['start'], 'end_time' => $slot['end']],
                ['sort_order' => $index + 1, 'is_active' => true]
            );
        }
    }

    /**
     * Get master's slot availability for a specific date
     */
    public function getMasterSlotsForDate(Master $master, string $date): array
    {
        $slots = Slot::active()->ordered()->get();
        $bookings = MasterSlotBooking::where('master_id', $master->id)
            ->where('date', $date)
            ->get()
            ->keyBy('slot_id');

        return $slots->map(function ($slot) use ($bookings, $date) {
            $booking = $bookings->get($slot->id);

            return [
                'id' => $slot->id,
                'start_time' => substr($slot->start_time, 0, 5),
                'end_time' => substr($slot->end_time, 0, 5),
                'time_range' => $slot->time_range,
                'status' => $booking ? $booking->status : MasterSlotBooking::STATUS_FREE,
                'status_label' => $booking ? $booking->status_label : 'Bo\'sh',
                'status_color' => $booking ? $booking->status_color : 'success',
                'booking_id' => $booking?->id,
                'order_id' => $booking?->order_id,
                'block_reason' => $booking?->block_reason,
            ];
        })->toArray();
    }

    /**
     * Block a slot for a master on a specific date
     */
    public function blockSlot(Master $master, Slot $slot, string $date, ?string $reason = null): MasterSlotBooking
    {
        return DB::transaction(function () use ($master, $slot, $date, $reason) {
            $booking = MasterSlotBooking::firstOrNew([
                'master_id' => $master->id,
                'slot_id' => $slot->id,
                'date' => $date,
            ]);

            if ($booking->exists && !in_array($booking->status, [MasterSlotBooking::STATUS_FREE])) {
                throw new \Exception('Bu slot allaqachon band yoki bloklangan');
            }

            $booking->fill([
                'status' => MasterSlotBooking::STATUS_BLOCKED,
                'block_reason' => $reason,
            ]);
            $booking->save();

            return $booking;
        });
    }

    /**
     * Unblock a slot
     */
    public function unblockSlot(MasterSlotBooking $booking): bool
    {
        if ($booking->status !== MasterSlotBooking::STATUS_BLOCKED) {
            throw new \Exception('Faqat bloklangan slotlarni ochish mumkin');
        }

        return $booking->delete();
    }

    /**
     * Block all slots for a master on a specific date (day off)
     */
    public function blockDay(Master $master, string $date, ?string $reason = null): int
    {
        $slots = Slot::active()->get();
        $count = 0;

        foreach ($slots as $slot) {
            try {
                $this->blockSlot($master, $slot, $date, $reason);
                $count++;
            } catch (\Exception $e) {
                // Slot already booked, skip
            }
        }

        return $count;
    }

    /**
     * Unblock all slots for a master on a specific date
     */
    public function unblockDay(Master $master, string $date): int
    {
        return MasterSlotBooking::where('master_id', $master->id)
            ->where('date', $date)
            ->where('status', MasterSlotBooking::STATUS_BLOCKED)
            ->delete();
    }

    /**
     * Get weekly calendar data for a master
     */
    public function getMasterWeeklyCalendar(Master $master, string $startDate): array
    {
        $start = Carbon::parse($startDate);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $days[] = [
                'date' => $date->toDateString(),
                'day_name' => $date->locale('uz')->dayName,
                'day_short' => $date->locale('uz')->shortDayName,
                'day_number' => $date->day,
                'is_today' => $date->isToday(),
                'slots' => $this->getMasterSlotsForDate($master, $date->toDateString()),
            ];
        }

        return $days;
    }
}
