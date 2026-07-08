<!-- ============ SIDEBAR (CUSTOMER) ============ -->
<aside id="sidebar">

    <!-- Logo -->
    <div class="px-6 pt-7 pb-3 flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl overflow-hidden shadow-lg shrink-0">
        <img src="{{ asset('images/logo1.png') }}" alt="Logo Hotel Pulang Yu" class="w-full h-full object-cover">
    </div>

        <div class="brand-text">
            <p class="font-bold text-navy leading-tight text-[15px]">
                Hotel Pulang Yu
            </p>
            <p class="text-xs text-slate-400">
                Customer
            </p>
        </div>
    </div>

    <div class="sidebar-scroll">

        <nav class="flex flex-col gap-1.5" id="navList">

                <!-- Dashboard -->
        <a href="{{ route('customer.dashboard') }}"
            class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span class="label">Dashboard</span>
        </a>

        <!-- Rooms -->
        <a href="{{ route('customer.rooms.index') }}"
            class="nav-link {{ request()->routeIs('customer.rooms.*') ? 'active' : '' }}">
            <i class="bi bi-door-closed-fill"></i>
            <span class="label">Rooms</span>
        </a>

        <!-- My Reservation -->
        <a href="{{ route('customer.reservations.index') }}"
            class="nav-link {{ request()->routeIs('customer.reservations.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check-fill"></i>
            <span class="label">My Reservation</span>
        </a>

        <!-- Profile -->
        <a href="{{ route('customer.profile') }}"
            class="nav-link {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
            <i class="bi bi-person-fill"></i>
            <span class="label">Profile</span>
        </a>
        </nav>

        <!-- Logout -->
        <div class="mt-6 pt-5 border-t border-slate-100">

            <a href="#"
                class="nav-link"
                onclick="event.preventDefault();document.getElementById('logout-form').submit();">

                <i class="bi bi-box-arrow-right"></i>

                <span class="label">
                    Logout
                </span>

            </a>

        </div>

    </div>

</aside>
