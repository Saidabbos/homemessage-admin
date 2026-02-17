<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Auth\SendOtpRequest;
use App\Http\Requests\Public\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\CustomerAuthService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected CustomerAuthService $customerAuthService,
    ) {}

    /**
     * Show customer login page
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('master')) {
                return redirect()->route('master.dashboard');
            }
            if ($user->hasRole('customer')) {
                return redirect()->route('customer.dashboard');
            }
        }

        return Inertia::render('Public/Auth/Login');
    }

    /**
     * Send OTP to customer's phone number
     */
    public function sendOtp(SendOtpRequest $request)
    {
        $phone = $request->validated('phone');
        $locale = $request->validated('locale', 'uz');

        $result = $this->otpService->sendOtp(
            $phone,
            $request->ip(),
            $request->userAgent() ?? '',
            $locale
        );

        // Return Inertia-compatible response
        if ($request->header('X-Inertia')) {
            if (!$result['success']) {
                return back()->withErrors(['phone' => $result['message']]);
            }
            return back()->with('success', $result['message']);
        }

        $statusCode = $result['success'] ? 200 : ($result['error'] === 'rate_limit_exceeded' ? 429 : 422);
        return response()->json($result, $statusCode);
    }

    /**
     * Verify OTP code and login customer
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $phone = $request->validated('phone');
        $code = $request->validated('code');

        $result = $this->otpService->verifyOtp($phone, $code);

        if (!$result['success']) {
            // Return Inertia-compatible response
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['code' => $result['message']]);
            }
            return response()->json($result, 422);
        }

        // Prepare Telegram data if provided
        $telegramData = null;
        $telegramId = $request->input('telegram_id');
        if ($telegramId) {
            $telegramData = [
                'id' => $telegramId,
                'username' => $request->input('telegram_username'),
                'first_name' => $request->input('telegram_first_name'),
                'photo_url' => $request->input('telegram_photo_url'),
            ];
        }

        // Login or register customer (service handles Telegram linking)
        $authResult = $this->customerAuthService->loginOrRegister(
            $phone, 
            $request->validated('locale', 'uz'),
            $telegramData
        );
        
        $user = $authResult['user'];
        $isNew = $authResult['is_new'];

        // Create session
        Auth::login($user, true); // remember = true

        // Check if new user needs to enter name (no Telegram name available)
        $needsName = $isNew && $this->userNeedsNameInput($user);

        // Determine redirect based on role
        $defaultRoute = $user->hasRole('master') ? route('master.dashboard') : route('customer.dashboard');

        // Return Inertia-compatible response
        if ($request->header('X-Inertia')) {
            // Redirect based on where request came from
            $isMiniApp = str_contains($request->header('referer', ''), '/app');
            
            if ($isMiniApp) {
                // For Mini App: redirect to home, frontend will handle name input
                return redirect()->route('miniapp.home')->with('needs_name', $needsName);
            }
            
            return redirect($defaultRoute);
        }

        return response()->json([
            'success' => true,
            'redirect' => $defaultRoute,
            'is_new' => $isNew,
            'needs_name' => $needsName,
            'message' => __('auth.otp.login_success'),
        ]);
    }

    /**
     * Check if user needs to input their name
     * Returns true if name is not set or equals phone number
     */
    protected function userNeedsNameInput(User $user): bool
    {
        // Name is required if it's empty, equals phone, or starts with +998
        $name = $user->name;
        return empty($name) 
            || $name === $user->phone 
            || str_starts_with($name, '+998');
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')
            ->with('success', __('auth.otp.logout_success'));
    }

    /**
     * Check if phone number has PIN code set
     * Returns: { has_pin: bool, has_user: bool }
     */
    public function checkPin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->input('phone');
        $user = User::where('phone', $phone)->first();

        return response()->json([
            'has_user' => $user !== null,
            'has_pin' => $user?->hasPinCode() ?? false,
        ]);
    }

    /**
     * Login with PIN code (no OTP required)
     */
    public function loginWithPin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|string|size:4',
        ]);

        $phone = $request->input('phone');
        $pin = $request->input('pin');

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'user_not_found',
                'message' => 'Foydalanuvchi topilmadi',
            ], 404);
        }

        if (!$user->hasPinCode()) {
            return response()->json([
                'success' => false,
                'error' => 'no_pin',
                'message' => 'PIN kod o\'rnatilmagan',
            ], 400);
        }

        if (!$user->verifyPinCode($pin)) {
            \Illuminate\Support\Facades\Log::warning('CustomerAuth: Invalid PIN attempt', [
                'phone' => $phone,
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'invalid_pin',
                'message' => 'Noto\'g\'ri PIN kod',
            ], 401);
        }

        // Login successful
        Auth::login($user, true);

        \Illuminate\Support\Facades\Log::info('CustomerAuth: PIN login successful', [
            'user_id' => $user->id,
            'phone' => $phone,
        ]);

        // Determine redirect
        $isMiniApp = str_contains($request->header('referer', ''), '/app');
        $defaultRoute = $user->hasRole('master') ? route('master.dashboard') : route('customer.dashboard');

        return response()->json([
            'success' => true,
            'redirect' => $isMiniApp ? route('miniapp.home') : $defaultRoute,
            'message' => 'Muvaffaqiyatli kirdingiz',
        ]);
    }
}
