<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PelanggaranExport implements FromView, ShouldAutoSize
{
    protected $pelanggaran;
    protected $bulan;
    protected $tahun;
    protected $minggu_ke;

    public function __construct($pelanggaran, $bulan, $tahun, $minggu_ke = null)
    {
        $this->pelanggaran = $pelanggaran;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->minggu_ke = $minggu_ke;
    }

    public function view(): View
    {
        return view('exports.daftar_penilaian', [
            'pelanggaran' => $this->pelanggaran,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'minggu_ke' => $this->minggu_ke,
        ]);
    }
}
