<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Antrian;
use App\Models\Departemen;
use App\Models\Loket;

// Pastikan semua 'use' statement ini ada dan lengkap
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportController extends Controller
{
    public function showActivityReport()
    {
        $user = Auth::user();
        $departments = [];
        $counters = [];

        if ($user->role === 1) {
            $departments = Departemen::orderBy('nama_departemen')->get();
            $counters = Loket::orderBy('nama_loket')->get();
        }

        return view('reports.activity_history', compact('departments', 'counters'));
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->all();
        $user = Auth::user();

        $query = Antrian::query()
            ->with(['pelayanan.departemen.loket', 'pengunjung'])
            ->orderBy('created_at', 'asc');

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }
        if (!empty($filters['status'])) {
            $query->where('status_antrian', $filters['status']);
        }

        if ($user->role === 2) {
            $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id_loket', $user->id_loket));
        } elseif ($user->role === 1 && !empty($filters['department_id'])) {
            $query->whereHas('pelayanan.departemen', fn($q) => $q->where('id', $filters['department_id']));
        }

        $filename = 'Laporan_Antrian_' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(new class($query, $filters) implements FromQuery, WithMapping, WithHeadings, ShouldAutoSize, WithStyles, WithEvents {

            private Builder $query;
            private array $filters;
            private array $sortedLoketIds;
            private int $rowNumber = 0;

            public function __construct(Builder $query, array $filters) {
                $this->query = $query;
                $this->filters = $filters;
                $this->sortedLoketIds = Loket::orderBy('id', 'asc')->pluck('id')->toArray();
            }

            public function query() {
                return $this->query;
            }

            public function map($antrian): array {
                $this->rowNumber++;

                $kodeAntrian = 'X';
                $currentLoketId = $antrian->pelayanan->departemen->loket->id ?? null;
                if ($currentLoketId) {
                    $loketIndex = array_search($currentLoketId, $this->sortedLoketIds);
                    if ($loketIndex !== false) { $kodeAntrian = chr(65 + $loketIndex); }
                }
                $nomorAntrianLengkap = $kodeAntrian . str_pad($antrian->nomor_antrian, 3, '0', STR_PAD_LEFT);

                $statusMap = [1 => 'Menunggu', 2 => 'Dipanggil', 3 => 'Selesai', 4 => 'Dilewati'];
                $statusText = $statusMap[$antrian->status_antrian] ?? 'N/A';

                return [
                    $this->rowNumber,
                    $nomorAntrianLengkap,
                    $antrian->pengunjung->nama_pengunjung ?? '-',
                    $antrian->pelayanan->nama_layanan ?? '-',
                    $antrian->pelayanan->departemen->nama_departemen ?? '-',
                    $antrian->pelayanan->departemen->loket->nama_loket ?? '-',
                    $statusText,
                    $antrian->created_at ? $antrian->created_at->format('d-m-Y H:i:s') : '-',
                ];
            }

            public function headings(): array {
                return [ 'No', 'Nomor Antrian', 'Nama Pengunjung', 'Layanan', 'Departemen', 'Loket', 'Status', 'Waktu Daftar' ];
            }

            public function styles(Worksheet $sheet) {
                return [ 4 => ['font' => ['bold' => true]] ];
            }

            public function registerEvents(): array {
                return [
                    AfterSheet::class => function(AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $sheet->insertNewRowBefore(1, 3);
                        $sheet->mergeCells('A1:H1');
                        $sheet->setCellValue('A1', 'Laporan Aktivitas Antrian PTSP');
                        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                        $sheet->mergeCells('A2:H2');
                        $startDate = !empty($this->filters['start_date']) ? date('d M Y', strtotime($this->filters['start_date'])) : 'Awal';
                        $endDate = !empty($this->filters['end_date']) ? date('d M Y', strtotime($this->filters['end_date'])) : 'Akhir';
                        $sheet->setCellValue('A2', 'Periode: ' . $startDate . ' - ' . $endDate);
                        $sheet->getStyle('A2')->getFont()->setItalic(true);
                        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    },
                ];
            }
        }, $filename);
    }
}