<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('admin.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|min:10|max:30',
            'status'       => ['required', Rule::in(['Active', 'Inactive', 'Suspended'])],
            'photo'        => 'nullable|image|max:2048',
            'remove_photo' => 'nullable|boolean',
        ]);

        // Hapus foto lama kalau user pilih "Hapus Foto"
        if ($request->boolean('remove_photo') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $validated['avatar'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('photo')->store('avatars', 'public');
        }

        unset($validated['photo'], $validated['remove_photo']);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
