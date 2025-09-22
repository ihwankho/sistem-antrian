<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Antrian;
use App\Models\Loket;
use App\Models\Departemen;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan peran pengguna.
     */
    public function showDashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();

        if ($user->role === 1) {
            return $this->showAdminDashboard($today);
        } elseif ($user->role === 2) {
            return $this->showPetugasDashboard($user, $today);
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Peran pengguna tidak valid.');
    }

    /**
     * Menyiapkan data dan menampilkan dashboard untuk Admin.
     */
    private function showAdminDashboard($date)
    {
        $stats = [
            'today_queue' => Antrian::whereDate('created_at', $date)->count(),
            'missed'      => Antrian::whereDate('created_at', $date)->where('status_antrian', 4)->count(),
            'served'      => Antrian::whereDate('created_at', $date)->where('status_antrian', 3)->count(),
            'active'      => Antrian::whereDate('created_at', $date)->whereIn('status_antrian', [1, 2])->count()
        ];
        $chartData = [
            'labels'    => ['08:00', '10:00', '12:00', '14:00', '16:00'],
            'today'     => $this->getHourlyDataForDate($date),
            'yesterday' => $this->getHourlyDataForDate(Carbon::yesterday())
        ];
        $notifications = $this->getRecentNotifications();
        return view('dashboard.admin', compact('stats', 'chartData', 'notifications'));
    }

    /**
     * Menyiapkan data dan menampilkan dashboard untuk Petugas.
     */
    private function showPetugasDashboard($user, $date)
    {
        $petugasInfo = Loket::find($user->id_loket);

        if ($petugasInfo) {
            $petugasInfo->departemen = Departemen::where('id_loket', $petugasInfo->id)->first();
        }

        $loketId = $petugasInfo ? $petugasInfo->id : null;
        
        $stats = [
            // =================================================================
            // PERBAIKAN DI SINI: Menggunakan whereHas untuk filter via relasi
            // =================================================================
            'served_by_you'   => Antrian::whereDate('updated_at', $date)
                                      ->where('status_antrian', 3) // Filter status dulu
                                      ->whereHas('pelayanan.departemen', function ($query) use ($loketId) {
                                          $query->where('id_loket', $loketId);
                                      })
                                      ->count(),
            'missed_by_you'   => Antrian::whereDate('updated_at', $date)
                                      ->where('status_antrian', 4) // Filter status dulu
                                      ->whereHas('pelayanan.departemen', function ($query) use ($loketId) {
                                          $query->where('id_loket', $loketId);
                                      })
                                      ->count(),
            // =================================================================
            
            'waiting_for_you' => Antrian::whereDate('created_at', $date)
                                      ->where('status_antrian', 1)
                                      ->whereHas('pelayanan.departemen', function ($query) use ($petugasInfo) {
                                          if ($petugasInfo && $petugasInfo->departemen) {
                                              $query->where('id', $petugasInfo->departemen->id);
                                          }
                                      })
                                      ->count(),
        ];

        return view('dashboard.petugas', compact('stats', 'petugasInfo'));
    }
    
    private function getHourlyDataForDate($date)
    {
        $hours = [8, 10, 12, 14, 16];
        $data = [];
        foreach ($hours as $hour) {
            $count = Antrian::whereDate('created_at', $date)
                ->whereTime('created_at', '>=', Carbon::createFromTimeString("{$hour}:00:00"))
                ->whereTime('created_at', '<=', Carbon::createFromTimeString("{$hour}:59:59"))
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    private function getRecentNotifications()
    {
        $recentQueues = Antrian::with('pengunjung')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->latest()->limit(2)->get();
        $notifications = [];
        foreach ($recentQueues as $queue) {
            $notifications[] = [
                'type' => 'new', 'icon' => 'person_add',
                'title' => 'Antrian Baru',
                'message' => 'Nomor antrian ' . $queue->nomor_antrian . ' telah daftar.',
                'time' => $queue->created_at->diffForHumans()
            ];
        }
        return $notifications;
    }
}