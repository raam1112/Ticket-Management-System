@extends('layouts.app')

@section('title', 'Manage Users & Roles')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Users & Roles</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary bg-gradient shadow-sm rounded-pill px-4 py-2 font-weight-bold" style="letter-spacing: 0.5px;"><i class="fas fa-plus fa-sm text-white-50 me-2"></i> Add New User</a>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4" style="border-radius: 12px; border: none;">
    <div class="card-body py-3">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row gx-2 gy-2 align-items-center">
            <div class="col-sm-3">
                <input type="text" name="search" class="form-control form-control-sm" style="border-radius: 8px;" placeholder="Search by name or email" value="{{ request('search') }}">
            </div>
            <div class="col-sm-3">
                <select name="role" class="form-select form-select-sm" style="border-radius: 8px;">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <select name="dept" class="form-select form-select-sm" style="border-radius: 8px;">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <select name="status" class="form-select form-select-sm" style="border-radius: 8px;">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary bg-gradient rounded-pill px-3 shadow-sm font-weight-bold">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light rounded-pill px-3 font-weight-bold text-muted">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3 border-0 rounded-top-left">User</th>
                        <th class="py-3 border-0">Role</th>
                        <th class="py-3 border-0">Department</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="py-3 border-0">Last Login</th>
                        <th class="px-4 py-3 border-0 text-center rounded-top-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($users as $user)
                        <tr class="{{ $user->trashed() ? 'table-danger text-muted' : '' }}" style="transition: all 0.2s ease;">
                            <td class="px-4 py-3 border-bottom-0">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random' }}" class="rounded-circle me-3 shadow-sm" style="width: 40px; border: 2px solid #fff;">
                                    <div>
                                        <div class="font-weight-bold text-dark">{{ $user->name }}</div>
                                        <div class="small text-muted">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 border-bottom-0">
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                                @endforeach
                                @if($user->roles->isEmpty())
                                    <span class="badge bg-secondary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">No Role</span>
                                @endif
                            </td>
                            <td class="py-3 border-bottom-0 text-gray-700 font-weight-medium">{{ $user->department?->name ?? 'N/A' }}</td>
                            <td class="py-3 border-bottom-0 text-center">
                                @if($user->trashed())
                                    <span class="badge bg-danger bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Deleted</span>
                                @elseif($user->is_active)
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Active</span>
                                @else
                                    <span class="badge bg-warning text-dark bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 border-bottom-0 small text-gray-500 font-weight-medium">
                                <i class="far fa-clock me-1 text-primary"></i> {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-4 py-3 border-bottom-0 text-center">
                                @if(!$user->trashed())
                                    <button class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;" data-bs-toggle="modal" data-bs-target="#roleModal{{ $user->id }}" title="Change Role"><i class="fas fa-user-tag text-info"></i></button>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;"><i class="fas fa-edit text-primary"></i></a>
                                    
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm me-1" style="width: 32px; height: 32px;" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $user->is_active ? 'fa-ban text-warning' : 'fa-check text-success' }}"></i>
                                        </button>
                                    </form>

                                    @if(auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm" style="width: 32px; height: 32px;" onclick="return confirm('Are you sure you want to delete this user?')"><i class="fas fa-trash text-danger"></i></button>
                                    </form>
                                    @endif
                                @endif
                            </td>
                        </tr>

                        <!-- Role Modal -->
                        <div class="modal fade" id="roleModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 1rem 3rem rgba(0,0,0,.175);">
                                    <form action="{{ route('admin.users.assign-role', $user) }}" method="POST">
                                        @csrf
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title font-weight-bold text-primary">Change Role for {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label font-weight-bold">Select Primary Role</label>
                                                <select name="role_id" class="form-select" style="border-radius: 8px;" required>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text mt-2"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Warning: Changing a role updates permissions instantly.</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary bg-gradient rounded-pill px-4 shadow-sm">Update Role</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted border-0">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="fas fa-users text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5>No Users Found</h5>
                                    <p class="mb-0">Try adjusting your filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">{{ $users->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
