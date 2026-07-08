<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hotel Pulang Yo')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons (dipakai: bi-person-fill, bi-envelope-fill, bi-lock-fill, bi-eye-fill, dst) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Google Fonts (Inter & Poppins dipakai di login.css) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    {{-- CSS Login --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    @stack('styles')
</head>
<body class="@yield('body-class')">

    <div class="hpy-bg"></div>
    <div class="hpy-vignette"></div>

    <div class="hpy-wrapper">
        <div class="hpy-card">

            <div class="hpy-logo-badge">
                <i class="bi bi-building"></i>
            </div>
            <h1 class="hpy-brand-title">Hotel Pulang Yo</h1>
            <p class="hpy-brand-sub">@yield('subtitle', 'Selamat datang di Hotel Pulang Yo')</p>

            <div class="hpy-tabs">
                <div class="hpy-tab-slider @yield('slider-class')"></div>

                <button
                    type="button"
                    class="hpy-tab-btn @yield('tab-login-active')"
                    onclick="window.location.href='{{ route('login') }}'">
                    Login
                </button>

                <button
                    type="button"
                    class="hpy-tab-btn @yield('tab-register-active')"
                    onclick="window.location.href='{{ route('register') }}'">
                    Register
                </button>
            </div>

            @yield('content')

        </div>
    </div>

    {{-- Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    {{-- JS Login --}}
    <script src="{{ asset('js/login.js') }}"></script>

    @stack('scripts')

</body>
</html>
