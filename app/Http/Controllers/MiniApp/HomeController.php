<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\MasterRepository;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
        protected MasterRepository $masterRepository,
    ) {}

    /**
     * Mini App home - auto-login via Telegram or redirect to login
     */
    public function index(Request $request)
    {
        Log::info('MiniApp: Home page accessed', [
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);

        // Try Telegram auto-login if not authenticated
        if (!Auth::check()) {
            $telegramUser = $this->getTelegramUser($request);
            Log::info('MiniApp: Telegram user from request', ['telegram_user' => $telegramUser]);
            
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
     * Booking wizard page
     */
    public function booking()
    {
        $services = $this->serviceTypeRepository->getActiveWithDurations();
        $masters = $this->masterRepository->getActive();

        return Inertia::render('MiniApp/Booking', [
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
            'masters' => $masters->map(fn ($master) => [
                'id' => $master->id,
                'name' => $master->name,
                'photo_url' => $master->photo_url,
            ]),
        ]);
    }

    /**
     * Booking success page
     */
    public function bookingSuccess()
    {
        return Inertia::render('MiniApp/BookingSuccess');
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
            'telegram_id' => $request->telegram_id,
            'init_data' => $request->init_data ? substr($request->init_data, 0, 200) : null,
            'raw_user' => $request->raw_user,
            'user_id' => Auth::id(),
        ]);

        // Try to extract user from initData if telegram_id not provided
        $telegramId = $request->telegram_id;
        $telegramUsername = $request->telegram_username;
        $telegramFirstName = $request->telegram_first_name;
        $telegramPhotoUrl = $request->telegram_photo_url;

        // Try parsing initData
        if (!$telegramId && $request->init_data) {
            $parsed = $this->parseInitData($request->init_data);
            Log::info('MiniApp: Parsed initData', ['parsed' => $parsed]);
            
            if (isset($parsed['user'])) {
                $user = json_decode(urldecode($parsed['user']), true);
                Log::info('MiniApp: Decoded user from initData', ['user' => $user]);
                $telegramId = $user['id'] ?? null;
                $telegramUsername = $user['username'] ?? null;
                $telegramFirstName = $user['first_name'] ?? null;
                $telegramPhotoUrl = $user['photo_url'] ?? null;
            }
        }

        // Try raw_user as fallback
        if (!$telegramId && $request->raw_user) {
            $user = json_decode($request->raw_user, true);
            if ($user) {
                Log::info('MiniApp: Using raw_user', ['user' => $user]);
                $telegramId = $user['id'] ?? null;
                $telegramUsername = $user['username'] ?? null;
                $telegramFirstName = $user['first_name'] ?? null;
                $telegramPhotoUrl = $user['photo_url'] ?? null;
            }
        }

        if (!$telegramId) {
            Log::warning('MiniApp: No telegram_id available after all attempts');
            return response()->json(['error' => 'No Telegram ID provided', 'debug' => 'All extraction methods failed'], 400);
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

    /**
     * Auto-login via Telegram ID (called from frontend)
     */
    public function autoLogin(Request $request)
    {
        $telegramId = $request->input('telegram_id');
        
        Log::info('MiniApp: Auto-login attempt', [
            'telegram_id' => $telegramId,
            'has_init_data' => !empty($request->input('init_data')),
        ]);

        if (!$telegramId) {
            return response()->json(['success' => false, 'error' => 'No Telegram ID'], 400);
        }

        // Find user by telegram_id
        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            Log::info('MiniApp: No user found for telegram_id', ['telegram_id' => $telegramId]);
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

        // Login the user
        Auth::login($user, true);
        
        Log::info('MiniApp: Auto-login successful', [
            'user_id' => $user->id,
            'telegram_id' => $telegramId,
        ]);

        return response()->json(['success' => true, 'redirect' => route('miniapp.home')]);
    }

    /**
     * Logout from Mini App
     */
    public function logout(Request $request)
    {
        Log::info('MiniApp: Logout', ['user_id' => Auth::id()]);
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('miniapp.login');
    }
}
