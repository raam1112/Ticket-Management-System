@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<div class="text-center mb-4">
    <h1 class="h3 text-gray-900 mb-2 font-weight-bold">Reset Your Password</h1>
    <p class="text-muted">Enter your new password below.</p>
</div>

<form class="user" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    
    <div class="mb-3">
        <label class="form-label font-weight-bold">Email Address</label>
        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
               name="email" value="{{ $email ?? old('email') }}" required readonly>
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    
    <div class="mb-3">
        <label class="form-label font-weight-bold">New Password</label>
        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" 
               name="password" required autofocus placeholder="Enter new password">
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="mb-4">
        <label class="form-label font-weight-bold">Confirm Password</label>
        <input type="password" class="form-control form-control-lg" 
               name="password_confirmation" required placeholder="Confirm new password">
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 shadow-sm" style="font-weight: 700; letter-spacing: 1px;">
        <i class="fas fa-key me-2"></i> UPDATE PASSWORD
    </button>
</form>
@endsection
