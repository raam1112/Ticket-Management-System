@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
</div>

<div class="row">
    <!-- Profile Dashboard Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-lg border-0 h-100" style="border-radius: 16px; overflow: hidden; backdrop-filter: blur(10px);">
            <div class="card-body text-center pt-5 pb-4">
                <div class="position-relative d-inline-block mb-4">
                    <img src="{{ $user->avatar_url }}" class="rounded-circle shadow-sm" style="width: 130px; height: 130px; object-fit: cover; border: 4px solid var(--bs-card-bg);">
                    @if($user->hasRole('agent'))
                        @php
                            $statusColor = match($user->availability_status) {
                                'available' => 'success',
                                'busy' => 'warning',
                                'on_leave' => 'primary',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="position-absolute bottom-0 end-0 p-2 bg-{{ $statusColor }} border rounded-circle shadow" style="border-width: 3px !important; border-color: var(--bs-card-bg) !important; width: 25px; height: 25px;" title="{{ ucfirst(str_replace('_', ' ', $user->availability_status)) }}"></span>
                    @endif
                </div>
                <h4 class="font-weight-bold mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2 font-weight-medium">{{ ucfirst($user->primary_role ?? 'User') }}</p>
                
                @if($user->pronouns || $user->location)
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        @if($user->pronouns)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill"><i class="fas fa-user-tag me-1"></i>{{ $user->pronouns }}</span>
                        @endif
                        @if($user->location)
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i class="fas fa-map-marker-alt me-1"></i>{{ $user->location }}</span>
                        @endif
                    </div>
                @endif

                <div class="text-start mt-4 pt-3 border-top">
                    <p class="mb-2 text-sm"><i class="fas fa-building text-muted me-2 w-15px"></i> <strong>Dept:</strong> {{ $user->department?->name ?? 'N/A' }}</p>
                    <p class="mb-2 text-sm"><i class="fas fa-clock text-muted me-2 w-15px"></i> <strong>Last Login:</strong> {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                    
                    @if($user->hasRole('agent'))
                        <div class="mt-4 border rounded-3 p-3">
                            <h6 class="font-weight-bold mb-3 text-center" style="font-size: 0.85rem; letter-spacing: 0.5px;">WORKLOAD ANALYTICS</h6>
                            <div class="row text-center">
                                <div class="col-4 border-end">
                                    <div class="h5 mb-0 font-weight-bold text-primary">{{ $user->assignedTickets()->whereNotIn('status', ['resolved', 'closed', 'cancelled'])->count() }}</div>
                                    <div class="text-xs text-muted mt-1">Active</div>
                                </div>
                                <div class="col-4 border-end">
                                    <div class="h5 mb-0 font-weight-bold text-success">98%</div>
                                    <div class="text-xs text-muted mt-1">SLA</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 font-weight-bold text-info">2h</div>
                                    <div class="text-xs text-muted mt-1">Avg Res</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Forms -->
    <div class="col-lg-8 mb-4">
        @if($user->hasRole('agent'))
        <div class="card shadow-lg border-0 mb-4" style="border-radius: 16px; backdrop-filter: blur(10px);">
            <div class="card-header py-3 bg-transparent border-0 d-flex align-items-center">
                <i class="fas fa-broadcast-tower text-primary me-2 fa-lg"></i>
                <h6 class="m-0 font-weight-bold" style="letter-spacing: 0.5px;">Enterprise Availability Settings</h6>
            </div>
            <div class="card-body pt-0">
                <form action="{{ route('profile.status') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row align-items-center p-3 rounded-3 mx-0 border">
                        <div class="col-md-7 mb-3 mb-md-0">
                            <h6 class="font-weight-bold mb-1">Current Status</h6>
                            <p class="text-muted small mb-0">Your status determines if the Auto-Assignment engine will route tickets to you.</p>
                        </div>
                        <div class="col-md-5 d-flex gap-2">
                            <select name="availability_status" class="form-select font-weight-bold shadow-sm border-0" onchange="this.form.submit()">
                                <option value="available" {{ $user->availability_status === 'available' ? 'selected' : '' }}>🟢 Available (Auto-Assign Active)</option>
                                <option value="busy" {{ $user->availability_status === 'busy' ? 'selected' : '' }}>🟡 Busy (Manual Only)</option>
                                <option value="offline" {{ $user->availability_status === 'offline' ? 'selected' : '' }}>🔴 Offline (Hidden)</option>
                                <option value="on_leave" {{ $user->availability_status === 'on_leave' ? 'selected' : '' }}>🟣 On Leave</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="card shadow-lg border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header py-3 bg-transparent border-0 d-flex align-items-center">
                <i class="fas fa-user-edit text-primary me-2 fa-lg"></i>
                <h6 class="m-0 font-weight-bold" style="letter-spacing: 0.5px;">Personal Information</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label font-weight-bold">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Profile Photo</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                            @error('avatar')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label font-weight-bold">Pronouns</label>
                            <input type="text" name="pronouns" class="form-control @error('pronouns') is-invalid @enderror" value="{{ old('pronouns', $user->pronouns) }}" placeholder="e.g. He/Him">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Location</label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $user->location) }}" placeholder="e.g. New York">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label font-weight-bold">Preferred Language</label>
                            <select name="preferred_language" class="form-select">
                                <option value="English" {{ old('preferred_language', $user->preferred_language) == 'English' ? 'selected' : '' }}>English</option>
                                <option value="Spanish" {{ old('preferred_language', $user->preferred_language) == 'Spanish' ? 'selected' : '' }}>Spanish</option>
                                <option value="French" {{ old('preferred_language', $user->preferred_language) == 'French' ? 'selected' : '' }}>French</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">Time Zone</label>
                            <select name="time_zone" class="form-select">
                                <option value="UTC" {{ old('time_zone', $user->time_zone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="America/New_York" {{ old('time_zone', $user->time_zone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                <option value="America/Los_Angeles" {{ old('time_zone', $user->time_zone) == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                <option value="Asia/Kolkata" {{ old('time_zone', $user->time_zone) == 'Asia/Kolkata' ? 'selected' : '' }}>India Standard Time</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary bg-gradient shadow-sm rounded-pill px-4 font-weight-bold"><i class="fas fa-save me-2"></i> Save Changes</button>
                </form>
            </div>
        </div>

        <div class="card shadow-lg border-0 mb-4" style="border-radius: 16px;">
            <div class="card-header py-3 bg-transparent border-0 d-flex align-items-center">
                <i class="fas fa-lock text-warning me-2 fa-lg"></i>
                <h6 class="m-0 font-weight-bold" style="letter-spacing: 0.5px;">Security</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning font-weight-bold shadow-sm rounded-pill px-4"><i class="fas fa-key me-2"></i> Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
