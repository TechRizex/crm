<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Agar request expects JSON (API/Postman se)
        if ($request->expectsJson() || $request->is('api/*')) {
            // Null return karein taake redirect na ho aur 401 response mile
            return null;
        }

        // Agar web request hai to login page pe bhej dein
        return route('login');
    }

    /**
     * Handle unauthenticated requests.
     */
    protected function unauthenticated($request, array $guards)
    {
        // Agar API request hai
        if ($request->expectsJson() || $request->is('api/*')) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }

        // Warna web ke liye default behavior
        parent::unauthenticated($request, $guards);
    }
}
