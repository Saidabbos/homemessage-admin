<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Mappers\OrderMapper;
use App\Models\User;
use App\Repositories\MasterRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        protected ServiceTypeRepository $serviceTypeRepository,
        protected MasterRepository $masterRepository,
        protected PaymentService $paymentService,
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

        $user = Auth::user();
        $services = $this->serviceTypeRepository->getActiveWithDurations();

        // Check if user needs to enter name (from flash or detect)
        $needsName = session('needs_name', false);
        if (!$needsName) {
            // Auto-detect: name is empty, equals phone, or starts with +998
            $needsName = empty($user->name) 
                || $user->name === $user->phone 
                || str_starts_with($user->name, '+998');
        }

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
            'needsName' => $needsName,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
            ],
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
                'name' => $master->full_name,
                'photo_url' => $master->photo_url,
                'experience' => $master->experience_years,
                'rating' => $master->rating ?? 5.0,
                'service_type_ids' => $master->serviceTypes->pluck('id')->toArray(),
            ]),
            'payment' => [
                'enabled' => config('services.payment.enabled', false),
                'payme_enabled' => config('services.payme.enabled', false),
                'click_enabled' => config('services.click.enabled', false),
            ],
        ]);
    }

    /**
     * Booking success page
     */
    public function bookingSuccess(Request $request)
    {
        $orderNumber = $request->query('order_number');
        $order = null;
        
        if ($orderNumber) {
            $order = \App\Models\Order::with(['master', 'serviceType', 'duration'])
                ->where('order_number', $orderNumber)
                ->where('customer_id', Auth::id())
                ->first();
        }
        
        return Inertia::render('MiniApp/BookingSuccess', [
            'order' => $order ? [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'booking_date' => $order->booking_date,
                'arrival_window_start' => $order->arrival_window_start,
                'arrival_window_end' => $order->arrival_window_end,
                'total_price' => $order->total_amount,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $order->payment_status_label,
                'payment_status_color' => $order->payment_status_color,
                'can_be_paid' => $order->canBePaid(),
                'duration' => $order->duration?->duration ?? 60,
                'master' => $order->master ? [
                    'name' => $order->master->name,
                ] : null,
                'service_type' => $order->serviceType ? [
                    'name' => $order->serviceType->name,
                ] : null,
            ] : null,
            'payment' => [
                'enabled' => $this->paymentService->isEnabled(),
                'providers' => $this->paymentService->getAvailableProviders(),
            ],
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

    /**
     * Customer orders list
     */
    public function orders(Request $request)
    {
        $orders = \App\Models\Order::with(['master', 'serviceType', 'duration'])
            ->where('customer_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('MiniApp/Orders/Index', [
            'orders' => [
                'data' => OrderMapper::collection($orders->getCollection(), 'toListItem'),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Customer order detail
     */
    public function orderShow(\App\Models\Order $order)
    {
        // Check ownership
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['master', 'serviceType', 'duration', 'oil']);

        return Inertia::render('MiniApp/Orders/Show', [
            'order' => OrderMapper::toDetail($order),
            'payment' => [
                'enabled' => $this->paymentService->isEnabled(),
                'providers' => $this->paymentService->getAvailableProviders(),
            ],
        ]);
    }

    /**
     * Cancel order request
     */
    public function orderCancel(\App\Models\Order $order)
    {
        // Check ownership
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        // Check if can be cancelled
        $cancellableStatuses = ['NEW', 'CONFIRMING', 'CONFIRMED', 'WAITING_PAYMENT'];
        if (!in_array($order->status, $cancellableStatuses)) {
            return back()->with('error', 'Bu buyurtmani bekor qilib bo\'lmaydi');
        }

        $order->update([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
            'cancelled_by' => 'customer',
        ]);

        Log::info('MiniApp: Order cancelled by customer', [
            'order_id' => $order->id,
            'customer_id' => Auth::id(),
        ]);

        return redirect()->route('miniapp.orders')->with('success', 'Buyurtma bekor qilindi');
    }

    /**
     * User profile page
     */
    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('MiniApp/Profile', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'locale' => $user->locale ?? 'uz',
                'telegram_id' => $user->telegram_id,
                'telegram_username' => $user->telegram_username,
                'telegram_photo_url' => $user->telegram_photo_url,
                'has_pin' => $user->hasPinCode(),
                'pin_set_at' => $user->pin_set_at?->format('d.m.Y'),
            ],
        ]);
    }

    /**
     * Update user profile
     */
    public function profileUpdate(\App\Http\Requests\MiniApp\UpdateProfileRequest $request)
    {
        $user = Auth::user();
        $user->update($request->validated());

        Log::info('MiniApp: Profile updated', [
            'user_id' => $user->id,
            'changes' => $request->validated(),
        ]);

        return back()->with('success', 'Profil yangilandi');
    }

    /**
     * Save user name (quick update from name modal)
     */
    public function saveName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|min:2',
        ]);

        $user = Auth::user();
        $user->update(['name' => $request->input('name')]);

        Log::info('MiniApp: Name saved', [
            'user_id' => $user->id,
            'name' => $request->input('name'),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Set or update PIN code
     */
    public function setPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|string|size:4|regex:/^[0-9]+$/',
            'current_pin' => 'nullable|string|size:4', // Required if already has PIN
        ]);

        $user = Auth::user();
        $newPin = $request->input('pin');
        $currentPin = $request->input('current_pin');

        // If user already has PIN, verify current PIN first
        if ($user->hasPinCode()) {
            if (!$currentPin) {
                return response()->json([
                    'success' => false,
                    'error' => 'current_pin_required',
                    'message' => 'Joriy PIN kodni kiriting',
                ], 400);
            }
            
            if (!$user->verifyPinCode($currentPin)) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_current_pin',
                    'message' => 'Joriy PIN kod noto\'g\'ri',
                ], 401);
            }
        }

        $user->setPinCode($newPin);

        Log::info('MiniApp: PIN code set', [
            'user_id' => $user->id,
            'is_update' => $user->hasPinCode(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN kod o\'rnatildi',
        ]);
    }

    /**
     * Remove PIN code
     */
    public function removePin(Request $request)
    {
        $request->validate([
            'current_pin' => 'required|string|size:4',
        ]);

        $user = Auth::user();

        if (!$user->hasPinCode()) {
            return response()->json([
                'success' => false,
                'error' => 'no_pin',
                'message' => 'PIN kod o\'rnatilmagan',
            ], 400);
        }

        if (!$user->verifyPinCode($request->input('current_pin'))) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_pin',
                'message' => 'PIN kod noto\'g\'ri',
            ], 401);
        }

        $user->update([
            'pin_code' => null,
            'pin_set_at' => null,
        ]);

        Log::info('MiniApp: PIN code removed', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'PIN kod o\'chirildi',
        ]);
    }
}
