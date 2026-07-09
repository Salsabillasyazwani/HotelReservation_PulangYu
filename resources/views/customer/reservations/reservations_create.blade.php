<div id="bookingModal" class="fixed inset-0 z-50 hidden items-start justify-center p-4 bg-black/50 overflow-y-auto">
    <div class="modal-scale bg-white rounded-2xl shadow-2xl w-full max-w-5xl my-6">

        <div class="bg-navy text-white p-6 rounded-t-2xl flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold">Book Reservation</h3>
                <p class="text-sm text-gold" id="bookingStepLabel">Step 1 of 3 &middot; Choose your dates</p>
            </div>
            <button type="button" id="btnCloseBooking" class="p-2 hover:bg-white/10 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6">

    
            <div id="bookingStep1">
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-navy mb-1">Check In Date</label>
                        <input id="searchCheckIn" type="date" min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                        <p class="error-text hidden text-xs text-red-500 mt-1" data-error-for="check_in"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy mb-1">Check Out Date</label>
                        <input id="searchCheckOut" type="date" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                        <p class="error-text hidden text-xs text-red-500 mt-1" data-error-for="check_out"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-navy mb-1">Number of Guests</label>
                        <input id="searchGuests" type="number" min="1" value="1" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                    </div>
                </div>
                <div class="flex justify-end mt-5">
                    <button type="button" id="btnSearchRooms" class="bg-gold hover:bg-yellow-500 text-navy font-semibold px-6 py-3 rounded-xl shadow-md transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search Available Rooms
                    </button>
                </div>
            </div>

            <div id="bookingStep2" class="hidden">
                <button type="button" id="btnBackToStep1" class="flex items-center gap-2 text-sm text-slate-500 hover:text-navy mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Change dates
                </button>

                <div id="roomLoading" class="hidden text-center py-10 text-slate-400 text-sm">Searching available rooms...</div>

                <div id="noRoomsFound" class="hidden text-center py-10 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    No rooms available for the selected dates.
                </div>

                <div id="roomResultsGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
            </div>

            <!-- STEP 3: data tamu, promo, summary -->
            <div id="bookingStep3" class="hidden">
                <button type="button" id="btnBackToStep2" class="flex items-center gap-2 text-sm text-slate-500 hover:text-navy mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Choose different room
                </button>

                <form id="bookingForm">
                    @csrf
                    <input type="hidden" id="selectedRoomId" name="room_id">
                    <input type="hidden" id="selectedRoomTypeId">
                    <input type="hidden" id="selectedRoomPrice">
                    <input type="hidden" id="finalCheckIn" name="check_in">
                    <input type="hidden" id="finalCheckOut" name="check_out">
                    <input type="hidden" id="finalGuests" name="guests">

                    <div class="grid lg:grid-cols-3 gap-6">
                        <!-- Selected room card -->
                        <div class="lg:col-span-1">
                            <div id="selectedRoomCard" class="rounded-2xl overflow-hidden border border-slate-100 shadow-sm"></div>
                        </div>

                        <!-- Guest form -->
                        <div class="lg:col-span-2 space-y-5">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1">Guest Name</label>
                                    <input type="text" value="{{ auth()->user()->name }}" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1">Email</label>
                                    <input type="email" value="{{ auth()->user()->email }}" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1">Phone</label>
                                    <input name="phone" id="phone" type="text" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                                    <p class="error-text hidden text-xs text-red-500 mt-1" data-error-for="phone"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-navy mb-1">Identity Number (KTP/Passport)</label>
                                    <input name="identity_number" id="identity_number" type="text" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                                    <p class="error-text hidden text-xs text-red-500 mt-1" data-error-for="identity_number"></p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-navy mb-1">Nationality</label>
                                <input name="nationality" id="nationality" type="text" value="Indonesia" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-navy mb-1">Promotion Code <span class="text-slate-400 font-normal">(optional)</span></label>
                                <input id="promoCode" name="promo_code" type="text" placeholder="e.g. WEEKEND20" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
                                <p id="promoMessage" class="text-xs mt-1"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-navy mb-1">Special Request <span class="text-slate-400 font-normal">(optional)</span></label>
                                <textarea name="special_request" id="special_request" rows="2" placeholder="e.g. Late check-in, extra pillows..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm"></textarea>
                            </div>

                            <!-- SUMMARY -->
                            <div class="bg-[#F8FAFC] rounded-2xl p-5 border border-slate-100">
                                <h4 class="font-semibold text-navy mb-3">Reservation Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between"><span class="text-slate-500">Room Price</span><span id="sumPrice">Rp 0</span></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Number of Nights</span><span id="sumNights">0 night</span></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span id="sumSubtotal">Rp 0</span></div>
                                    <div class="flex justify-between text-green-600"><span>Discount</span><span id="sumDiscount">- Rp 0</span></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Tax (10%)</span><span id="sumTax">Rp 0</span></div>
                                    <div class="border-t border-dashed border-slate-300 my-2"></div>
                                    <div class="flex justify-between text-base font-bold text-navy"><span>Total Payment</span><span id="sumTotal">Rp 0</span></div>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" id="btnCancelBooking" class="flex-1 bg-slate-100 hover:bg-slate-200 text-navy font-semibold py-3 rounded-xl transition">Cancel</button>
                                <button type="submit" id="btnConfirmReservation" class="flex-1 bg-gold hover:bg-yellow-500 text-navy font-semibold py-3 rounded-xl shadow-md transition">Confirm Reservation</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
