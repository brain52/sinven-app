<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LbacVerify
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $requestedLocationId = $request->route('location_id') ?? $request->input('location_id');

        // Super Admin & Wakasek bypass LBAC
        if ($user->hasRole(['Super Admin', 'Wakasek Sarpras'])) {
            return $next($request);
        }

        // LBAC Check: Strict match between user's assigned location and requested location
        if ($requestedLocationId && $user->location_id != $requestedLocationId) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'code' => 403, 'message' => 'LBAC: Akses ditolak ke lokasi ini.'], 403);
            }
            abort(403, 'Anda tidak memiliki wewenang mengelola aset di lokasi ini.');
        }

        return $next($request);
    }
}