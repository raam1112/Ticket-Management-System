@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="text-center mb-4">
    <h1 class="h3 text-gray-900 mb-2 font-weight-bold">Create an Account!</h1>
    <p class="text-muted">Fill out the form below to register to the ETMS portal.</p>
</div>

<form class="user" method="POST" action="{{ route('register.post') }}">
    @csrf
    
    <div class="mb-3">
        <label class="form-label font-weight-bold">Full Name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" 
               name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe">
        @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label font-weight-bold">Email Address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   name="email" value="{{ old('email') }}" required placeholder="name@example.com">
            @error('email')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label font-weight-bold">Phone Number</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                   name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 890">
            @error('phone')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label font-weight-bold">Department</label>
        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
            <option value="">-- Select Department (Optional) --</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>
        @error('department_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label font-weight-bold">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                   name="password" required placeholder="Create password">
            @error('password')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
        </div>
        <div class="col-md-6 mb-4">
            <label class="form-label font-weight-bold">Repeat Password</label>
            <input type="password" class="form-control" name="password_confirmation" required placeholder="Repeat password">
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 shadow-sm" style="font-weight: 700; letter-spacing: 1px;">
        <i class="fas fa-user-plus me-2"></i> REGISTER ACCOUNT
    </button>
</form>

<hr class="my-4">

<div class="text-center">
    <span class="text-muted small">Already have an account?</span> 
    <a class="font-weight-bold text-decoration-none" href="{{ route('login') }}">Login!</a>
</div>
@endsection
