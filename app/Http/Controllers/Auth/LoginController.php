<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request and send OTP
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Find user by mobile
        $user = User::where('mobile', $this->formatMobile($request->mobile))->first();

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'mobile' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate and send OTP
        $otp = $user->generateOTP();
        
        $smsSent = $this->smsService->sendOTP($user->mobile, $otp);

        if (!$smsSent) {
            throw ValidationException::withMessages([
                'mobile' => ['Failed to send OTP. Please try again.'],
            ]);
        }

        // Store user ID in session for OTP verification
        session(['otp_user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your mobile number',
            'mobile_hint' => substr($user->mobile, -4) // Last 4 digits for display
        ]);
    }

    /**
     * Verify OTP and complete login
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('otp_user_id');
        
        if (!$userId) {
            throw ValidationException::withMessages([
                'otp' => ['Session expired. Please login again.'],
            ]);
        }

        $user = User::find($userId);

        if (!$user) {
            throw ValidationException::withMessages([
                'otp' => ['User not found. Please login again.'],
            ]);
        }

        // Check if OTP is expired
        if ($user->isOTPExpired()) {
            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new one.'],
            ]);
        }

        // Verify OTP
        if (!$user->verifyOTP($request->otp)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code. Please try again.'],
            ]);
        }

        // Login user
        Auth::login($user);
        
        // Clear session
        session()->forget('otp_user_id');

        // Regenerate session for security
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => route('dashboard')
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOTP(Request $request)
    {
        $userId = session('otp_user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Generate new OTP
        $otp = $user->generateOTP();
        
        $smsSent = $this->smsService->sendOTP($user->mobile, $otp);

        if (!$smsSent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'New OTP sent successfully'
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Format mobile number
     */
    private function formatMobile($mobile)
    {
        $mobile = preg_replace('/[\s\-\+]/', '', $mobile);
        
        if (substr($mobile, 0, 1) === '0') {
            $mobile = substr($mobile, 1);
        }
        
        if (substr($mobile, 0, 3) !== '961') {
            $mobile = '961' . $mobile;
        }
        
        return $mobile;
    }
}