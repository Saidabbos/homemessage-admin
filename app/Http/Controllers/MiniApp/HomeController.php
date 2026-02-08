<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
    ) {}

    /**
     * Mini App home - auto-login via Telegram or redirect to login
     */
    public function index(Request $request)
    {
        Log::info('MiniApp: Home page accessed');

        // Try Telegram auto-login if not authenticated
        if (!Auth::check()) {
            $telegramUser = $this->getTelegramUser($request);
            
            if ($telegramUser && isset($telegramUser['id'])) {
                $user = User::where('telegram_id', $telegramUser['id'])->first();
                
                if ($user) {
                    Auth::login($user, true);
                    Log::info('MiniApp: Auto-login via Telegram', ['user_id' => $user->id, 'telegram_id' => $telegramUser['id']]);
                }
            }
        }

        // Show login if still not authenticated
        if (!Auth::check()) {
            return redirect()->route('miniapp.login');
        }

        $services = $this->serviceTypeRepository->getActiveWithDurations();

        return Inertia::render('MiniApp/Home', [
            'services' => $services->map(fn ($service) => [
                'id' => $service->id,
                'slug' => $service->slug,
                'name' => $service->name,
                'description' => $service->description,
                'image_url' => $service->image_url,
                'durations' => $service->durations->where('status', true)->map(fn ($d) => [
                    'id' => $d->id,
                    'duration' => $d->duration,
                    'price' => $d->price,
                    'is_default' => $d->is_default,
                ])->values(),
            ]),
        ]);
    }

    /**
     * Mini App login page
     */
    public function login(Request $request)
    {
        Log::info('MiniApp: Login page accessed');

        // Try Telegram auto-login
        if (!Auth::check()) {
            $telegramUser = $this->getTelegramUser($request);
            
            if ($telegramUser && isset($telegramUser['id'])) {
                $user = User::where('telegram_id', $telegramUser['id'])->first();
                
                if ($user) {
                    Auth::login($user, true);
                    Log::info('MiniApp: Auto-login via Telegram from login page', ['user_id' => $user->id]);
                    return redirect()->route('miniapp.home');
                }
            }
        }

        // Redirect to home if already authenticated
        if (Auth::check()) {
            return redirect()->route('miniapp.home');
        }

        // Pass Telegram data to frontend for linking after OTP
        $telegramUser = $this->getTelegramUser($request);

        return Inertia::render('MiniApp/Login', [
            'telegramUser' => $telegramUser,
        ]);
    }

    /**
     * Link Telegram account after OTP verification
     */
    public function linkTelegram(Request $request)
    {
        Log::info('MiniApp: Link Telegram request received', [
            'all_data' => $request->all(),
            'user_id' => Auth::id(),
        ]);

        // Try to extract user from initData if telegram_id not provided
        $telegramId = $request->telegram_id;
        $telegramUsername = $request->telegram_username;
        $telegramFirstName = $request->telegram_first_name;
        $telegramPhotoUrl = $request->telegram_photo_url;

        if (!$telegramId && $request->init_data) {
            $parsed = $this->parseInitData($request->init_data);
            Log::info('MiniApp: Parsed initData', ['parsed' => $parsed]);
            
            if (isset($parsed['user'])) {
                $user = json_decode($parsed['user'], true);
                $telegramId = $user['id'] ?? null;
                $telegramUsername = $user['username'] ?? null;
                $telegramFirstName = $user['first_name'] ?? null;
                $telegramPhotoUrl = $user['photo_url'] ?? null;
            }
        }

        if (!$telegramId) {
            Log::warning('MiniApp: No telegram_id available');
            return response()->json(['error' => 'No Telegram ID provided'], 400);
        }

        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Check if this Telegram ID is already linked to another account
        $existingUser = User::where('telegram_id', $telegramId)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            Log::warning('MiniApp: Telegram ID already linked to another account', [
                'telegram_id' => $telegramId,
                'current_user' => $user->id,
                'existing_user' => $existingUser->id,
            ]);
            return response()->json(['error' => 'Telegram account already linked'], 409);
        }

        $user->update([
            'telegram_id' => $telegramId,
            'telegram_username' => $telegramUsername,
            'telegram_first_name' => $telegramFirstName,
            'telegram_photo_url' => $telegramPhotoUrl,
        ]);

        Log::info('MiniApp: Telegram account linked', [
            'user_id' => $user->id,
            'telegram_id' => $telegramId,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Parse Telegram initData string
     */
    protected function parseInitData(string $initData): array
    {
        $result = [];
        parse_str($initData, $result);
        return $result;
    }

    /**
     * Extract Telegram user data from request
     */
    protected function getTelegramUser(Request $request): ?array
    {
        // Try to get from query params (Telegram WebApp passes initData)
        $initData = $request->query('tgWebAppData') ?? $request->query('initData');
        
        if ($initData) {
            parse_str($initData, $parsed);
            if (isset($parsed['user'])) {
                return json_decode($parsed['user'], true);
            }
        }

        // Try from header (custom implementation)
        $telegramUser = $request->header('X-Telegram-User');
        if ($telegramUser) {
            return json_decode($telegramUser, true);
        }

        return null;
    }
}
