<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $previousPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $previousPhotoPath = $user->profile_photo_path;
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        unset($data['profile_photo']);
        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        if ($previousPhotoPath && str_starts_with($previousPhotoPath, 'profile-photos/') && $previousPhotoPath !== $user->profile_photo_path && Storage::disk('public')->exists($previousPhotoPath)) {
            Storage::disk('public')->delete($previousPhotoPath);
        }

        return Redirect::route('admin.profile.edit')->with('status', 'profile-updated');
    }
}
