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
        // Redirect to dashboard if already authenticated as customer
        if (Auth::check() && Auth::user()->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
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
            return response()->json($result, 422);
        }

        // Login or register customer
        $user = $this->customerAuthService->loginOrRegister($phone, $request->validated('locale', 'uz'));

        // Create session
        Auth::login($user, true); // remember = true

        return response()->json([
            'success' => true,
            'redirect' => route('customer.dashboard'),
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
