<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'department'])->withTrashed();

        if ($request->filled('role'))   $query->byRole($request->role);
        if ($request->filled('dept'))   $query->where('department_id', $request->dept);
        if ($request->filled('status')) $query->where('is_active', $request->status === 'active');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%$s%")->orWhere('email','like',"%$s%"));
        }

        $users       = $query->latest()->paginate(25)->withQueryString();
        $roles       = Role::all();
        $departments = Department::active()->get();
        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        $roles       = Role::all();
        $departments = Department::active()->get();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:users',
            'role_id'       => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'phone'         => 'nullable|string|max:20',
            'password'      => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => $request->password,
            'department_id'     => $request->department_id,
            'phone'             => $request->phone,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($request->role_id, ['assigned_by' => auth()->id()]);
        AuditLog::record('user_created', auth()->id(), User::class, $user->id);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $user->load('roles', 'department');
        $roles       = Role::all();
        $departments = Department::active()->get();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'department_id' => 'nullable|exists:departments,id',
            'phone'         => 'nullable|string|max:20',
        ]);

        $user->update($request->only('name', 'email', 'department_id', 'phone'));
        AuditLog::record('user_updated', auth()->id(), User::class, $user->id);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Cannot delete yourself.');
        $user->delete();
        AuditLog::record('user_deleted', auth()->id(), User::class, $user->id);
        return back()->with('success', 'User deactivated.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        AuditLog::record("user_{$status}", auth()->id(), User::class, $user->id);
        return back()->with('success', "User {$status}.");
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);
        $user->roles()->sync([$request->role_id => ['assigned_by' => auth()->id()]]);
        return back()->with('success', 'Role updated.');
    }
}
