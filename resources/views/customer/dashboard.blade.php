@extends('layouts.customer')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer/dashboard.css') }}">
@endpush

@section('content')

    <!-- Page Header -->
    <div class="mb-8 animate-fade-in-up">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 class="text-2xl lg:text-3xl font-extrabold text-navy tracking-tight">Dashboard</h2>
          <p class="text-gray-500 text-sm mt-1">Welcome back, <span class="font-bold text-navy">{{ auth()->user()->name }}</span> 👋</p>
        </div>
        <!-- Date Range -->
        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2.5 shadow-sm">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span class="text-xs font-semibold text-navy">{{ now()->startOfMonth()->format('M d, Y') }} – {{ now()->endOfMonth()->format('M d, Y') }}</span>
          <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
      <!-- Card 1: Total Reservation -->
      <div class="card-hover bg-white rounded-[24px] p-6 shadow-[0_2px_16px_rgba(11,23,57,0.04)] animate-fade-in-up delay-1">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center">
            <i class="fas fa-calendar-alt text-purple-500 text-lg"></i>
          </div>
          <button class="w-8 h-8 rounded-lg hover:bg-gray-50 flex items-center justify-center transition-colors">
            <i class="fas fa-ellipsis-vertical text-gray-400 text-sm"></i>
          </button>
        </div>
        <p class="text-gray-500 text-sm font-medium mb-1">Total Reservation</p>
        <div class="flex items-end gap-3">
          <h3 class="text-3xl font-extrabold text-navy">{{ $totalReservation }}</h3>
          @if($totalReservationGrowth !== 0.0)
          <span class="flex items-center gap-1 px-2 py-0.5 rounded-full {{ $totalReservationGrowth >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }} text-xs font-bold mb-1">
            <i class="fas fa-arrow-{{ $totalReservationGrowth >= 0 ? 'up' : 'down' }} text-[10px]"></i>
            {{ abs($totalReservationGrowth) }}%
          </span>
          @endif
        </div>
        <p class="text-[11px] text-gray-400 mt-2">from last month</p>
      </div>

      <!-- Card 2: Upcoming Stay -->
      <div class="card-hover bg-white rounded-[24px] p-6 shadow-[0_2px_16px_rgba(11,23,57,0.04)] animate-fade-in-up delay-2">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center">
            <i class="fas fa-clock text-amber-500 text-lg"></i>
          </div>
          <button class="w-8 h-8 rounded-lg hover:bg-gray-50 flex items-center justify-center transition-colors">
            <i class="fas fa-ellipsis-vertical text-gray-400 text-sm"></i>
          </button>
        </div>
        <p class="text-gray-500 text-sm font-medium mb-1">Upcoming Stay</p>
        <div class="flex items-end gap-3">
          <h3 class="text-3xl font-extrabold text-navy">{{ $upcomingStay }}</h3>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">reservations ahead</p>
      </div>

      <!-- Card 3: Completed Stay -->
      <div class="card-hover bg-white rounded-[24px] p-6 shadow-[0_2px_16px_rgba(11,23,57,0.04)] animate-fade-in-up delay-3">
        <div class="flex items-start justify-between mb-4">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center">
            <i class="fas fa-circle-check text-emerald-500 text-lg"></i>
          </div>
          <button class="w-8 h-8 rounded-lg hover:bg-gray-50 flex items-center justify-center transition-colors">
            <i class="fas fa-ellipsis-vertical text-gray-400 text-sm"></i>
          </button>
        </div>
        <p class="text-gray-500 text-sm font-medium mb-1">Completed Stay</p>
        <div class="flex items-end gap-3">
          <h3 class="text-3xl font-extrabold text-navy">{{ $completedStay }}</h3>
        </div>
        <p class="text-[11px] text-gray-400 mt-2">total check-outs</p>
      </div>
    </div>

    <!-- Two Column Grid: Recent Reservation + Promo -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
      <!-- Recent Reservation -->
      <div class="bg-white rounded-[24px] p-6 shadow-[0_2px_16px_rgba(11,23,57,0.04)] animate-fade-in-up delay-4">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg font-bold text-navy">Recent Reservation</h3>
            <p class="text-xs text-gray-400 mt-0.5">Your latest booking activity</p>
          </div>
          <div class="relative">
            <select class="appearance-none bg-bg text-xs font-semibold text-navy px-4 py-2 pr-8 rounded-xl border border-gray-200 outline-none focus:border-accent cursor-pointer">
              <option>This Year</option>
              <option>This Month</option>
              <option>Last 3 Months</option>
            </select>
            <i class="fas fa-chevron-down text-[10px] text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">Room</th>
                <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">Check In</th>
                <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3 pr-4">Check Out</th>
                <th class="text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider pb-3">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($reservations as $res)
              <tr class="table-row border-b border-gray-50">
                <td class="py-3.5 pr-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center shrink-0">
                      <i class="fas fa-bed text-blue-500 text-xs"></i>
                    </div>
                    <div>
                      <p class="text-sm font-bold text-navy leading-tight">{{ $res['room_type'] }}</p>
                      <p class="text-[10px] text-gray-400">Room {{ $res['room_number'] }}</p>
                    </div>
                  </div>
                </td>
                <td class="py-3.5 pr-4 text-sm text-gray-600 font-medium">{{ $res['check_in'] }}</td>
                <td class="py-3.5 pr-4 text-sm text-gray-600 font-medium">{{ $res['check_out'] }}</td>
                <td class="py-3.5">
                  @php
                    $statusColor = [
                      'Confirmed'   => 'green',
                      'Checked Out' => 'blue',
                      'Cancelled'   => 'red',
                    ][$res['status']] ?? 'gray';
                  @endphp
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 text-[11px] font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusColor }}-500"></span>
                    {{ $res['status'] }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="py-6 text-center text-sm text-gray-400">Belum ada reservasi.</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Promotion -->
      <div class="bg-white rounded-[24px] p-6 shadow-[0_2px_16px_rgba(11,23,57,0.04)] animate-fade-in-up delay-5">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg font-bold text-navy">Promotions</h3>
            <p class="text-xs text-gray-400 mt-0.5">Exclusive deals for you</p>
          </div>
          <a href="#" class="text-xs text-accent font-semibold hover:underline">View all</a>
        </div>

        <div class="space-y-4">
          @php
            // Warna gradient dirotasi per index supaya tetap variatif walau datanya dinamis.
            $promoGradients = [
              'from-purple-600 via-purple-500 to-indigo-500',
              'from-orange-500 via-orange-400 to-amber-400',
            ];
          @endphp

          @forelse($promotions as $i => $promo)
          @php
            // Kalau admin upload banner, pakai itu sebagai background image.
            // Kalau tidak ada, fallback ke gradient warna seperti sebelumnya.
            $bannerUrl = $promo->banner ? asset('storage/' . $promo->banner) : null;
          @endphp
          <div
            class="promo-card relative rounded-2xl bg-gradient-to-r {{ $promoGradients[$i % count($promoGradients)] }} p-5 cursor-pointer group bg-cover bg-center"
            @if($bannerUrl) style="background-image: linear-gradient(to right, rgba(11,23,57,0.55), rgba(11,23,57,0.25)), url('{{ $bannerUrl }}');" @endif
          >
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-xl group-hover:bg-white/15 transition-all duration-500"></div>
            <div class="relative flex items-center justify-between">
              <div>
                <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-[10px] font-bold mb-3 backdrop-blur-sm">{{ $promo->promo_code }}</span>
                <h4 class="text-white text-lg font-bold">{{ $promo->promo_name }}</h4>
                <p class="text-white/80 text-xs mt-1">{{ \Illuminate\Support\Str::limit($promo->description, 50) }}</p>
              </div>
              <div class="text-right">
                @if($promo->discount_type === 'Percentage')
                  <div class="text-white text-4xl font-extrabold leading-none">{{ rtrim(rtrim(number_format($promo->discount_value, 0), '0'), '.') }}%</div>
                @else
                  <div class="text-white text-2xl font-extrabold leading-none">Rp {{ number_format($promo->discount_value, 0, ',', '.') }}</div>
                @endif
                <p class="text-white/70 text-[10px] font-medium mt-1">OFF</p>
              </div>
            </div>
            <button onclick="usePromo('{{ $promo->promo_code }}')" class="mt-4 w-full py-2.5 rounded-xl bg-white text-navy font-bold text-sm hover:bg-white/90 active:scale-[0.98] transition-all">
              Use Promo <i class="fas fa-arrow-right text-xs ml-2"></i>
            </button>
          </div>
          @empty
          <div class="text-center text-sm text-gray-400 py-6">Belum ada promo aktif saat ini.</div>
          @endforelse
        </div>
      </div>
    </div>

    <!-- Recommended Rooms -->
    <div class="animate-fade-in-up delay-5">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-lg font-bold text-navy">Recommended Room</h3>
          <p class="text-xs text-gray-400 mt-0.5">Pick your perfect stay</p>
        </div>
        <div class="flex items-center gap-2">
          <button onclick="scrollRooms(-1)" class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fas fa-chevron-left text-xs text-navy"></i>
          </button>
          <button onclick="scrollRooms(1)" class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fas fa-chevron-right text-xs text-navy"></i>
          </button>
        </div>
      </div>

      <div id="roomsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @php
          $roomGradients = [
            'from-navy/20 via-purple-100/50 to-blue-100/50',
            'from-amber-100/50 via-purple-100/30 to-pink-100/50',
            'from-green-100/50 via-teal-50 to-blue-100/50',
            'from-rose-100/50 via-orange-50 to-yellow-50',
          ];
        @endphp

        @forelse($rooms as $i => $room)
        @php
          // ⚠️ sesuaikan nama kolom/relasi kalau berbeda di model Room kamu
          $roomTypeName = optional($room->roomType)->name ?? ($room->type ?? 'Room');
          $pricePerNight = $room->price_per_night ?? $room->price ?? 0;
          $capacity = $room->capacity ?? $room->max_guests ?? 2;
        @endphp
        <div class="room-card bg-white rounded-[24px] overflow-hidden shadow-[0_2px_16px_rgba(11,23,57,0.04)] group card-hover">
          <div class="relative overflow-hidden h-44">
            <div class="room-img w-full h-full bg-gradient-to-br {{ $roomGradients[$i % count($roomGradients)] }} flex items-center justify-center">
              @if(!empty($room->image))
                <img src="{{ asset('storage/'.$room->image) }}" alt="{{ $roomTypeName }}" class="w-full h-full object-cover">
              @else
                <div class="text-center">
                  <i class="fas fa-bed text-4xl text-navy/20 mb-2 block"></i>
                  <span class="text-[10px] text-navy/30 font-medium">Room Photo</span>
                </div>
              @endif
            </div>
            <div class="absolute top-3 left-3">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-green-600 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                Available
              </span>
            </div>
            <div class="absolute top-3 right-3 flex gap-1.5">
              <button
                class="favorite-btn w-8 h-8 rounded-lg bg-white/90 backdrop-blur-sm flex items-center justify-center shadow-sm hover:bg-white transition-colors"
                onclick="toggleFavorite(this, event)"
                data-room-id="{{ $room->id }}"
              >
                <i class="far fa-heart text-xs text-gray-400 hover:text-red-400"></i>
              </button>
            </div>
          </div>
          <div class="p-5">
            <div class="flex items-start justify-between mb-2">
              <div>
                <h4 class="text-sm font-bold text-navy">{{ $roomTypeName }} Room</h4>
                <p class="text-[11px] text-gray-400 mt-0.5">Room {{ $room->room_number ?? '-' }} • {{ $capacity }} Guests</p>
              </div>
            </div>
            <div class="flex items-center justify-between mt-4">
              <div>
                <p class="text-lg font-extrabold text-navy">Rp {{ number_format($pricePerNight, 0, ',', '.') }}</p>
                <p class="text-[10px] text-gray-400">/night</p>
              </div>
              <button
                onclick="bookNow(this)"
                data-room-id="{{ $room->id }}"
                data-room-name="{{ $roomTypeName }} Room"
                data-room-price="{{ $pricePerNight }}"
                class="px-5 py-2.5 rounded-xl bg-navy text-white text-xs font-bold hover:bg-accent active:scale-95 transition-all shadow-lg shadow-navy/25"
              >
                Book Now
              </button>
            </div>
          </div>
        </div>
        @empty
        <div class="col-span-full text-center text-sm text-gray-400 py-10">Tidak ada kamar tersedia saat ini.</div>
        @endforelse
      </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 z-[100] transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
      <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-navy text-white shadow-2xl shadow-navy/30">
        <div id="toastIcon" class="w-8 h-8 rounded-xl bg-green-500/20 flex items-center justify-center">
          <i class="fas fa-check text-green-400 text-sm"></i>
        </div>
        <div>
          <p id="toastTitle" class="text-sm font-bold">Success</p>
          <p id="toastMessage" class="text-xs text-gray-300">Action completed</p>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 z-[90] hidden">
      <div class="absolute inset-0 bg-navy/50 backdrop-blur-sm" onclick="closeBookingModal()"></div>
      <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform scale-95 opacity-0 transition-all duration-300" id="bookingModalContent">
          <div class="bg-gradient-to-r from-navy to-accent p-6 relative">
            <button type="button" onclick="closeBookingModal()" class="absolute top-4 right-4 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
              <i class="fas fa-xmark text-white text-sm"></i>
            </button>
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                <i class="fas fa-calendar-check text-white text-xl"></i>
              </div>
              <div>
                <h3 class="text-white text-lg font-bold">Book Room</h3>
                <p class="text-white/60 text-xs" id="modalRoomName">-</p>
              </div>
            </div>
          </div>
          <form action="{{ route('customer.reservations.store') }}" method="POST" id="bookingForm">
            @csrf
            <div class="p-6">
              <input type="hidden" name="room_id" id="modalRoomId">
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-2">Check In</label>
                  <input type="date" name="check_in" required min="{{ now()->format('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl bg-bg border border-gray-200 text-sm text-navy outline-none focus:border-accent font-medium">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-2">Check Out</label>
                  <input type="date" name="check_out" required min="{{ now()->addDay()->format('Y-m-d') }}" class="w-full px-4 py-3 rounded-xl bg-bg border border-gray-200 text-sm text-navy outline-none focus:border-accent font-medium">
                </div>
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-2">Guests</label>
                  <select name="guests" class="w-full px-4 py-3 rounded-xl bg-bg border border-gray-200 text-sm text-navy outline-none focus:border-accent font-medium appearance-none cursor-pointer">
                    <option value="1">1 Guest</option>
                    <option value="2" selected>2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                  </select>
                </div>
                <div class="bg-bg rounded-xl p-4 flex items-center justify-between">
                  <span class="text-xs text-gray-500 font-medium">Estimated Total</span>
                  <span class="text-base font-extrabold text-navy" id="modalPrice">Rp 0</span>
                </div>
              </div>
              <button type="submit" class="w-full mt-6 py-3.5 rounded-xl bg-navy text-white font-bold text-sm hover:bg-accent active:scale-[0.98] transition-all shadow-lg shadow-navy/25">
                Confirm Booking
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('js/customer/dashboard.js') }}"></script>
@endpush
