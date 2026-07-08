@extends('layouts.guest')

@section('title', 'Login')

@section('tab-login-active', 'active')
@section('tab-register-active', '')
@section('slider-class', '')
@section('body-class', 'hpy-page-login')

@section('content')

@if(session('success'))
    <div class="alert alert-success mb-3">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="hpy-input-group">
        <label class="hpy-label">Email</label>
        <div class="hpy-input-wrap">
            <i class="bi bi-envelope-fill field-icon"></i>
            <input
                type="email"
                name="email"
                class="hpy-input"
                placeholder="nama@email.com"
                value="{{ old('email') }}"
                required>
        </div>
    </div>

    <div class="hpy-input-group">
        <label class="hpy-label">Password</label>
        <div class="hpy-input-wrap">
            <i class="bi bi-lock-fill field-icon"></i>

            <input
                type="password"
                name="password"
                class="hpy-input"
                id="loginPassword"
                placeholder="••••••••"
                required>

            <button
                type="button"
                class="hpy-toggle-pass"
                onclick="hpyTogglePassword('loginPassword', this)">
                <i class="bi bi-eye-fill"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="hpy-btn-primary">
        <i class="bi bi-box-arrow-in-right me-2"></i>
        Login
    </button>

    <div class="hpy-divider">
        <span>OR</span>
    </div><a href="{{ route('google.login') }}" class="hpy-btn-google">
        <svg width="18" height="18" viewBox="0 0 48 48">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12s5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
        </svg>

        Continue with Google
    </a>

    <p class="hpy-switch-text">
        Belum punya akun?
        <a href="{{ route('register') }}" class="hpy-link">
            Register
        </a>
    </p>

</form>

@endsection
