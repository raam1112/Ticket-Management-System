<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    // ── Login ──────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Rate limiting: 5 attempts per minute per email+IP
        $key = 'login.' . $request->email . '.' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->withInput($request->only('email'));
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'These credentials do not match our records.'])
                         ->withInput($request->only('email'));
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        // Block inactive users
        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_activity_at' => now(),
        ]);

        if ($user->hasRole('agent')) {
            $user->update(['availability_status' => 'available']);
            \App\Models\AgentStatusHistory::create([
                'user_id' => $user->id,
                'status' => 'available',
                'reason' => 'Logged in',
            ]);
        }

        // Audit log
        AuditLog::record('login', $user->id);

        $request->session()->regenerate();

        // Role-based redirect
        return redirect()->intended(route('dashboard'));
    }

    // ── Register ───────────────────────────────────────────────────────────────

    public function showRegister()
    {
        $departments = Department::active()->orderBy('name')->get();
        return view('auth.register', compact('departments'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:users,email|max:191',
            'phone'         => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            'password'      => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone'             => $request->phone,
            'department_id'     => $request->department_id,
            'password'          => $request->password,
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        // Assign default 'user' role
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        }

        AuditLog::record('registered', $user->id, User::class, $user->id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account has been created.');
    }

    // ── Logout ─────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->hasRole('agent')) {
            $user->update(['availability_status' => 'offline']);
            \App\Models\AgentStatusHistory::create([
                'user_id' => $user->id,
                'status' => 'offline',
                'reason' => 'Logged out',
            ]);
        }

        AuditLog::record('logout', auth()->id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    // ── Forgot Password ────────────────────────────────────────────────────────

    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    public function sendReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::ResetLinkSent
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // ── Reset Password ─────────────────────────────────────────────────────────

    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])
                     ->setRememberToken(Str::random(60));
                $user->save();
                AuditLog::record('password_reset', $user->id);
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('success', 'Password reset successfully. Please log in.')
            : back()->withErrors(['email' => __($status)]);
    }
}
