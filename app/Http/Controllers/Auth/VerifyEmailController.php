<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnPlatform($request);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectBasedOnPlatform($request);
    }

    /**
     * Redirect based on the platform: mobile app or web.
     */
    private function redirectBasedOnPlatform($request): RedirectResponse
    {
        $userAgent = $request->header('User-Agent');
        
        // Check if the request is from a mobile browser
        $isMobileBrowser = preg_match('/(android|iphone|ipad|ipod|mobile)/i', $userAgent);

        if ($isMobileBrowser) {
            // Redirect to deep link for mobile app
            return redirect()->away('myapp://email-verified?verified=1');
        } else {
            // Redirect to web dashboard for desktop browsers
            return redirect()->intended(RouteServiceProvider::FORM . '?verified=1');
        }
    }
}
