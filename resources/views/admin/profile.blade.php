@extends('layouts.admin')

@section('title', 'My Profile')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/Admin/profile.css') }}">
@endpush

@section('content')

<section class="profile-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Kelola informasi akun dan preferensi tampilanmu.</p>
    </div>
    <button type="submit" form="profileForm" id="saveBtn" class="btn-save">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Save Changes</span>
    </button>
</section>

@if (session('success'))
    <div class="alert-success mb-5">{{ session('success') }}</div>
@endif

<section class="profile-grid">

    {{-- LEFT COLUMN --}}
    <div class="profile-col">

        <div class="card">
            <h3 class="card-title">Profile Photo</h3>

            <div class="avatar-block">
                <div class="avatar-wrap">
                    <div id="avatarCircle" class="avatar-circle">
                        <span id="avatarLetter" class="avatar-letter {{ $user->photo ? 'hidden' : '' }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                        <img id="avatarImage"
                             class="avatar-image {{ $user->photo ? '' : 'hidden' }}"
                             src="{{ $user->photo ? asset('storage/'.$user->photo) : '' }}"
                             alt="avatar">
                    </div>

                    <button type="button" id="photoMenuBtn" class="avatar-edit-btn">
                        <i class="fa-solid fa-camera"></i>
                    </button>

                    <div id="photoMenu" class="profile-dropdown photo-menu hidden">
                        <button type="button" id="choosePhotoBtn" class="dropdown-item">
                            <i class="fa-regular fa-image"></i> Pilih Foto
                        </button>
                        <button type="button" id="removePhotoBtn" class="dropdown-item">
                            <i class="fa-regular fa-trash-can"></i> Hapus Foto
                        </button>
                    </div>
                </div>

                <h4 id="displayName" class="user-name">{{ $user->name }}</h4>
                <p class="user-role">{{ optional($user->role)->name ?? '-' }}</p>

                <div class="info-list">
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-regular fa-calendar"></i></div>
                        <div>
                            <p class="info-label">Joined At</p>
                            <p class="info-value">{{ optional($user->created_at)->format('d F Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon"><i class="fa-regular fa-clock"></i></div>
                        <div>
                            <p class="info-label">Last Login</p>
                            <p class="info-value">{{ optional($user->last_login_at)->format('d F Y, h:i A') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon info-icon-success"><i class="fa-regular fa-circle-check"></i></div>
                        <div>
                            <p class="info-label">Status</p>
                            @php
                                $statusClass = match($user->status) {
                                    'Active'    => 'pill-active',
                                    'Suspended' => 'pill-suspended',
                                    default     => 'pill-inactive',
                                };
                            @endphp
                            <span id="leftStatusPill" class="status-pill {{ $statusClass }}">
                                {{ $user->status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="profile-col">

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Personal Information</h3>
                <p class="card-subtitle">Update informasi pribadi dan detail akunmu.</p>
            </div>

            <form id="profileForm" method="POST" action="{{ route('profile.update') }}"
                  enctype="multipart/form-data" class="form-grid">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label class="form-label" for="fullName">Full Name</label>
                    <input id="fullName" name="name" type="text" value="{{ old('name', $user->name) }}" class="field">
                    @error('name')
                        <p class="error-text">{{ $message }}</p>
                    @else
                        <p class="error-text hidden">Nama wajib diisi.</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label" for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" class="field">
                    @error('username')
                        <p class="error-text">{{ $message }}</p>
                    @else
                        <p class="error-text hidden">Username wajib diisi.</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="field">
                    @error('email')
                        <p class="error-text">{{ $message }}</p>
                    @else
                        <p class="error-text hidden">Email tidak valid.</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Role</label>
                    <input type="text" value="{{ optional($user->role)->name ?? '-' }}" disabled class="field field-disabled">
                </div>

                <div class="form-field">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input id="phone" name="phone_number" type="text" value="{{ old('phone_number', $user->phone_number) }}" class="field">
                    @error('phone_number')
                        <p class="error-text">{{ $message }}</p>
                    @else
                        <p class="error-text hidden">Nomor telepon minimal 10 digit.</p>
                    @enderror
                </div>

                <div class="form-field relative">
                    <label class="form-label">Status</label>
                    <button id="statusBtn" type="button" class="field status-btn">
                        <span class="status-btn-left">
                            <span id="statusDot" class="badge-dot"></span>
                            <span id="statusText">{{ old('status', $user->status) }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-down status-chevron"></i>
                    </button>
                    <input type="hidden" name="status" id="statusInput" value="{{ old('status', $user->status) }}">

                    <div id="statusMenu" class="dropdown-menu status-menu hidden">
                        <button type="button" data-status="Active" class="status-option dropdown-item">
                            <span class="badge-dot dot-active"></span><span>Active</span>
                        </button>
                        <button type="button" data-status="Inactive" class="status-option dropdown-item">
                            <span class="badge-dot dot-inactive"></span><span>Inactive</span>
                        </button>
                        <button type="button" data-status="Suspended" class="status-option dropdown-item">
                            <span class="badge-dot dot-suspended"></span><span>Suspended</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

</section>

<div id="toast" class="toast hidden"></div>

@endsection

@push('scripts')
<script src="{{ asset('js/Admin/profile.js') }}?v={{ filemtime(public_path('js/Admin/profile.js')) }}"></script>
@endpush
