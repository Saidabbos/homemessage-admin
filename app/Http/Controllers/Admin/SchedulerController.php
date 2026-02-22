<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchedulerRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class SchedulerController extends Controller
{
    public function index(Request $request)
    {
        $runs = SchedulerRun::query()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get stats
        $stats = [
            'total_runs' => SchedulerRun::count(),
            'successful' => SchedulerRun::where('status', 'success')->count(),
            'failed' => SchedulerRun::where('status', 'failed')->count(),
            'last_run' => SchedulerRun::latest()->first()?->created_at?->diffForHumans(),
            'records_today' => SchedulerRun::whereDate('created_at', today())
                ->sum('records_processed'),
        ];

        return Inertia::render('Admin/Scheduler/Index', [
            'runs' => $runs,
            'stats' => $stats,
        ]);
    }

    public function run(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:orders:process-statuses,otp:cleanup',
        ]);

        try {
            Artisan::call($request->command);
            $output = Artisan::output();

            return back()->with('success', "Buyruq bajarildi: {$request->command}");
        } catch (\Exception $e) {
            return back()->with('error', "Xatolik: {$e->getMessage()}");
        }
    }
}
