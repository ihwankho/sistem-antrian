<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();
        
        // Konversi semua parameter roles ke integer
        $allowedRoles = array_map('intval', $roles);
        
        // Periksa apakah user memiliki role yang diizinkan
        if (in_array($user->role, $allowedRoles)) {
            return $next($request);
        }

        // Jika tidak memiliki akses, logout dan redirect ke login
        Auth::logout();
        return redirect('/login')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}