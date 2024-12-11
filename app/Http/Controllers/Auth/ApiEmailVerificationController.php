<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class ApiEmailVerificationController extends Controller
{
    // Show verification notice
    public function showVerificationNotice(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        return response()->json(['message' => 'Email not verified. Please check your inbox.'], 403);
    }

    // Send verification email
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent.'], 200);
    }

    // Verifikasi email
    public function verifyEmail(Request $request, $id, $hash): JsonResponse
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);

        // Periksa apakah email sudah diverifikasi
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        // Verifikasi hash untuk memastikan keabsahan link
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        // Tandai email sebagai terverifikasi
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return response()->json(['message' => 'Email verified successfully.'], 200);
        }

        return response()->json(['message' => 'Failed to verify email.'], 500);
    }
}
