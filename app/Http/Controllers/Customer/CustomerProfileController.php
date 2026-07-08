<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CustomerProfileController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('customer.profile.index', [
            'user' => $user,
        ]);
    }

    /**
     * Update data dasar: name, email.
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password.
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required'],
            'new_password'      => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        $user->update([
            // Model User sudah punya cast 'password' => 'hashed',
            // jadi tidak perlu Hash::make() manual di sini.
            'password' => $validated['new_password'],
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Upload / ganti avatar.
     */
    public function updateAvatar(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $disk = config('filesystems.default');

        if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
            Storage::disk($disk)->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', $disk);

        $user->update([
            'avatar' => $path,
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Hapus avatar (kembali ke avatar default di UI).
     */
    public function deleteAvatar()
    {
        /** @var User $user */
        $user = Auth::user();

        $disk = config('filesystems.default');

        if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
            Storage::disk($disk)->delete($user->avatar);
        }

        $user->update([
            'avatar' => null,
        ]);

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }
}
