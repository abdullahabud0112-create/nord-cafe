<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCashierRole
{
    /**
     * Soft role gate: routes stay open while there is no login flow yet
     * (kiosk mode), but any authenticated user must hold the cashier
     * or admin role to pass — everyone else receives a friendly 403.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->hasCashierAccess()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses ditolak. Halaman ini hanya untuk kasir atau admin.',
                ], 403);
            }

            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
