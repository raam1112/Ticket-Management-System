@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="text-center mb-5">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 60px; height: 60px;">
        <i class="fas fa-fingerprint fa-2x"></i>
    </div>
    <h1 class="h3 mb-2 font-weight-bold" style="letter-spacing: -0.5px;">Welcome Back!</h1>
    <p class="text-muted">Securely sign in to your ETMS dashboard</p>
</div>

<form class="user" method="POST" action="{{ route('login.post') }}">
    @csrf
    
    <div class="mb-4">
        <label class="form-label font-weight-bold small text-uppercase" style="letter-spacing: 0.5px; opacity: 0.8;">Email Address</label>
        <div class="input-group input-group-lg shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <span class="input-group-text bg-light border-0"><i class="fas fa-envelope text-muted"></i></span>
            <input type="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com" style="font-size: 15px;">
        </div>
        @error('email')
            <span class="text-danger small mt-1 d-block"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
    
    <div class="mb-4">
        <label class="form-label font-weight-bold small text-uppercase" style="letter-spacing: 0.5px; opacity: 0.8;">Password</label>
        <div class="input-group input-group-lg shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <span class="input-group-text bg-light border-0"><i class="fas fa-lock text-muted"></i></span>
            <input type="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" 
                   name="password" required placeholder="Enter password" style="font-size: 15px;">
        </div>
        @error('password')
            <span class="text-danger small mt-1 d-block"><strong>{{ $message }}</strong></span>
        @enderror
    </div>
    
    <div class="mb-4 d-flex justify-content-between align-items-center px-1">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="remember" id="customCheck" {{ old('remember') ? 'checked' : '' }} style="cursor: pointer;">
            <label class="form-check-label text-muted ms-1" for="customCheck" style="font-size: 14px; cursor: pointer;">
                Remember me
            </label>
        </div>
        <a class="small text-primary text-decoration-none font-weight-bold" href="{{ route('password.request') }}">Forgot Password?</a>
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100 mb-4 shadow text-uppercase" style="font-weight: 700; letter-spacing: 1px; border-radius: 12px; background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%); border: none; transition: all 0.3s ease;">
        <i class="fas fa-sign-in-alt me-2"></i> Authenticate
    </button>
</form>

<div class="text-center">
    <span class="text-muted small">Don't have an account?</span> 
    <a class="font-weight-bold text-decoration-none text-primary" href="{{ route('register') }}">Create an Account!</a>
</div>

<!-- Demo credentials alert -->
<div class="alert alert-primary bg-primary bg-opacity-10 border-0 mt-5 small shadow-sm rounded-3" role="alert">
    <h6 class="alert-heading font-weight-bold mb-2"><i class="fas fa-info-circle me-1"></i> Demo Access</h6>
    <div class="d-flex flex-column gap-1">
        <div><strong>Admin:</strong> admin@etms.com / Admin@1234</div>
        <div><strong>Agent:</strong> alice@etms.com / Agent@1234</div>
        <div><strong>User:</strong> user@etms.com / User@1234</div>
    </div>
</div>
@endsection
