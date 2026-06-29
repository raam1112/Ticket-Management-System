@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="text-center mb-4">
    <h1 class="h3 text-gray-900 mb-2 font-weight-bold">Forgot Your Password?</h1>
    <p class="text-muted">We get it, stuff happens. Just enter your email address below and we'll send you a link to reset your password!</p>
</div>

@if (session('success'))
    <div class="alert alert-success border-0 shadow-sm" role="alert">
        {{ session('success') }}
    </div>
@endif

<form class="user" method="POST" action="{{ route('password.email') }}">
    @csrf
    
    <div class="mb-4">
        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" 
               name="email" value="{{ old('email') }}" required autofocus placeholder="Enter Email Address...">
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 shadow-sm" style="font-weight: 700; letter-spacing: 1px;">
        <i class="fas fa-envelope me-2"></i> RESET PASSWORD
    </button>
</form>

<hr class="my-4">

<div class="text-center">
    <a class="font-weight-bold text-decoration-none" href="{{ route('register') }}">Create an Account!</a>
</div>
<div class="text-center mt-2">
    <a class="text-decoration-none" href="{{ route('login') }}">Already have an account? Login!</a>
</div>
@endsection
