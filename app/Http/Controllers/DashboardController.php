<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower(trim($user->role));

        if ($role === 'chief' || $role === 'admin') {
            // Dashboard for Chief/Admin: Show Danrus and their weekly report status for the current month
            $danrus = User::where('role', 'Danru')->where('status_aktif', 1)->orderBy('regu', 'asc')->orderBy('nama_lengkap', 'asc')->get();
            
            $now = \Carbon\Carbon::now();
            $defaultBulanMap = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $currentBulan = $defaultBulanMap[$now->month];
            $currentTahun = $now->year;
            
            $laporan = \App\Models\LaporanMingguan::where('bulan', $currentBulan)
                        ->where('tahun', $currentTahun)
                        ->get()
                        ->groupBy('id_danru');

            $danruData = [];
            
            $bulanNum = str_pad($now->month, 2, '0', STR_PAD_LEFT);
            $firstDayOfMonth = \Carbon\Carbon::createFromDate($currentTahun, $bulanNum, 1);
            $daysInMonth = $firstDayOfMonth->daysInMonth;
            $firstDayIso = $firstDayOfMonth->dayOfWeekIso;

            foreach ($danrus as $danru) {
                $danruLaporan = $laporan->get($danru->id_user, collect());
                $mingguanStatus = [];
                for ($i = 1; $i <= 6; $i++) {
                    $totalWeeks = 4; // Fix to 4 weeks
                    if ($i > $totalWeeks) {
                        $mingguanStatus["minggu_$i"] = 'Not_Available';
                        continue;
                    }

                    $startOfWeek1 = \App\Http\Controllers\LaporanController::getStartOfWeek1($currentTahun, $bulanNum);
                    $endOfWeekDate = $startOfWeek1->copy()->addDays(($i - 1) * 7 + 6)->endOfDay();
                    $isPassed = $now->gt($endOfWeekDate);

                    $lap = $danruLaporan->firstWhere('minggu_ke', $i);
                    $statusDokumen = $lap ? $lap->status_dokumen : 'Draft';
                    
                    if (in_array($statusDokumen, ['Review_Chief', 'Review_Klien', 'Approved'])) {
                        $mingguanStatus["minggu_$i"] = 'Signed';
                    } else {
                        $mingguanStatus["minggu_$i"] = $isPassed ? 'Unsigned' : 'Pending';
                    }
                }
                
                $danruData[] = [
                    'id_user' => $danru->id_user,
                    'nama_lengkap' => $danru->nama_lengkap,
                    'regu' => $danru->regu,
                    'mingguan_status' => $mingguanStatus,
                ];
            }

            return Inertia::render('Dashboard/Index', [
                'currentUser' => [
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role,
                    'regu' => trim($user->regu),
                ],
                'danrus' => $danruData,
                'currentBulan' => $currentBulan,
                'currentTahun' => $currentTahun,
            ]);
        } else {
            // Dashboard for Danru/Anggota: Show Anggota and their weekly schedule
            $query = User::where('role', 'Anggota')->where('status_aktif', 1);

            if ($role === 'danru') {
                $query->where('regu', trim($user->regu));
            }

            $anggota = $query->orderBy('nama_lengkap', 'asc')->get();

            $startDateParam = $request->query('start_date');
            if ($startDateParam) {
                $startOfWeek = \Carbon\Carbon::parse($startDateParam)->startOfWeek();
            } else {
                $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
            }
            
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

            $jadwalData = [];
            $anggotaIds = $anggota->pluck('id_user')->toArray();
            if (!empty($anggotaIds)) {
                foreach ($monthYearsToFetch as $my) {
                    $jadwals = \App\Models\JadwalBulanan::where('bulan', $my['bulan'])
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

            foreach ($anggota as $a) {
                foreach ($weekDates as $wd) {
                    if (!isset($jadwalData[$a->id_user][$wd['date']])) {
                        $jadwalData[$a->id_user][$wd['date']] = 'Libur';
                    }
                }
            }

            // --- Calculate 3-Month Trend (Pagi & Malam) for the Regu ---
            $now = \Carbon\Carbon::now();
            $defaultBulanMap = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            $trendPagi = [];
            $trendMalam = [];

            $anggotaNames = $anggota->pluck('nama_lengkap', 'id_user')->toArray();

            if (!empty($anggotaIds)) {
                for ($i = 2; $i >= 0; $i--) {
                    $targetDate = $now->copy()->subMonths($i);
                    $tMonth = $targetDate->month;
                    $tYear = $targetDate->year;
                    $tBulanStr = $defaultBulanMap[$tMonth];
                    $shortBulan = substr($tBulanStr, 0, 3);
                    
                    $monthDataPagi = ['name' => $shortBulan];
                    $monthDataMalam = ['name' => $shortBulan];
                    
                    // Query violations and schedules for this month
                    $violationsThisMonth = \App\Models\CatatanPelanggaran::whereIn('id_anggota', $anggotaIds)
                        ->where('bulan', $tBulanStr)
                        ->where('tahun', $tYear)
                        ->get();
                        
                    $jadwalsThatMonth = \App\Models\JadwalBulanan::whereIn('id_anggota', $anggotaIds)
                        ->where('bulan', str_pad($tMonth, 2, '0', STR_PAD_LEFT))
                        ->where('tahun', $tYear)
                        ->get()->keyBy('id_anggota');

                    // Process shift schedules & deductions per person
                    $pagiDeduction = [];
                    $malamDeduction = [];
                    $hasPagiShift = [];
                    $hasMalamShift = [];

                    foreach ($anggotaNames as $id => $name) {
                        $pagiDeduction[$id] = 0;
                        $malamDeduction[$id] = 0;
                        $hasPagiShift[$id] = false;
                        $hasMalamShift[$id] = false;

                        if (isset($jadwalsThatMonth[$id]) && is_array($jadwalsThatMonth[$id]->jadwal_harian)) {
                            foreach ($jadwalsThatMonth[$id]->jadwal_harian as $d => $sStr) {
                                $lower = strtolower((string)$sStr);
                                if (strpos($lower, 'pagi') !== false || strpos($lower, 'siang') !== false) {
                                    $hasPagiShift[$id] = true;
                                } elseif (strpos($lower, 'malam') !== false) {
                                    $hasMalamShift[$id] = true;
                                }
                            }
                        }
                    }

                    foreach ($violationsThisMonth as $v) {
                        $deduction = 5;
                        if ($v->tingkat_pelanggaran === 'Sedang') $deduction = 10;
                        if ($v->tingkat_pelanggaran === 'Berat') $deduction = 25;
                        
                        $idAcc = $v->id_anggota;
                        if (isset($anggotaNames[$idAcc])) {
                            $shift = 'Libur';
                            if (isset($jadwalsThatMonth[$idAcc]) && is_array($jadwalsThatMonth[$idAcc]->jadwal_harian)) {
                                $day = \Carbon\Carbon::parse($v->tanggal_kejadian)->format('j');
                                $dailyShift = $jadwalsThatMonth[$idAcc]->jadwal_harian[$day] ?? 'Libur';
                                $lowerD = strtolower((string)$dailyShift);
                                if (strpos($lowerD, 'malam') !== false) {
                                    $shift = 'Malam';
                                } elseif (strpos($lowerD, 'pagi') !== false || strpos($lowerD, 'siang') !== false) {
                                    $shift = 'Pagi';
                                }
                            }

                            if ($shift === 'Malam') {
                                $malamDeduction[$idAcc] += $deduction;
                            } elseif ($shift === 'Pagi') {
                                $pagiDeduction[$idAcc] += $deduction;
                            }
                        }
                    }

                    foreach ($anggotaNames as $id => $name) {
                        $monthDataPagi[$name] = $hasPagiShift[$id] ? max(0, 100 - $pagiDeduction[$id]) : 0;
                        $monthDataMalam[$name] = $hasMalamShift[$id] ? max(0, 100 - $malamDeduction[$id]) : 0;
                    }
                    
                    $trendPagi[] = $monthDataPagi;
                    $trendMalam[] = $monthDataMalam;
                }
            }

            return Inertia::render('Dashboard/Index', [
                'anggota' => $anggota,
                'currentUser' => [
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role,
                    'regu' => trim($user->regu),
                ],
                'weekDates' => $weekDates,
                'jadwalMingguan' => $jadwalData,
                'currentStartDate' => $startOfWeek->format('Y-m-d'),
                'trendPagi' => $trendPagi,
                'trendMalam' => $trendMalam,
            ]);
        }
    }
}
