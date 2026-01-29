<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $referralService;
    protected $notificationService;

    public function __construct(ReferralService $referralService, NotificationService $notificationService)
    {
        $this->referralService = $referralService;
        $this->notificationService = $notificationService;
    }
    /**
     * Handle user login
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->except('password'));
        }

        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact support.',
            ])->withInput($request->except('password'));
        }

        // Log the user in
        Auth::login($user, $request->filled('remember'));

        // Update last login
        $user->update(['last_login' => now()]);

        // Redirect based on user role
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        } elseif ($user->isHost()) {
            return redirect()->intended(route('host.dashboard'));
        } else {
            return redirect()->intended(route('dashboard'));
        }
    }

    /**
     * Handle user registration
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'language' => $validated['language'] ?? 'en',
            'status' => 'active',
            'email_verified_at' => now(), // Auto-verify for now
        ]);

        // Process referral code if provided
        $referralCode = $request->get('referral_code') ?? $request->get('ref');
        if ($referralCode) {
            try {
                $this->referralService->processReferralRegistration($user, $referralCode);
            } catch (\Exception $e) {
                // Log error but don't fail registration
                \Log::warning('Referral processing failed during registration', [
                    'user_id' => $user->id,
                    'referral_code' => $referralCode,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Log the user in
        Auth::login($user);

        // Send welcome notification
        $this->notificationService->sendWelcomeNotification($user);

        // Redirect to home with success message
        $welcomeMessage = 'Welcome to HabibiStay! Your account has been created successfully.';
        if ($referralCode) {
            $signupCredit = config('referrals.signup_credit', 25);
            $welcomeMessage .= " You've received ${$signupCredit} in referral credits!";
        }

        return redirect()->route('dashboard')->with('success', $welcomeMessage);
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show the registration form
     */
    public function showRegisterForm(Request $request)
    {
        $referralCode = $request->get('ref');
        $referrer = null;
        
        if ($referralCode) {
            $referrer = $this->referralService->validateReferralCode($referralCode);
        }
        
        return view('auth.register', compact('referralCode', 'referrer'));
    }
}
