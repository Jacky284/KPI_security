<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\User;
use App\Models\JadwalBulanan;

class JadwalBulananExport implements FromView, ShouldAutoSize
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        // Query users
        $query = User::where('role', 'Anggota')->where('status_aktif', 1);
        if (strtolower(trim(auth()->user()->role)) === 'danru') {
            $query->where('regu', auth()->user()->regu);
        }
        $anggotas = $query->orderBy('nama_lengkap', 'asc')->get();

        $jadwals = JadwalBulanan::where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->whereIn('id_anggota', $anggotas->pluck('id_user'))
            ->get()
            ->keyBy('id_anggota');

        $logoBase64 = config('pdf_logo.base64');
        $reguName = $anggotas->pluck('regu')->unique()->filter()->implode(', ') ?: 'Semua Regu';
        
        $bulanMap = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
            'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
            'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
        ];
        $bulanAngka = is_numeric($this->bulan) ? (int)$this->bulan : ($bulanMap[$this->bulan] ?? date('n'));
        $daysInMonth = \Carbon\Carbon::createFromDate($this->tahun, $bulanAngka, 1)->daysInMonth;
        $tanggalRekap = \Carbon\Carbon::now()->format('d M Y');

        return view('exports.jadwal_bulanan', [
            'anggotas' => $anggotas,
            'jadwals' => $jadwals,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'logoBase64' => $logoBase64,
            'reguName' => $reguName,
            'bulanAngka' => $bulanAngka,
            'daysInMonth' => $daysInMonth,
            'tanggalRekap' => $tanggalRekap,
        ]);
    }
}
