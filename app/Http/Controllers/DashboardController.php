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
    public  function showDashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 1) { // Admin
            return $this->showAdminDashboard($request);
        } elseif ($user->role === 2) { // Petugas
            return $this->showPetugasDashboard($user);
        }

        Auth::logout();
        return redirect('/login')->with('error', 'Peran pengguna tidak valid.');
    }

    /**
     * Menyiapkan data dan menampilkan dashboard untuk Admin.
     */
    private function showAdminDashboard(Request $request)
    {
        $today = Carbon::today();
        $selectedLoket = $request->input('loket_id', 'all');

        $baseQuery = Antrian::query();
        if ($selectedLoket !== 'all') {
            $baseQuery->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $selectedLoket));
        }

        $todayAntrians = (clone $baseQuery)->whereDate('created_at', $today)->get();

        $stats = [
            'today_queue' => $todayAntrians->count(),
            'missed'      => $todayAntrians->where('status_antrian', 4)->count(),
            'served'      => $todayAntrians->where('status_antrian', 3)->count(),
            'active'      => $todayAntrians->whereIn('status_antrian', [1, 2])->count()
        ];
        
        $chartData = [
            'labels'    => ['08:00', '10:00', '12:00', '14:00', '16:00'],
            'today'     => $this->getHourlyDataForDate($today, $selectedLoket),
            'yesterday' => $this->getHourlyDataForDate(Carbon::yesterday(), $selectedLoket)
        ];
        
        $weeklyVisitorData = $this->getWeeklyVisitorData($selectedLoket);
        $donutChartData = [
            'served' => $stats['served'],
            'missed' => $stats['missed'],
        ];

        $notifications = $this->getRecentNotifications();
        $lokets = Loket::orderBy('nama_loket')->get();

        return view('dashboard.admin', compact('stats', 'chartData', 'weeklyVisitorData', 'donutChartData', 'notifications', 'lokets', 'selectedLoket'));
    }

    /**
     * Menyiapkan data dan menampilkan dashboard untuk Petugas.
     */
    private function showPetugasDashboard($user)
    {
        $today = Carbon::today();
        $petugasInfo = Loket::find($user->id_loket);
        $loketId = $user->id_loket;

        if ($petugasInfo) {
            $petugasInfo->departemen = Departemen::where('id_loket', $petugasInfo->id)->first();
        }
        
        $stats = [
            'served_by_you'   => Antrian::whereDate('updated_at', $today)->where('status_antrian', 3)->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId))->count(),
            'missed_by_you'   => Antrian::whereDate('updated_at', $today)->where('status_antrian', 4)->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId))->count(),
            'waiting_for_you' => Antrian::whereDate('created_at', $today)->where('status_antrian', 1)->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId))->count(),
        ];

        // [PERBAIKAN] Memanggil helper dengan ID loket spesifik milik petugas
        $weeklyVisitorData = $this->getWeeklyVisitorData($loketId);
        $donutChartData = [
            'served' => $stats['served_by_you'],
            'missed' => $stats['missed_by_you'],
        ];

        return view('dashboard.petugas', compact('stats', 'petugasInfo', 'weeklyVisitorData', 'donutChartData'));
    }
    
    private function getHourlyDataForDate($date, $loketId = 'all')
    {
        $hours = [8, 10, 12, 14, 16];
        $data = [];
        foreach ($hours as $hour) {
            $query = Antrian::whereDate('created_at', $date)
                ->whereTime('created_at', '>=', Carbon::createFromTimeString("{$hour}:00:00"))
                ->whereTime('created_at', '<', Carbon::createFromTimeString(($hour + 2) . ":00:00"));

            if ($loketId !== 'all') {
                $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId));
            }
            $data[] = $query->count();
        }
        return $data;
    }

    private function getWeeklyVisitorData($loketId = 'all')
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);
        
        $query = Antrian::selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter ini akan berlaku untuk admin dan petugas
        if ($loketId !== 'all') {
            $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId));
        }

        $dataFromDb = $query->groupBy('tanggal')->orderBy('tanggal', 'asc')->get()->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());
        
        $labels = [];
        $data = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $labels[] = $date->translatedFormat('d M'); 
            $data[] = $dataFromDb->has($date->toDateString()) ? $dataFromDb[$date->toDateString()]->jumlah : 0;
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    private function getRecentNotifications()
    {
        $recentQueues = Antrian::with('pengunjung')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->latest()->limit(5)->get();
        $notifications = [];
        foreach ($recentQueues as $queue) {
             if ($queue->pengunjung) {
                $notifications[] = [
                    'type' => 'new', 'icon' => 'person_add',
                    'title' => 'Antrian Baru',
                    'message' => 'Pengunjung ' . $queue->pengunjung->nama_pengunjung . ' telah daftar.',
                    'time' => $queue->created_at->diffForHumans()
                ];
            }
        }
        return $notifications;
    }
}