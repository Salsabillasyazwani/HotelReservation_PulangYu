@extends('layouts.admin')
@section('title', 'Add New Reservation - Hotel Pulang Yo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/reservation.css') }}">
@endpush

@section('content')

    {{-- WAJIB: dipakai oleh script.js untuk request AJAX (CSRF + endpoint) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.ReservationConfig = {
            dataUrl: "{{ route('admin.reservations.data') }}",
            storeUrl: "{{ route('admin.reservations.store') }}",
            indexUrl: "{{ route('admin.reservations.index') }}",
            createUrl: "{{ route('admin.reservations.create') }}",
            availableRoomsUrl: "{{ route('admin.reservations.available-rooms') }}",
            validatePromoUrl: "{{ route('admin.reservations.validate-promo') }}",
            resourceBaseUrl: "{{ url('admin/reservations') }}",
        };
    </script>

    <!-- Toast Notification -->
    <div id="toast" class="toast flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-success">
            <i class="fa-solid fa-check"></i>
        </div>
        <div>
            <p class="font-semibold text-sm text-text">Success</p>
            <p id="toast-message" class="text-xs text-secondary">Operation completed successfully</p>
        </div>
    </div>

    <div class="max-w-[960px] mx-auto px-8 pt-6 pb-10">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-8">
            <div>
                <h1 class="text-[28px] font-semibold text-[#111827] tracking-[-0.025em]">Add New Reservation</h1>
                <p class="text-[#6B7280] mt-1">Create a new hotel reservation for guests.</p>
            </div>

            {{-- Breadcrumb --}}
            <div class="flex items-center text-sm">
                <div class="flex items-center text-[#6B7280]">
                    <span class="font-medium">Reservation</span>
                    <i class="mx-2 fa-solid fa-chevron-right text-xs"></i>
                    <span class="font-semibold text-[#1F3A8A]">Add Reservation</span>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- id="reservationForm" WAJIB ada, dipakai script.js untuk submit via AJAX --}}
        <form id="reservationForm" action="{{ route('admin.reservations.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Section 1: Guest Information --}}
            <div class="bg-white rounded-3xl p-6 luxury-shadow border border-[#E5E7EB] section-card">
                <div class="flex items-center gap-x-3 mb-5">
                    <div class="w-8 h-8 flex items-center justify-center bg-[#1F3A8A] text-white rounded-2xl text-sm font-bold">1</div>
                    <span class="section-header text-lg text-[#111827]">Guest Information</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Guest Name <span class="text-red-500">*</span></label>
                        <input type="text" name="guest_name" id="guestName" value="{{ old('guest_name') }}" placeholder="Enter guest full name"
                               class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A]">
                        @error('guest_name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Phone Number <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="e.g. 0812-3456-7890"
                               class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A]">
                        @error('phone')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="guest@example.com"
                               class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A]">
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Identity Number (KTP / Passport) <span class="text-red-500">*</span></label>
                        <input type="text" name="identity_number" id="identity" value="{{ old('identity_number') }}" placeholder="Enter KTP / Passport number"
                               class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A]">
                        @error('identity_number')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Nationality <span class="text-red-500">*</span></label>
                        <select name="nationality" id="nationality" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] modern-select">
                            @foreach (['Indonesia', 'Singapore', 'Malaysia', 'United States', 'United Kingdom'] as $country)
                                <option value="{{ $country }}" {{ old('nationality', 'Indonesia') == $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                        @error('nationality')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section 2: Reservation Information --}}
            <div class="bg-white rounded-3xl p-6 luxury-shadow border border-[#E5E7EB] section-card">
                <div class="flex items-center gap-x-3 mb-5">
                    <div class="w-8 h-8 flex items-center justify-center bg-[#1F3A8A] text-white rounded-2xl text-sm font-bold">2</div>
                    <span class="section-header text-lg text-[#111827]">Reservation Information</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Reservation ID</label>
                        <input type="text" id="reservationId" value="{{ $reservationId }}" readonly
                               class="form-input w-full px-4 py-3 bg-[#F7F9FC] border border-[#E5E7EB] rounded-2xl text-sm text-[#6B7280]">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Check In Date <span class="text-red-500">*</span></label>
                            <input type="date" name="check_in" id="checkIn" value="{{ old('check_in', date('Y-m-d')) }}"
                                   class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A]">
                            @error('check_in')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Check Out Date <span class="text-red-500">*</span></label>
                            <input type="date" name="check_out" id="checkOut" value="{{ old('check_out', date('Y-m-d', strtotime('+2 days'))) }}"
                                   class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A]">
                            @error('check_out')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Number of Nights</label>
                        <input type="text" id="nights" value="" readonly
                               class="form-input w-full px-4 py-3 bg-[#F7F9FC] border border-[#E5E7EB] rounded-2xl text-sm text-[#6B7280]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Number of Guests <span class="text-red-500">*</span></label>
                        <div class="flex items-center border border-[#E5E7EB] rounded-2xl px-4 py-3 bg-white">
                            <i class="fa-solid fa-users text-[#6B7280]"></i>
                            <input type="number" name="guests" id="guests" value="{{ old('guests', 1) }}" min="1" class="ml-3 flex-1 bg-transparent outline-none text-sm">
                        </div>
                        @error('guests')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Reservation Status</label>
                        <select name="reservation_status" id="reservationStatus" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] modern-select">
                            @foreach (['Pending', 'Confirmed', 'Cancelled'] as $status)
                                <option value="{{ $status }}" {{ old('reservation_status', 'Pending') == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                        @error('reservation_status')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Section 3: Room Selection --}}
            <div class="bg-white rounded-3xl p-6 luxury-shadow border border-[#E5E7EB] section-card">
                <div class="flex items-center gap-x-3 mb-5">
                    <div class="w-8 h-8 flex items-center justify-center bg-[#1F3A8A] text-white rounded-2xl text-sm font-bold">3</div>
                    <span class="section-header text-lg text-[#111827]">Room Selection</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Room Type <span class="text-red-500">*</span></label>
                        <select name="room_type" id="roomType" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] modern-select">
                            @forelse ($roomTypes ?? [] as $type)
                                <option value="{{ $type->name }}" {{ old('room_type') == $type->name ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @empty
                                <option value="">No room types available</option>
                            @endforelse
                        </select>
                        @error('room_type')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Available Room <span class="text-red-500">*</span></label>
                        {{-- Diisi ulang oleh JS setiap kali room_type / tanggal berubah via endpoint available-rooms --}}
                        <select name="available_room" id="availableRoom" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] modern-select">
                            @forelse ($availableRooms ?? [] as $room)
                                <option value="{{ $room }}">{{ $room }}</option>
                            @empty
                                <option value="">No rooms available</option>
                            @endforelse
                        </select>
                        @error('available_room')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Price per Night</label>
                            <div class="px-4 py-3 bg-[#F7F9FC] border border-[#E5E7EB] rounded-2xl">
                                <span id="pricePerNight" class="font-semibold text-[#111827]">-</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Room Capacity</label>
                            <div class="px-4 py-3 bg-[#F7F9FC] border border-[#E5E7EB] rounded-2xl text-sm">
                                <span id="roomCapacity">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- Room Preview: gambar & nama diisi JS dari data room asli (bukan hardcode) --}}
                    <div class="md:col-span-2">
                        <div class="flex gap-4 items-center">
                            <div class="w-40 h-24 bg-gray-100 rounded-3xl overflow-hidden border border-[#E5E7EB] flex items-center justify-center shrink-0">
                                <img id="roomImage" src="" class="w-full h-full object-cover hidden room-image" alt="Room Preview">
                                <i class="fa-solid fa-image text-2xl text-[#D1D5DB]" id="roomImagePlaceholder"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-x-2">
                                    <span id="roomName" class="font-semibold text-[#111827]">Select a room</span>
                                    <span id="roomStatusBadge" class="status-badge px-3 py-0.5 rounded-3xl text-xs bg-gray-100 text-gray-500 font-medium">-</span>
                                </div>
                                <div id="roomFacilities" class="flex gap-3 text-[#6B7280] mt-2 text-xs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 4: Payment Information --}}
            <div class="bg-white rounded-3xl p-6 luxury-shadow border border-[#E5E7EB] section-card">
                <div class="flex items-center gap-x-3 mb-5">
                    <div class="w-8 h-8 flex items-center justify-center bg-[#1F3A8A] text-white rounded-2xl text-sm font-bold">4</div>
                    <span class="section-header text-lg text-[#111827]">Payment Information</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-5">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Payment Method</label>
                        <select name="payment_method" id="paymentMethod" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] modern-select">
                            @foreach (['Transfer Bank', 'Credit Card', 'Cash', 'E-Wallet'] as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Payment Status</label>
                        <select name="payment_status" id="paymentStatus" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] modern-select">
                            <option value="Unpaid">Unpaid</option>
                            <option value="Paid">Paid</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Promo Code</label>
                        <div class="flex gap-2">
                            <input type="text" name="promo_code" id="promoCode" value="{{ old('promo_code') }}"
                                   placeholder="Enter promo code (optional)"
                                   class="form-input flex-1 px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm focus:border-[#1F3A8A] uppercase">
                            <button type="button" id="btnApplyPromo" class="btn-outline px-5 py-3 rounded-2xl font-semibold text-sm whitespace-nowrap">Apply</button>
                        </div>
                        <p id="promoFeedback" class="text-xs mt-1.5 hidden"></p>
                        @error('promo_code')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-3 gap-4 md:col-span-2">
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Deposit Amount</label>
                            <input type="number" name="deposit" id="deposit" value="{{ old('deposit', 0) }}" placeholder="0" min="0" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-2xl text-sm">
                            @error('deposit')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Tax (10%)</label>
                            <div class="px-4 py-3 bg-[#F7F9FC] border border-[#E5E7EB] rounded-2xl text-sm font-medium" id="tax">Rp 0</div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Discount</label>
                            <div class="px-4 py-3 bg-[#F7F9FC] border border-[#E5E7EB] rounded-2xl text-sm font-medium" id="discount">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 5: Additional Information --}}
            <div class="bg-white rounded-3xl p-6 luxury-shadow border border-[#E5E7EB] section-card">
                <div class="flex items-center gap-x-3 mb-5">
                    <div class="w-8 h-8 flex items-center justify-center bg-[#1F3A8A] text-white rounded-2xl text-sm font-bold">5</div>
                    <span class="section-header text-lg text-[#111827]">Additional Information</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Special Request</label>
                        <textarea name="special_request" id="specialRequest" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-3xl text-sm h-20 resize-none" placeholder="e.g. High floor room, please.">{{ old('special_request') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#6B7280] mb-1.5">Notes</label>
                        <textarea name="notes" id="notes" class="form-input w-full px-4 py-3 bg-white border border-[#E5E7EB] rounded-3xl text-sm h-20 resize-none" placeholder="Internal notes about the guest">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-x-3">
                <a href="{{ route('admin.reservations.index') }}" class="btn-outline px-6 py-3 text-sm font-semibold rounded-3xl">
                    Cancel
                </a>
                <button type="submit" name="action" value="save" class="btn-outline px-6 py-3 text-sm font-semibold rounded-3xl">
                    Save Reservation
                </button>
                <button type="submit" name="action" value="save_checkin" class="btn-gradient-primary px-8 py-3 text-sm font-semibold rounded-3xl">
                    Save &amp; Check In
                </button>
            </div>

        </form>

    </div>

    {{-- ============================================================
         Reservation Summary Popup
         ------------------------------------------------------------
         Muncul hanya ketika user klik "Save Reservation" / "Save &
         Check In" -> berfungsi sebagai konfirmasi terakhir sebelum
         data benar-benar disimpan (step 1: preview, step 2: sukses).
         Semua field #summary* di sini otomatis ke-update oleh JS
         (updateSummary()) setiap ada perubahan input di form,
         meskipun modal ini masih tersembunyi.
         ============================================================ --}}
    <div id="save-summary-modal" class="modal-overlay">
        <div class="modal-content bg-white rounded-3xl w-full max-w-md mx-auto mt-16 shadow-soft-lg max-h-[85vh] overflow-y-auto">

            <div class="p-6" id="save-summary-body">
                <div class="flex items-center gap-x-3 mb-5">
                    <div class="w-9 h-9 flex items-center justify-center bg-[#1F3A8A] text-white rounded-2xl">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <span class="section-header text-lg text-[#111827]">Reservation Summary</span>
                </div>

                {{-- Guest Info --}}
                <div class="mb-5">
                    <div class="text-xs uppercase tracking-wider text-[#6B7280] mb-2 font-medium">Guest Information</div>
                    <div class="flex items-center gap-x-3">
                        <div id="summaryGuestInitial" class="w-9 h-9 bg-[#E5E7EB] rounded-2xl flex items-center justify-center text-[#1F3A8A] font-bold">-</div>
                        <div>
                            <div id="summaryGuestName" class="font-semibold">-</div>
                            <div id="summaryPhone" class="text-xs text-[#6B7280]">-</div>
                        </div>
                    </div>
                </div>

                {{-- Room Info --}}
                <div class="mb-5">
                    <div class="text-xs uppercase tracking-wider text-[#6B7280] mb-2 font-medium">Room Information</div>
                    <div class="flex gap-3">
                        <div class="w-16 h-16 rounded-2xl border bg-gray-100 flex items-center justify-center overflow-hidden shrink-0">
                            <img id="summaryRoomImage" src="" class="w-full h-full object-cover hidden" alt="">
                            <i class="fa-solid fa-image text-[#D1D5DB]"></i>
                        </div>
                        <div>
                            <div id="summaryRoomName" class="font-semibold">-</div>
                            <div class="text-xs text-[#6B7280] font-medium flex items-center gap-1"><span id="summaryRoomStatus">-</span></div>
                            <div id="summaryRoomCapacity" class="text-xs text-[#6B7280]">-</div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-[#E5E7EB] pt-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Check In</span>
                        <span id="summaryCheckIn" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Check Out</span>
                        <span id="summaryCheckOut" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Duration</span>
                        <span id="summaryDuration" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#6B7280]">Guests</span>
                        <span id="summaryGuests" class="font-medium">-</span>
                    </div>

                    <div class="pt-4 border-t border-[#E5E7EB]">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[#6B7280]">Price per Night</span>
                            <span id="summaryPrice" class="font-semibold">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-baseline mt-1">
                            <span class="text-[#6B7280]" id="summaryNightsLabel">Total</span>
                            <span id="summaryTotal" class="font-semibold">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-baseline mt-1">
                            <span class="text-[#6B7280]">Tax (10%)</span>
                            <span id="summaryTax" class="font-semibold">Rp 0</span>
                        </div>

                        <div class="pt-4 mt-4 border-t border-[#E5E7EB] flex justify-between items-center">
                            <span class="font-semibold text-base">Grand Total</span>
                            <span id="summaryGrandTotal" class="font-bold text-[#1F3A8A] text-2xl">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 pb-6" id="save-summary-actions">
                <button type="button" id="btn-back-edit" class="btn-outline px-6 py-3 rounded-2xl font-semibold text-sm">Back to Edit</button>
                <button type="button" id="btn-confirm-save" class="btn-gradient-primary px-6 py-3 rounded-2xl font-semibold text-sm">Confirm &amp; Save</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
     <script src="{{ asset('js/admin/reservation.js') }}"></script>
@endpush
