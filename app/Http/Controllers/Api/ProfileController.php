<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile information.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully.',
            'data' => $user,
        ], 200);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update user information
        $user->fill($request->only(['name', 'email']));

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User profile updated successfully.',
            'data' => $user,
        ], 200);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password.',
            ], 403);
        }

        Auth::logout();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User account deleted successfully.',
        ], 200);
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $user = $request->user();

        // Validasi file
        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Maksimum 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Hapus foto lama jika ada
        if ($user->photo) {
            Storage::delete($user->photo);
        }

        // Simpan foto baru
        $path = $request->file('photo')->store('profile_photos', 'public');

        // Perbarui URL foto di database
        $user->photo = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile photo updated successfully.',
            'data' => [
                'photo_url' => asset('storage/' . $path),
            ],
        ], 200);
    }
}
