<!-- ============ SIDEBAR ============ -->
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
                Administrator
            </p>
        </div>
    </div>

    <div class="sidebar-scroll">

        <nav class="flex flex-col gap-1.5" id="navList">

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span class="label">Dashboard</span>
            </a>

            <!-- Room -->
            <div>

                <div class="nav-link {{ request()->routeIs('admin.room.*') || request()->routeIs('admin.room-types.*') ? 'active' : '' }}"
                    data-toggle="room">

                    <i class="bi bi-door-closed-fill"></i>
                    <span class="label">Room</span>
                    <i class="bi bi-chevron-right chev"></i>

                </div>

                <div class="submenu {{ request()->routeIs('admin.room.*') || request()->routeIs('admin.room-types.*') ? 'open' : '' }}"
                    id="sub-room">

                    <a href="{{ route('admin.room.index') }}"
                        class="{{ request()->routeIs('admin.room.*') ? 'active' : '' }}">
                        All Rooms
                    </a>

                    <a href="{{ route('admin.room-types.index') }}"
                        class="{{ request()->routeIs('admin.room-types.*') ? 'active' : '' }}">
                        Room Types
                    </a>

                </div>

            </div>

          <!-- Reservation -->
        <div>

            <div class="nav-link {{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}" data-toggle="reservation">
                <i class="bi bi-calendar2-check-fill"></i>
                <span class="label">Reservation</span>
                <i class="bi bi-chevron-right chev"></i>
            </div>

            <div class="submenu {{ request()->routeIs('admin.reservations.*') ? 'open' : '' }}" id="sub-reservation">

                <a href="{{ route('admin.reservations.index') }}"
                    class="{{ request()->routeIs('admin.reservations.index') ? 'active' : '' }}">
                    All Reservations
                </a>

                <a href="{{ route('admin.reservations.create') }}"
                    class="{{ request()->routeIs('admin.reservations.create') ? 'active' : '' }}">
                    New Reservation
                </a>

            </div>

        </div>

           <!-- Facilities -->
<div>

    <div class="nav-link {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}" data-toggle="facilities">
        <i class="bi bi-building-fill"></i>
        <span class="label">Facilities</span>
        <i class="bi bi-chevron-right chev"></i>
    </div>

    <div class="submenu {{ request()->routeIs('admin.facilities.*') ? 'open' : '' }}" id="sub-facilities">
        <a href="{{ route('admin.facilities.index') }}"
            class="{{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}">
            Facility List
        </a>
    </div>

</div>

<!-- Promotion -->
<a href="{{ route('admin.promotions.index') }}"
   class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}">
    <i class="bi bi-tags-fill"></i>
    <span class="label">Promotion</span>
</a>

<!-- Report -->
<a href="{{ route('admin.reports') }}"
    class="nav-link {{ request()->routeIs('admin.reports') || request()->routeIs('admin.reports.*') ? 'active' : '' }}">
    <i class="bi bi-bar-chart-line-fill"></i>
    <span class="label">Report</span>
</a>
        </nav>

        <!-- Logout -->
        <div class="mt-6 pt-5 border-t border-slate-100">

            <a href="#"
                class="nav-link"
                 onclick="event.preventDefault(); logoutAction();">

                <i class="bi bi-box-arrow-right"></i>

                <span class="label">
                    Logout
                </span>

            </a>

        </div>

    </div>

</aside>
