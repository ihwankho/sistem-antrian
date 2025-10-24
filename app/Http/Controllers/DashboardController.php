<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Antrian;
use App\Models\Loket;
use App\Models\Departemen;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan peran (role) pengguna yang login.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showDashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 1) {
            // Role 1: Admin
            return $this->showAdminDashboard($request);
        } elseif ($user->role === 2) {
            // Role 2: Petugas Loket
            return $this->showPetugasDashboard($user);
        }

        // Jika peran tidak dikenali, logout dan redirect
        Auth::logout();
        return redirect('/login')->with('error', 'Peran pengguna tidak valid atau tidak diizinkan.');
    }

    /**
     * Menampilkan dashboard untuk Admin.
     * Menyajikan statistik harian, mingguan, data grafik, dan notifikasi.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    private function showAdminDashboard(Request $request)
    {
        $today = Carbon::today();
        // Ambil filter Loket ID dari request, default 'all'
        $selectedLoket = $request->input('loket_id', 'all');

        // Statistik Harian (Total, Dilayani, Dilewatkan, Aktif)
        $stats = Antrian::whereDate('created_at', $today)
            ->when($selectedLoket !== 'all', function ($query) use ($selectedLoket) {
                // Filter berdasarkan Loket ID jika bukan 'all'
                $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $selectedLoket));
            })
            ->selectRaw("
                COUNT(*) as today_queue,
                SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as served,
                SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as missed,
                SUM(CASE WHEN status_antrian IN (1, 2) THEN 1 ELSE 0 END) as active
            ")
            ->first();

        // Data Grafik Per Jam (Perbandingan Hari Ini vs Kemarin)
        $chartData = [
            'labels'    => ['08-10', '10-12', '12-14', '14-16', '16-18'],
            'today'     => $this->getHourlyDataForDate($today, $selectedLoket),
            'yesterday' => $this->getHourlyDataForDate(Carbon::yesterday(), $selectedLoket)
        ];

        // Data Grafik Mingguan
        $weeklyVisitorData = $this->getWeeklyVisitorData($selectedLoket);

        // Data Donut Chart (Rasio Dilayani vs Dilewatkan)
        $donutChartData = [
            'served' => $stats->served ?? 0,
            'missed' => $stats->missed ?? 0,
        ];

        // Notifikasi Terbaru
        $notifications = $this->getRecentNotifications();

        // Daftar Loket untuk filter dropdown
        $lokets = Loket::orderBy('nama_loket')->get();

        return view('dashboard.admin', compact('stats', 'chartData', 'weeklyVisitorData', 'donutChartData', 'notifications', 'lokets', 'selectedLoket'));
    }

    /**
     * Menampilkan dashboard untuk Petugas Loket.
     * Menyajikan statistik kinerja pribadi dan antrian yang menunggu.
     * * @param mixed $user Objek user yang sedang login.
     * @return \Illuminate\View\View
     */
    private function showPetugasDashboard($user)
    {
        $today = Carbon::today();

        // Mengambil info Loket dan Departemen yang dilayani oleh petugas
        $petugasInfo = Loket::with('departemens')->find($user->id_loket);
        $loketId = $user->id_loket;

        // Statistik Kinerja Petugas (Dilayani, Dilewatkan, Menunggu)
        $stats = Antrian::whereDate('created_at', $today)
            ->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId))
            ->selectRaw("
                SUM(CASE WHEN status_antrian = 3 THEN 1 ELSE 0 END) as served_by_you,
                SUM(CASE WHEN status_antrian = 4 THEN 1 ELSE 0 END) as missed_by_you,
                SUM(CASE WHEN status_antrian = 1 THEN 1 ELSE 0 END) as waiting_for_you
            ")
            ->first();

        // Data Grafik Mingguan untuk Loket ini
        $weeklyVisitorData = $this->getWeeklyVisitorData($loketId);

        // Data Donut Chart Kinerja
        $donutChartData = [
            'served' => $stats->served_by_you ?? 0,
            'missed' => $stats->missed_by_you ?? 0,
        ];

        return view('dashboard.petugas', compact('stats', 'petugasInfo', 'weeklyVisitorData', 'donutChartData'));
    }

    /**
     * Mendapatkan data antrian per interval waktu per jam.
     * * @param \Carbon\Carbon $date Tanggal yang akan dianalisis.
     * @param string $loketId Filter Loket ID ('all' atau ID numerik).
     * @return array
     */
    private function getHourlyDataForDate($date, $loketId = 'all')
    {
        $intervals = [
            '08-10' => [8, 10], '10-12' => [10, 12], '12-14' => [12, 14],
            '14-16' => [14, 16], '16-18' => [16, 18]
        ];

        $query = Antrian::whereDate('created_at', $date)
            ->when($loketId !== 'all', function ($query) use ($loketId) {
                $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId));
            });

        // Menggunakan CASE statement untuk mengelompokkan data per jam
        $counts = $query->select(
                DB::raw("CASE
                    WHEN HOUR(created_at) >= 8 AND HOUR(created_at) < 10 THEN '08-10'
                    WHEN HOUR(created_at) >= 10 AND HOUR(created_at) < 12 THEN '10-12'
                    WHEN HOUR(created_at) >= 12 AND HOUR(created_at) < 14 THEN '12-14'
                    WHEN HOUR(created_at) >= 14 AND HOUR(created_at) < 16 THEN '14-16'
                    WHEN HOUR(created_at) >= 16 AND HOUR(created_at) < 18 THEN '16-18'
                    ELSE NULL END as interval_group"),
                DB::raw("COUNT(*) as count")
            )
            ->whereTime('created_at', '>=', '08:00:00')
            ->whereTime('created_at', '<', '18:00:00')
            ->groupBy('interval_group')
            ->get()
            ->pluck('count', 'interval_group');

        $result = [];
        // Memastikan urutan dan nilai nol untuk interval yang tidak memiliki data
        foreach ($intervals as $label => $hours) {
            $result[] = $counts[$label] ?? 0;
        }

        return $result;
    }

    /**
     * Mendapatkan data jumlah pengunjung selama 7 hari terakhir.
     * * @param string $loketId Filter Loket ID ('all' atau ID numerik).
     * @return array
     */
    private function getWeeklyVisitorData($loketId = 'all')
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6); // 7 hari termasuk hari ini

        $query = Antrian::selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);

        if ($loketId !== 'all') {
            $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $loketId));
        }

        $dataFromDb = $query->groupBy('tanggal')->orderBy('tanggal', 'asc')
                            ->get()->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());

        $labels = [];
        $data = [];
        // Iterasi 7 hari untuk memastikan urutan dan nilai nol
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            // Label dalam format 'd M' (misalnya, '21 Okt')
            $labels[] = $date->translatedFormat('d M');
            // Mengambil jumlah dari DB, atau 0 jika tidak ada
            $data[] = $dataFromDb[$date->toDateString()]->jumlah ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Mendapatkan notifikasi antrian baru dalam 30 menit terakhir.
     * * @return \Illuminate\Support\Collection
     */
    private function getRecentNotifications()
    {
        // Mengambil antrian yang dibuat dalam 30 menit terakhir
        $recentQueues = Antrian::with('pengunjung:id,nama_pengunjung')
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->latest()->limit(5)->get();

        return $recentQueues->map(function ($queue) {
            if ($queue->pengunjung) {
                // Membuat format notifikasi yang mudah ditampilkan di view
                return [
                    'type' => 'new',
                    'icon' => 'person_add',
                    'title' => 'Antrian Baru',
                    'message' => 'Pengunjung ' . $queue->pengunjung->nama_pengunjung . ' telah mendaftar.',
                    'time' => $queue->created_at->diffForHumans() // Contoh: '5 menit yang lalu'
                ];
            }
            return null;
        })->filter(); // Menghapus entri yang mungkin null
    }
}