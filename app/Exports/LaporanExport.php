<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanExport implements FromView, ShouldAutoSize
{
    protected $performanceData;
    protected $bulan;
    protected $tahun;
    protected $minggu_ke;

    public function __construct($performanceData, $bulan, $tahun, $minggu_ke)
    {
        $this->performanceData = $performanceData;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->minggu_ke = $minggu_ke;
    }

    public function view(): View
    {
        return view('exports.laporan', [
            'performanceData' => $this->performanceData,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'minggu_ke' => $this->minggu_ke,
        ]);
    }
}
