<div class="topbar">

  <div class="burger" onclick="toggleSidebar()">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
  </div>

  <div class="search-box" id="globalSearchBox">
    <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
    <input type="text" id="searchBox" class="search-input" placeholder="" autocomplete="off">
    <span class="kbd">Ctrl /</span>
    <div class="search-dropdown" id="searchResults"></div>
  </div>

  <div class="topbar-right">

    {{-- Notifikasi --}}
    <div class="icon-btn relative" onclick="event.stopPropagation(); toggleDrop('notifDrop')">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      <span class="badge" id="notifBadge">3</span>
      <div class="dropdown-menu" id="notifDrop">
        <a href="#">Reservasi baru masuk</a>
        <a href="#">Tamu check-in hari ini</a>
        <a href="#">Pembayaran diterima</a>
      </div>
    </div>

    {{-- Pesan --}}
    <div class="icon-btn relative" onclick="event.stopPropagation(); toggleDrop('msgDrop')">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      <span class="badge blue" id="msgBadge">2</span>
      <div class="dropdown-menu" id="msgDrop">
        <a href="#">Pesan dari Guest Service</a>
        <a href="#">Pesan dari Housekeeping</a>
      </div>
    </div>

    {{-- Dark mode --}}
    <div class="icon-btn" onclick="toggleDark()">
      <i id="darkIcon" class="bi bi-moon-stars" style="font-size:18px;color:#4b5265;"></i>
    </div>

    {{-- Profile --}}
    <div class="profile relative" onclick="event.stopPropagation(); toggleDrop('profileDrop')">
      <div class="avatar">
        @if(auth()->user()->photo ?? false)
          <img src="{{ asset('storage/'.auth()->user()->photo) }}"
               alt="avatar"
               style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
        @else
          {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        @endif
      </div>
      <div>
        <div class="profile-name">{{ auth()->user()->name ?? 'Administrator' }}</div>
        <div class="profile-role">{{ optional(auth()->user()->role)->name ?? 'Super Admin' }}</div>
      </div>
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
      <div class="dropdown-menu" id="profileDrop">
        <a href="{{ route('profile.edit') }}">Profil Saya</a>
        <a href="#" onclick="logoutAction(); return false;">Keluar</a>
      </div>
    </div>

  </div>
</div>
