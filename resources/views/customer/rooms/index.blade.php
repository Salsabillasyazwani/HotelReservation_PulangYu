@extends('layouts.customer')

@section('title', 'Rooms')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer/rooms.css') }}">
@endpush

@section('content')

    <!-- Hero Section -->
    <div class="hero-section rounded-2xl p-8 lg:p-12 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-gold opacity-5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-gold opacity-5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-star text-gold"></i>
                <span class="text-gold text-sm font-semibold tracking-wider uppercase">Luxury Experience</span>
            </div>
            <h1 class="text-3xl lg:text-4xl font-display font-bold text-white mb-3">Find Your Perfect Room</h1>
            <p class="text-gray-300 text-base lg:text-lg max-w-xl">Discover our carefully curated collection of premium rooms and suites, designed for your ultimate comfort.</p>
        </div>
    </div>

    <!-- Recommended Room Section -->
    <div class="mt-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-navy flex items-center gap-2">
                    <i class="fas fa-crown text-gold"></i>
                    Recommended For You
                </h3>
                <p class="text-sm text-gray-500 mt-1">Handpicked rooms based on your preferences</p>
            </div>
            <a href="#" class="text-gold text-sm font-semibold hover:text-gold-dark transition-colors">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5" id="recommendedRooms">
            <!-- Recommended cards injected by JS from room data -->
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mt-8">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Search rooms by name or number..." class="search-input w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none transition-all" oninput="filterRooms()">
            </div>
            <!-- Room Type Filter -->
            <div class="relative min-w-[200px]">
                <select id="typeFilter" class="w-full appearance-none px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 bg-white cursor-pointer pr-10" onchange="filterRooms()">
                    <option value="">All Room Types</option>
                    @foreach(($roomTypes ?? []) as $type)
                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>
            <!-- Status Filter -->
            <div class="relative min-w-[200px]">
                <select id="statusFilter" class="w-full appearance-none px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-gold focus:ring-2 focus:ring-gold/20 bg-white cursor-pointer pr-10" onchange="filterRooms()">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="Occupied">Occupied</option>
                    <option value="Maintenance">Maintenance</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>
            <!-- Reset -->
            <button onclick="resetFilters()" class="flex items-center justify-center gap-2 px-5 py-3 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:border-gold hover:text-gold transition-all whitespace-nowrap">
                <i class="fas fa-redo-alt"></i>
                <span>Reset</span>
            </button>
        </div>
        <!-- Active Filters -->
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100" id="activeFilters">
            <!-- Active filter chips injected by JS -->
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4 mt-8">
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500">Showing <span id="roomCount" class="font-bold text-navy">0</span> rooms</span>
            <div class="hidden sm:flex items-center gap-2 text-xs text-gray-400">
                <span class="w-2 h-2 rounded-full bg-green-500"></span> Available
                <span class="w-2 h-2 rounded-full bg-red-500 ml-2"></span> Occupied
                <span class="w-2 h-2 rounded-full bg-yellow-500 ml-2"></span> Maintenance
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="setView('grid')" id="gridViewBtn" class="p-2 rounded-lg bg-navy text-white transition-all">
                <i class="fas fa-th-large"></i>
            </button>
            <button onclick="setView('list')" id="listViewBtn" class="p-2 rounded-lg bg-white text-gray-400 hover:text-navy transition-all border border-gray-200">
                <i class="fas fa-list"></i>
            </button>
        </div>
    </div>

    <!-- Room Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-6" id="roomGrid">
        <!-- Room cards injected by JS -->
    </div>

    <!-- Empty State -->
    <div class="hidden text-center py-16" id="emptyState">
        <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <i class="fas fa-search text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-navy mb-2">No rooms found</h3>
        <p class="text-gray-500 text-sm">Try adjusting your search or filter criteria</p>
    </div>

    <!-- Load More -->
    <div class="text-center pt-4 mt-4" id="loadMoreSection">
        <button onclick="loadMore()" class="px-8 py-3 navy-gradient text-white rounded-xl font-semibold text-sm hover:opacity-90 transition-all shadow-lg shadow-navy/20">
            Load More Rooms <i class="fas fa-arrow-down ml-2"></i>
        </button>
    </div>

    <!-- Room Detail Modal -->
    <div class="modal-backdrop fixed inset-0 z-50 flex items-center justify-center p-4" id="roomModal">
        <div class="absolute inset-0 bg-black bg-opacity-60" onclick="closeModal()"></div>
        <div class="modal-content relative bg-white rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
            <button onclick="closeModal()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-white bg-opacity-90 rounded-full flex items-center justify-center hover:bg-opacity-100 transition-all shadow-lg">
                <i class="fas fa-times text-navy"></i>
            </button>
            <div id="modalContent">
                <!-- Modal content injected by JS -->
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    window.roomTypesData = @json($roomTypes ?? []);
    window.roomsData = @json($rooms ?? []);
    window.reservationsUrl = "{{ route('customer.reservations.index') }}";
</script>
<script src="{{ asset('js/customer/rooms.js') }}"></script>
@endpush
