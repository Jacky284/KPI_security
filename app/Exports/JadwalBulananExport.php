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

        return view('exports.jadwal_bulanan', [
            'anggotas' => $anggotas,
            'jadwals' => $jadwals,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
        ]);
    }
}
