<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\Auth\SendOtpRequest;
use App\Http\Requests\Public\Auth\VerifyOtpRequest;
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

        // Login or register customer
        $user = $this->customerAuthService->loginOrRegister($phone, $request->validated('locale', 'uz'));

        // Link Telegram account if data provided
        $telegramId = $request->input('telegram_id');
        if ($telegramId && !$user->telegram_id) {
            $user->update([
                'telegram_id' => $telegramId,
                'telegram_username' => $request->input('telegram_username'),
                'telegram_first_name' => $request->input('telegram_first_name'),
                'telegram_photo_url' => $request->input('telegram_photo_url'),
            ]);
            \Illuminate\Support\Facades\Log::info('CustomerAuth: Telegram linked', [
                'user_id' => $user->id,
                'telegram_id' => $telegramId,
            ]);
        }

        // Create session
        Auth::login($user, true); // remember = true

        // Determine redirect based on role
        $defaultRoute = $user->hasRole('master') ? route('master.dashboard') : route('customer.dashboard');

        // Return Inertia-compatible response
        if ($request->header('X-Inertia')) {
            // Redirect based on where request came from
            $redirect = str_contains($request->header('referer', ''), '/app')
                ? route('miniapp.home')
                : $defaultRoute;
            return redirect($redirect);
        }

        return response()->json([
            'success' => true,
            'redirect' => $defaultRoute,
            'message' => __('auth.otp.login_success'),
        ]);
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
}
