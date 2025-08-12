<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan peran pengguna
     */
    public function showDashboard()
    {
        $stats = [
            'today_queue' => 24,
            'missed' => 3,
            'served' => 18,
            'overtime' => 2
        ];

        // Cek peran pengguna
        $role = auth()->check() ? auth()->user()->role : 'guest';

        if ($role === 'admin') {
            return view('dashboard.admin', compact('stats'));
        } elseif ($role === 'petugas') {
            return view('dashboard.petugas', compact('stats'));
        }
        
        return view('dashboard.admin', compact('stats'));
    }
}