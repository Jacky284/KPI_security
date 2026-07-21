<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\LaporanBulanan;
use Illuminate\Support\Facades\Auth;

class LaporanBulananWorkflowExport implements FromView, ShouldAutoSize
{
    protected $tahun;

    public function __construct($tahun)
    {
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        $user = Auth::user();
        $laporanQuery = LaporanBulanan::with('danruPembuat')->where('tahun', $this->tahun);
        
        if ($user->role === 'Danru') {
            $laporanQuery->where('regu', $user->regu);
        }
        
        $laporanBulanan = $laporanQuery->get();

        return view('exports.laporan_bulanan', [
            'laporanBulanan' => $laporanBulanan,
            'tahun' => $this->tahun,
        ]);
    }
}
