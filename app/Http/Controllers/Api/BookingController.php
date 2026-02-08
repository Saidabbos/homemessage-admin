<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Master;
use App\Models\ServiceType;
use App\Models\ServiceTypeDuration;
use App\Services\SlotCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        protected SlotCalculationService $slotService
    ) {}

    /**
     * GET /api/v1/services
     * Xizmat turlarini olish (multi-duration bilan)
     */
    public function services(): JsonResponse
    {
        $services = ServiceType::where('status', true)
            ->with(['activeDurations'])
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'slug' => $s->slug,
                'name' => $s->getTranslation('name', app()->getLocale()),
                'description' => $s->getTranslation('description', app()->getLocale()),
                'image_url' => $s->image_url,
                'price_range' => $s->price_range,
                'durations' => $s->activeDurations->map(fn ($d) => [
                    'id' => $d->id,
                    'duration' => $d->duration,
                    'duration_formatted' => $d->formatted_duration,
                    'price' => (int) $d->price,
                    'price_formatted' => $d->formatted_price,
                    'is_default' => $d->is_default,
                ]),
                'default_duration_id' => $s->defaultDuration()?->id,
            ]);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * GET /api/v1/masters
     * Masterlar ro'yxati (filtrlash bilan)
     */
    public function masters(Request $request): JsonResponse
    {
        $query = Master::where('status', true)->with('serviceTypes');

        // Massage type bo'yicha filtrlash
        if ($request->has('massage_type')) {
            $query->whereHas('serviceTypes', function ($q) use ($request) {
                $q->where('slug', $request->massage_type);
            });
        }

        // Pressure level bo'yicha filtrlash
        if ($request->has('pressure_level') && $request->pressure_level !== 'any') {
            $query->where(function ($q) use ($request) {
                $q->whereJsonContains('pressure_levels', $request->pressure_level)
                  ->orWhereNull('pressure_levels');
            });
        }

        $masters = $query->get()->map(fn ($m) => [
            'id' => $m->id,
            'name' => $m->full_name,
            'first_name' => $m->first_name,
            'photo_url' => $m->photo_url,
            'bio' => $m->getTranslation('bio', app()->getLocale()),
            'experience_years' => $m->experience_years,
            'shift_start' => substr($m->shift_start, 0, 5),
            'shift_end' => substr($m->shift_end, 0, 5),
            'pressure_levels' => $m->pressure_levels ?? ['soft', 'medium', 'hard'],
            'service_types' => $m->serviceTypes->pluck('slug')->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $masters,
        ]);
    }

    /**
     * GET /api/v1/dates/availability
     * Sanalar uchun slot mavjudligini tekshirish
     */
    public function dateAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'duration' => 'required|integer|min:15|max:240',
            'people_count' => 'integer|min:1|max:4',
            'master_id' => 'nullable|exists:masters,id',
            'massage_type' => 'nullable|string',
            'pressure_level' => 'nullable|string',
            'days' => 'integer|min:7|max:30',
        ]);

        $master = $request->master_id ? Master::find($request->master_id) : null;
        $days = $request->input('days', 14);

        $serviceParams = [
            'duration' => (int) $request->duration,
            'people_count' => (int) $request->input('people_count', 1),
            'massage_type' => $request->massage_type,
            'pressure_level' => $request->input('pressure_level', 'any'),
        ];

        $dates = $this->slotService->getDateAvailability($master, $serviceParams, $days);

        return response()->json([
            'success' => true,
            'data' => $dates,
        ]);
    }

    /**
     * GET /api/v1/slots
     * Berilgan sana uchun mavjud slotlarni olish
     */
    public function slots(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|min:15|max:240',
            'people_count' => 'integer|min:1|max:4',
            'master_id' => 'nullable|exists:masters,id',
            'massage_type' => 'nullable|string',
            'pressure_level' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->date);
        $master = $request->master_id ? Master::find($request->master_id) : null;

        $serviceParams = [
            'duration' => (int) $request->duration,
            'people_count' => (int) $request->input('people_count', 1),
            'massage_type' => $request->massage_type,
            'pressure_level' => $request->input('pressure_level', 'any'),
        ];

        $slots = $this->slotService->getAvailableSlots($master, $date, $serviceParams);

        // Master tanlangan bo'lsa, available_masters_count qo'shmaymiz
        if ($master) {
            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date->toDateString(),
                    'master' => [
                        'id' => $master->id,
                        'name' => $master->full_name,
                    ],
                    'slots' => $slots,
                    'slots_count' => count($slots),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
                'slots' => $slots,
                'slots_count' => count($slots),
            ],
        ]);
    }

    /**
     * GET /api/v1/slots/{slot}/masters
     * Berilgan slot uchun mavjud masterlarni olish
     */
    public function slotMasters(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'window_start' => 'required|date_format:H:i',
            'window_end' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:15|max:240',
            'people_count' => 'integer|min:1|max:4',
            'massage_type' => 'nullable|string',
            'pressure_level' => 'nullable|string',
        ]);

        $date = Carbon::parse($request->date);
        $windowStart = Carbon::parse($request->date . ' ' . $request->window_start);
        $windowEnd = Carbon::parse($request->date . ' ' . $request->window_end);
        
        $duration = (int) $request->duration;
        $peopleCount = (int) $request->input('people_count', 1);

        // Barcha mos masterlarni olish
        $query = Master::where('status', true);

        if ($request->massage_type) {
            $query->whereHas('serviceTypes', function ($q) use ($request) {
                $q->where('slug', $request->massage_type);
            });
        }

        if ($request->pressure_level && $request->pressure_level !== 'any') {
            $query->where(function ($q) use ($request) {
                $q->whereJsonContains('pressure_levels', $request->pressure_level)
                  ->orWhereNull('pressure_levels');
            });
        }

        $masters = $query->get();
        $availableMasters = [];

        foreach ($masters as $master) {
            $availability = $this->slotService->checkSlotAvailability(
                $master,
                $date,
                $windowStart,
                $windowEnd,
                $duration,
                $peopleCount
            );

            if ($availability['available']) {
                $availableMasters[] = [
                    'id' => $master->id,
                    'name' => $master->full_name,
                    'first_name' => $master->first_name,
                    'photo_url' => $master->photo_url,
                    'bio' => $master->getTranslation('bio', app()->getLocale()),
                    'experience_years' => $master->experience_years,
                    'available_durations' => $availability['available_durations'],
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'slot' => [
                    'window_start' => $request->window_start,
                    'window_end' => $request->window_end,
                    'display' => $request->window_start . '–' . $request->window_end,
                ],
                'masters' => $availableMasters,
                'masters_count' => count($availableMasters),
            ],
        ]);
    }

    /**
     * GET /api/v1/booking/calculate-price
     * Narxni hisoblash (duration_id orqali)
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $request->validate([
            'duration_id' => 'required|exists:service_type_durations,id',
            'people_count' => 'integer|min:1|max:4',
            'oil_id' => 'nullable|exists:oils,id',
        ]);

        $duration = ServiceTypeDuration::with('serviceType')->find($request->duration_id);
        $serviceType = $duration->serviceType;
        $peopleCount = (int) $request->input('people_count', 1);

        $basePrice = (int) $duration->price;
        $totalPrice = $basePrice * $peopleCount;

        // Oil qo'shimcha narxi (agar kerak bo'lsa)
        $oilPrice = 0;
        if ($request->oil_id) {
            $oil = \App\Models\Oil::find($request->oil_id);
            if ($oil && $oil->price) {
                $oilPrice = (int) $oil->price * $peopleCount;
                $totalPrice += $oilPrice;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'service_type' => [
                    'id' => $serviceType->id,
                    'name' => $serviceType->getTranslation('name', app()->getLocale()),
                ],
                'duration' => $duration->duration,
                'duration_formatted' => $duration->formatted_duration,
                'service_price' => $basePrice,
                'people_count' => $peopleCount,
                'oil_price' => $oilPrice,
                'total_price' => $totalPrice,
                'total_formatted' => number_format($totalPrice, 0, '', ' ') . ' so\'m',
                'massage_total' => $this->slotService->calculateMassageTotal($duration->duration, $peopleCount),
                'total_busy' => $this->slotService->calculateTotalBusy($duration->duration, $peopleCount),
            ],
        ]);
    }
}
