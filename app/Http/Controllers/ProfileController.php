<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
{

    $user = $request->user();

    if ($request->hasFile('profile_photo')) {

        $previousPhotoPath = $user->profile_photo_path;

        $newPhotoPath = $request
            ->file('profile_photo')
            ->store('profile-photos', 'public');

        $user->profile_photo_path = $newPhotoPath;
        $user->save();

        if (
            $previousPhotoPath &&
            str_starts_with($previousPhotoPath, 'profile-photos/') &&
            $previousPhotoPath !== $newPhotoPath &&
            Storage::disk('public')->exists($previousPhotoPath)
        ) {
            Storage::disk('public')->delete($previousPhotoPath);
        }
    }

    return Redirect::route('profile.edit')
        ->with('status', 'profile-updated');
}

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->profile_photo_path && str_starts_with($user->profile_photo_path, 'profile-photos/') && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
