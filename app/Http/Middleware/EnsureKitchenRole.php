<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKitchenRole
{
    /**
     * Soft role gate mirroring EnsureCashierRole: routes stay open while the
     * login flow is not built (kiosk mode), but any authenticated user must
     * hold the kitchen or admin role to enter the operational kitchen area.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasKitchenAccess()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses ditolak. Halaman ini hanya untuk dapur atau admin.',
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
