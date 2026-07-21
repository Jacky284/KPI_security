<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\User;
use App\Models\JadwalBulanan;
use Carbon\Carbon;

class JadwalMingguanExport implements FromView, ShouldAutoSize
{
    protected $startDate;

    public function __construct($startDate)
    {
        $this->startDate = $startDate;
    }

    public function view(): View
    {
        $startOfWeek = Carbon::parse($this->startDate)->startOfWeek();
        
        // Prepare dates
        $weekDates = [];
        $monthYearsToFetch = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDates[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->locale('id')->isoFormat('dddd'),
                'day' => $date->format('j'),
                'month' => $date->format('m'),
                'year' => $date->format('Y')
            ];
            $monthYearsToFetch[$date->format('m-Y')] = [
                'bulan' => $date->format('m'),
                'tahun' => $date->format('Y')
            ];
        }

        // Query users
        $query = User::where('role', 'Anggota')->where('status_aktif', 1);
        if (strtolower(trim(auth()->user()->role)) === 'danru') {
            $query->where('regu', auth()->user()->regu);
        }
        $anggotas = $query->orderBy('nama_lengkap', 'asc')->get();

        $jadwalData = [];
        $anggotaIds = $anggotas->pluck('id_user')->toArray();
        if (!empty($anggotaIds)) {
            foreach ($monthYearsToFetch as $my) {
                $jadwals = JadwalBulanan::where('bulan', $my['bulan'])
                    ->where('tahun', $my['tahun'])
                    ->whereIn('id_anggota', $anggotaIds)
                    ->get();
                    
                foreach ($jadwals as $j) {
                    if (!isset($jadwalData[$j->id_anggota])) {
                        $jadwalData[$j->id_anggota] = [];
                    }
                    $harian = $j->jadwal_harian;
                    foreach ($weekDates as $wd) {
                        if ($wd['month'] === $my['bulan'] && $wd['year'] === $my['tahun']) {
                            $shift = isset($harian[$wd['day']]) ? $harian[$wd['day']] : 'Libur';
                            $jadwalData[$j->id_anggota][$wd['date']] = $shift;
                        }
                    }
                }
            }
        }

        return view('exports.jadwal_mingguan', [
            'anggotas' => $anggotas,
            'weekDates' => $weekDates,
            'jadwalData' => $jadwalData,
        ]);
    }
}
