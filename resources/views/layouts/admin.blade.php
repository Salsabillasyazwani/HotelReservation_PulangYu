<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Hotel Pulang Yo — @yield('title', 'Admin Dashboard')</title>

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Chart.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'Inter', 'sans-serif']
                    },
                    colors: {
                        navy: '#0F172A',
                        royal: '#1B2E66',
                        gold: '#D4AF37',
                        soft: '#F8FAFC',
                        bordergray: '#E5E7EB'
                    },
                    borderRadius: {
                        '4xl': '32px',
                        '5xl': '40px'
                    }
                }
            }
        }
    </script>

    {{-- Global CSS --}}
    <link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">

    {{-- CSS Halaman --}}
    @stack('styles')

</head>

<body class="text-slate-800">

    {{-- Loader --}}
    <div id="pageLoader">
        <div class="loader-ring"></div>
    </div>

    {{-- Overlay (dim background utk mobile sidebar toggle) --}}
    <div id="overlay" onclick="closeMobileSidebar()"></div>

    {{-- Sidebar --}}
    @include('admin.partials.sidebar')

    <div id="main-wrap">

        {{-- Navbar --}}
        @include('admin.partials.navbar')

        {{-- Content --}}
        <main>
            <div class="page-content">
                @yield('content')
            </div>
        </main>

        {{-- Modal detail bawaan layout (dipakai halaman lain, mis. quick view) --}}
        <div id="detailModal" class="modal-backdrop-custom">
            <div class="modal-box card w-[92%] max-w-md p-6 relative">
                <div id="modalBody" class="flex flex-col gap-3 text-sm"></div>
            </div>
        </div>

    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Global JS --}}
    <script src="{{ asset('js/admin/admin.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/admin/global-search.js') }}"></script>

    {{-- JS Halaman --}}
    @stack('scripts')
    @stack('modals')

    {{-- Logout Form --}}
    <form id="logout-form"
          action="{{ route('logout') }}"
          method="POST"
          style="display: none;">
        @csrf
    </form>

</body>

</html>
