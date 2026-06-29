<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name'     => 'required|string|max:150',
            'phone'    => 'nullable|string|max:20',
            'pronouns' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'preferred_language' => 'nullable|string|max:50',
            'time_zone'          => 'nullable|string|max:50',
            'avatar'   => 'nullable|image|max:2048',
        ]);

        $data = $request->only('name', 'phone', 'pronouns', 'location', 'preferred_language', 'time_zone');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);
        AuditLog::record('profile_updated', $user->id);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user->update(['password' => Hash::make($request->password)]);
        AuditLog::record('password_changed', $user->id);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateStatus(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('agent')) abort(403);

        $request->validate([
            'availability_status' => 'required|in:available,busy,offline,on_leave'
        ]);

        $status = $request->availability_status;
        
        $user->update(['availability_status' => $status]);
        
        \App\Models\AgentStatusHistory::create([
            'user_id' => $user->id,
            'status' => $status,
            'changed_by' => auth()->id(),
            'reason' => 'Manual update via profile'
        ]);

        AuditLog::record("agent_status_{$status}", $user->id);

        return back()->with('success', 'Status updated successfully.');
    }
}
