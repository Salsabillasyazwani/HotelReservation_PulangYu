@extends('layouts.customer')

@section('title', 'Profile - Hotel Pulang Yo')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/customer/profile.css') }}">
@endpush

@section('content')

@php
    $avatarSrc = $user->avatar_url ?? ('https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=2563EB&color=ffffff&size=200&bold=true');
    $roleName = $user->role->name ?? 'Customer';
@endphp

@if(session('success'))
    <div id="serverFlash" data-type="success" data-message="{{ session('success') }}" class="hidden"></div>
@elseif($errors->any())
    <div id="serverFlash" data-type="error" data-message="{{ $errors->first() }}" class="hidden"></div>
@endif

<section class="profile-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Kelola informasi akun dan keamanan profilmu.</p>
    </div>
    <button type="submit" form="profileForm" class="btn-save">
        <i class="fa-solid fa-floppy-disk"></i>
        <span>Save Changes</span>
    </button>
</section>

<section class="profile-grid">

    {{-- LEFT COLUMN --}}
    <div class="profile-col">

        <div class="card">
            <h3 class="card-title">Profile Photo</h3>

            <div class="avatar-block">
                <div class="avatar-wrap">
                    <div id="avatarCircle" class="avatar-circle">
                        <img id="profileAvatar" class="avatar-image" src="{{ $avatarSrc }}" alt="avatar">
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

                {{-- form upload/hapus foto - auto submit lewat profile.js --}}
                <form id="avatarUploadForm" method="POST" action="{{ route('customer.profile.avatar.update') }}" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" name="avatar" id="photoInput" accept="image/jpeg,image/jpg,image/png" onchange="previewPhoto(event)">
                </form>
                <form id="avatarDeleteForm" method="POST" action="{{ route('customer.profile.avatar.delete') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

                <h4 id="displayName" class="user-name">{{ $user->name }}</h4>
                <p class="user-role">{{ ucfirst($roleName) }}</p>

                <div class="info-list">
                    <div class="info-row">
                        <div class="info-icon"><i class="fa-regular fa-calendar"></i></div>
                        <div>
                            <p class="info-label">Joined At</p>
                            <p class="info-value">{{ $user->created_at->format('d F Y') }}</p>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon info-icon-success"><i class="fa-regular fa-circle-check"></i></div>
                        <div>
                            <p class="info-label">Email Verified</p>
                            <span class="status-pill {{ $user->email_verified_at ? 'pill-active' : 'pill-inactive' }}">
                                {{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div class="profile-col">

        {{-- Personal Information --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Personal Information</h3>
                <p class="card-subtitle">Update informasi pribadi dan detail akunmu.</p>
            </div>

            <form id="profileForm" method="POST" action="{{ route('customer.profile.update') }}" class="form-grid">
                @csrf
                @method('PUT')

                <div class="form-field">
                    <label class="form-label" for="editName">Full Name</label>
                    <input id="editName" name="name" type="text" value="{{ old('name', $user->name) }}" class="field">
                    @error('name')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label" for="editEmail">Email Address</label>
                    <input id="editEmail" name="email" type="email" value="{{ old('email', $user->email) }}" class="field">
                    @error('email')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
                <p class="card-subtitle">Perbarui password secara berkala untuk keamanan akunmu.</p>
            </div>

            <form id="passwordForm" onsubmit="return validatePasswordForm(event)" method="POST" action="{{ route('customer.profile.password.update') }}" class="form-grid">
                @csrf
                @method('PUT')

                <div class="form-field md:col-span-2">
                    <label class="form-label">Current Password</label>
                    <div class="input-with-icon">
                        <input type="password" name="current_password" required placeholder="Masukkan password lama" class="field">
                        <button type="button" onclick="togglePassword('current_password')" class="input-icon-btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-field">
                    <label class="form-label">New Password</label>
                    <div class="input-with-icon">
                        <input type="password" name="new_password" id="newPassword" oninput="checkPasswordStrength(this.value)" required minlength="8" placeholder="Masukkan password baru" class="field">
                        <button type="button" onclick="togglePassword('new_password')" class="input-icon-btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <div class="strength-wrap">
                        <div class="strength-bars">
                            <div id="str1" class="strength-bar"></div>
                            <div id="str2" class="strength-bar"></div>
                            <div id="str3" class="strength-bar"></div>
                            <div id="str4" class="strength-bar"></div>
                        </div>
                        <p id="strengthText" class="strength-text">Password strength</p>
                    </div>
                </div>

                <div class="form-field">
                    <label class="form-label">Confirm New Password</label>
                    <div class="input-with-icon">
                        {{-- nama field ini HARUS new_password_confirmation agar rule 'confirmed' di Laravel jalan --}}
                        <input type="password" name="new_password_confirmation" required placeholder="Konfirmasi password baru" class="field">
                        <button type="button" onclick="togglePassword('new_password_confirmation')" class="input-icon-btn">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-field md:col-span-2">
                    <button type="submit" class="btn-save w-full justify-center">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>

</section>

<div id="toast" class="toast hidden"></div>

@endsection

@push('scripts')
    <script src="{{ asset('js/customer/profile.js') }}?v={{ filemtime(public_path('js/customer/profile.js')) }}"></script>
@endpush
