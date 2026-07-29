<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LaporanBulanan;
use App\Models\CatatanPelanggaran;
use App\Models\JadwalBulanan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    private function getDetailedMonthlyData($bulan, $tahun, $filter_regu = null)
    {
        $user = Auth::user();
        $anggotaQuery = User::where('status_aktif', 1);
        if (strtolower(trim($user->role)) === 'danru') {
            $anggotaQuery->where('role', 'Anggota')->where('regu', trim($user->regu));
        } else if (strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') {
            $anggotaQuery->where('role', 'Anggota');
            if ($filter_regu) {
                $anggotaQuery->where('regu', $filter_regu);
            }
        } else {
            $anggotaQuery->where('role', 'Anggota');
        }

        $anggotaList = $anggotaQuery->get()->sort(function($a, $b) {
            $aRegu = $a->regu ?: 'Z';
            $bRegu = $b->regu ?: 'Z';
            $reguCompare = strnatcasecmp($aRegu, $bRegu);
            if ($reguCompare === 0) {
                $isADanru = $a->role === 'Danru' ? 0 : 1;
                $isBDanru = $b->role === 'Danru' ? 0 : 1;
                if ($isADanru !== $isBDanru) return $isADanru - $isBDanru;
                return strnatcasecmp($a->nama_lengkap, $b->nama_lengkap);
            }
            return $reguCompare;
        })->values();
        $indicators = ['Disiplin Kerja', 'Kehadiran', 'Penampilan & Kerapihan', 'Komunikasi & Pelayanan'];
        $targets = [
            'Disiplin Kerja' => 95,
            'Kehadiran' => 100,
            'Penampilan & Kerapihan' => 100,
            'Komunikasi & Pelayanan' => 90,
        ];

        $perPerson = [];
        $indicatorTotals = [
            'Disiplin Kerja' => ['total' => 0, 'max' => 0],
            'Kehadiran' => ['total' => 0, 'max' => 0],
            'Penampilan & Kerapihan' => ['total' => 0, 'max' => 0],
            'Komunikasi & Pelayanan' => ['total' => 0, 'max' => 0],
        ];

        $allViolations = CatatanPelanggaran::whereIn('id_anggota', $anggotaList->pluck('id_user'))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        $bulanNum = str_pad($this->parseBulanToNumber($bulan), 2, '0', STR_PAD_LEFT);
        $firstOfMonth = \Carbon\Carbon::createFromDate($tahun, $bulanNum, 1)->startOfDay();
        $daysInMonth = $firstOfMonth->daysInMonth;
        $dayOfWeek = $firstOfMonth->dayOfWeek; // 0 (Sun) - 6 (Sat)
        
        $startOfWeek1 = clone $firstOfMonth;
        if ($dayOfWeek === 6) {
            // same
        } else if ($dayOfWeek === 0 || $dayOfWeek === 1 || $dayOfWeek === 2) {
            $daysToSubtract = $dayOfWeek + 1;
            $startOfWeek1->subDays($daysToSubtract);
        } else {
            $daysToAdd = 6 - $dayOfWeek;
            $startOfWeek1->addDays($daysToAdd);
        }
        
        $dayToWeekMap = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $targetDate = clone $firstOfMonth;
            $targetDate->day($d);
            
            $weekNum = 1;
            if ($targetDate->gte($startOfWeek1)) {
                $diffDays = $startOfWeek1->diffInDays($targetDate);
                $weekNum = (int)floor($diffDays / 7) + 1;
            }
            if ($weekNum > 4) $weekNum = 4;
            if ($weekNum < 1) $weekNum = 1;
            
            $dayToWeekMap[$d] = (int)$weekNum;
        }

        $totalWeeks = 4;

        $jadwals = JadwalBulanan::whereIn('id_anggota', $anggotaList->pluck('id_user'))
            ->where('bulan', str_pad($this->parseBulanToNumber($bulan), 2, '0', STR_PAD_LEFT))
            ->where('tahun', $tahun)
            ->get()->keyBy('id_anggota');

        foreach ($anggotaList as $officer) {
            $weeklyScores = [];
            $totalMonthScore = 0;
            $maxMonthScore = 0;
            $jadwalHarian = isset($jadwals[$officer->id_user]) ? $jadwals[$officer->id_user]->jadwal_harian : [];

            for ($m = 1; $m <= $totalWeeks; $m++) {
                $passedWorkingDays = 0;
                $hasSchedule = false;
                
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    if ($dayToWeekMap[$d] !== $m) continue;

                    $shiftDay = isset($jadwalHarian[$d]) ? $jadwalHarian[$d] : null;
                    if ($shiftDay) {
                        $hasSchedule = true;
                        if ($shiftDay !== 'Libur') {
                            $dateOfD = clone $firstOfMonth;
                            $dateOfD->day($d)->startOfDay();
                            if (\Carbon\Carbon::now()->gte($dateOfD)) {
                                $passedWorkingDays++;
                            }
                        }
                    }
                }

                $totalWeekScore = 0;
                foreach ($indicators as $ind) {
                    $violation = $allViolations->where('id_anggota', $officer->id_user)
                        ->where('minggu_ke', $m)
                        ->where('kategori_indikator', $ind)
                        ->first();

                    $score = 5;
                    if ($violation) {
                        $tingkat = $violation->tingkat_penilaian;
                        if (in_array($tingkat, ['Ringan 1 kali', 'Kurang rapi 1 kali', 'Terlambat 1 kali', 'Komplain ringan'])) {
                            $score = 4;
                        } elseif (in_array($tingkat, ['Ringan 2 kali', 'Kurang rapi 2 kali', 'Terlambat 2 kali', 'Komplain sedang'])) {
                            $score = 3;
                        } elseif (in_array($tingkat, ['Sedang', 'Seragam tidak lengkap', 'Tidak hadir dengan izin', 'Sering mendapat teguran'])) {
                            $score = 2;
                        } elseif (in_array($tingkat, ['Berat', 'Penampilan tidak sesuai Standar', 'Mangkir / Alpha', 'Komplain berat'])) {
                            $score = 1;
                        }
                    }

                    if ($hasSchedule && $passedWorkingDays > 0) {
                        $totalWeekScore += $score;
                        $indicatorTotals[$ind]['total'] += $score;
                        $indicatorTotals[$ind]['max'] += 5;
                    }
                }
                
                if (!$hasSchedule || $passedWorkingDays === 0) {
                    $weeklyScores['M' . $m] = null;
                } else {
                    $percentage = ($totalWeekScore / 20) * 100;
                    $weeklyScores['M' . $m] = $percentage;
                    $totalMonthScore += $totalWeekScore;
                    $maxMonthScore += 20;
                }
            }

            if ($maxMonthScore === 0) {
                $avgPercentage = null;
                $penilaian = '-';
            } else {
                $avgPercentage = ($totalMonthScore / $maxMonthScore) * 100;
                $penilaian = 'Kurang';
                if ($avgPercentage >= 90) $penilaian = 'Sangat Baik';
                elseif ($avgPercentage >= 75) $penilaian = 'Baik';
                elseif ($avgPercentage >= 60) $penilaian = 'Cukup';
            }

            $perPerson[] = [
                'id_user' => $officer->id_user,
                'nama_lengkap' => $officer->nama_lengkap,
                'regu' => $officer->regu,
                'role' => $officer->role,
                'weekly_scores' => $weeklyScores,
                'avg_percentage' => $avgPercentage,
                'penilaian' => $penilaian,
            ];
        }

        $perIndicator = [];
        foreach ($indicators as $ind) {
            $max = $indicatorTotals[$ind]['max'];
            $total = $indicatorTotals[$ind]['total'];
            $achievedPercentage = $max > 0 ? ($total / $max) * 100 : 0;
            
            if ($ind === 'Disiplin Kerja') {
                $ket = $achievedPercentage > 95 ? 'Tercapai' : 'Tidak tercapai';
                $targetText = '<span style="color: #2f855a;">> 95%</span>';
            } elseif ($ind === 'Komunikasi & Pelayanan') {
                $ket = $achievedPercentage >= 90 ? 'Tercapai' : 'Tidak tercapai';
                $targetText = '<span style="color: #2f855a;">>= 90%</span>';
            } else {
                $ket = $achievedPercentage >= $targets[$ind] ? 'Tercapai' : 'Tidak tercapai';
                $targetText = $targets[$ind] == 100 ? '100%' : '<span style="color: #2f855a;">>= ' . $targets[$ind] . '%</span>';
            }

            $perIndicator[] = [
                'indikator' => $ind,
                'target' => $targets[$ind],
                'target_text' => $targetText,
                'achieved_percentage' => $achievedPercentage,
                'keterangan' => $ket,
            ];
        }

        return [
            'perPerson' => $perPerson,
            'perIndicator' => $perIndicator,
            'totalWeeks' => $totalWeeks
        ];
    }
    private function getLaporanData($bulan, $tahun, $minggu_ke, $filter_regu = null)
    {
        $user = Auth::user();
        
        // Fetch Security Officers (Anggota)
        $anggotaQuery = User::where('status_aktif', 1);
        
        if (strtolower(trim($user->role)) === 'danru') {
            $anggotaQuery->where('role', 'Anggota')->where('regu', trim($user->regu));
        } else if (strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') {
            $anggotaQuery->where('role', 'Anggota');
            if ($filter_regu) {
                $anggotaQuery->where('regu', $filter_regu);
            }
        } else {
            $anggotaQuery->where('role', 'Anggota');
        }
        
        $anggotaList = $anggotaQuery->get()->sort(function($a, $b) {
            $aRegu = $a->regu ?: 'Z';
            $bRegu = $b->regu ?: 'Z';
            $reguCompare = strnatcasecmp($aRegu, $bRegu);
            if ($reguCompare === 0) {
                $isADanru = $a->role === 'Danru' ? 0 : 1;
                $isBDanru = $b->role === 'Danru' ? 0 : 1;
                if ($isADanru !== $isBDanru) return $isADanru - $isBDanru;
                return strnatcasecmp($a->nama_lengkap, $b->nama_lengkap);
            }
            return $reguCompare;
        })->values();
        $bulanNum = str_pad($this->parseBulanToNumber($bulan), 2, '0', STR_PAD_LEFT);
        
        $startOfWeek1 = self::getStartOfWeek1($tahun, $bulanNum);
        $currentWeekStart = $startOfWeek1->copy()->addDays(($minggu_ke - 1) * 7);
        $currentWeekEnd = $currentWeekStart->copy()->addDays(6);

        // Fetch Jadwal for current month
        $jadwals = JadwalBulanan::whereIn('id_anggota', $anggotaList->pluck('id_user'))
            ->where('bulan', $bulanNum)
            ->where('tahun', $tahun)
            ->get()->keyBy('id_anggota');

        // Check if we need previous month schedules
        $jadwalsPrevMonth = null;
        if ($currentWeekStart->month != (int)$bulanNum) {
            $prevMonthNum = str_pad($currentWeekStart->month, 2, '0', STR_PAD_LEFT);
            $jadwalsPrevMonth = JadwalBulanan::whereIn('id_anggota', $anggotaList->pluck('id_user'))
                ->where('bulan', $prevMonthNum)
                ->where('tahun', $currentWeekStart->year)
                ->get()->keyBy('id_anggota');
        }

        // Check if we need next month schedules
        $jadwalsNextMonth = null;
        if ($currentWeekEnd->month != (int)$bulanNum) {
            $nextMonthNum = str_pad($currentWeekEnd->month, 2, '0', STR_PAD_LEFT);
            $jadwalsNextMonth = JadwalBulanan::whereIn('id_anggota', $anggotaList->pluck('id_user'))
                ->where('bulan', $nextMonthNum)
                ->where('tahun', $currentWeekEnd->year)
                ->get()->keyBy('id_anggota');
        }

        $performanceData = [];
        $indicators = ['Disiplin Kerja', 'Kehadiran', 'Penampilan & Kerapihan', 'Komunikasi & Pelayanan'];
        $now = \Carbon\Carbon::now();

        foreach ($anggotaList as $officer) {
            $passedWorkingDays = 0;
            $hasSchedule = false;

            for ($date = $currentWeekStart->copy(); $date->lte($currentWeekEnd); $date->addDay()) {
                $d = $date->day;
                $shiftDay = null;

                if ($date->month == (int)$bulanNum) {
                    $shiftDay = isset($jadwals[$officer->id_user]) ? ($jadwals[$officer->id_user]->jadwal_harian[$d] ?? null) : null;
                } elseif ($jadwalsPrevMonth && $date->month == $currentWeekStart->month) {
                    $shiftDay = isset($jadwalsPrevMonth[$officer->id_user]) ? ($jadwalsPrevMonth[$officer->id_user]->jadwal_harian[$d] ?? null) : null;
                } elseif ($jadwalsNextMonth && $date->month == $currentWeekEnd->month) {
                    $shiftDay = isset($jadwalsNextMonth[$officer->id_user]) ? ($jadwalsNextMonth[$officer->id_user]->jadwal_harian[$d] ?? null) : null;
                }

                if ($shiftDay) {
                    $hasSchedule = true;
                    if ($shiftDay !== 'Libur') {
                        if ($now->gte($date->copy()->startOfDay())) {
                            $passedWorkingDays++;
                        }
                    }
                }
            }

            $violations = CatatanPelanggaran::where('id_anggota', $officer->id_user)
                ->where('minggu_ke', $minggu_ke)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->get();

            $scores = [];
            $totalScore = 0;

            foreach ($indicators as $ind) {
                $score = 5;
                foreach ($violations->where('kategori_indikator', $ind) as $violation) {
                    $tingkat = $violation->tingkat_penilaian;
                    $currentScore = 5;
                    if (in_array($tingkat, ['Ringan 1 kali', 'Kurang rapi 1 kali', 'Terlambat 1 kali', 'Komplain ringan'])) {
                        $currentScore = 4;
                    } elseif (in_array($tingkat, ['Ringan 2 kali', 'Kurang rapi 2 kali', 'Terlambat 2 kali', 'Komplain sedang'])) {
                        $currentScore = 3;
                    } elseif (in_array($tingkat, ['Sedang', 'Seragam tidak lengkap', 'Tidak hadir dengan izin', 'Sering mendapat teguran'])) {
                        $currentScore = 2;
                    } elseif (in_array($tingkat, ['Berat', 'Penampilan tidak sesuai Standar', 'Mangkir / Alpha', 'Komplain berat'])) {
                        $currentScore = 1;
                    }
                    if ($currentScore < $score) {
                        $score = $currentScore;
                    }
                }

                $scores[$ind] = $score;
                $totalScore += $score;
            }

            if (!$hasSchedule || $passedWorkingDays === 0) {
                $scores = array_fill_keys($indicators, null);
                $totalScore = null;
                $percentage = null;
            } else {
                $percentage = ($totalScore / 20) * 100;
            }

            $officerData = [
                'id_user' => $officer->id_user,
                'nama_lengkap' => $officer->nama_lengkap,
                'regu' => $officer->regu,
                'role' => $officer->role,
                'scores' => $scores,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'violations' => $violations,
                'total_hari_kerja' => $passedWorkingDays,
            ];

            $performanceData[] = $officerData;
        }

        return $performanceData;
    }

    private function parseBulanToNumber($bulanStr)
    {
        if (is_numeric($bulanStr)) {
            return (int)$bulanStr;
        }
        $bulanMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
        ];
        return $bulanMap[strtolower(trim($bulanStr))] ?? (int)date('n');
    }

    public static function getStartOfWeek1($tahun, $bulanNum)
    {
        $firstDayOfMonth = \Carbon\Carbon::createFromDate($tahun, $bulanNum, 1);
        $dayOfWeek = $firstDayOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        
        if (in_array($dayOfWeek, [1, 2, 7])) {
            return $firstDayOfMonth->copy()->previous(\Carbon\Carbon::SATURDAY);
        } elseif ($dayOfWeek == 6) {
            return $firstDayOfMonth->copy(); // It's exactly Saturday
        } else {
            return $firstDayOfMonth->copy()->next(\Carbon\Carbon::SATURDAY);
        }
    }

    private function autoGenerateLaporan($bulan, $tahun)
    {
        $bulanNum = str_pad($this->parseBulanToNumber($bulan), 2, '0', STR_PAD_LEFT);
        $jadwals = \App\Models\JadwalBulanan::where('bulan', $bulanNum)->where('tahun', $tahun)->get();
        if ($jadwals->isEmpty()) return;

        $anggotaIds = $jadwals->pluck('id_anggota')->unique();
        $regus = User::whereIn('id_user', $anggotaIds)->pluck('regu')->unique()->filter();

        $totalWeeks = 4; // Fix to 4 weeks per month for Laporan Mingguan

        foreach ($regus as $regu) {
            $danru = User::where('role', 'Danru')->where('regu', $regu)->where('status_aktif', 1)->first();
            if (!$danru) continue;

            for ($i = 1; $i <= $totalWeeks; $i++) {
                \App\Models\LaporanMingguan::firstOrCreate(
                    [
                        'regu' => $regu,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'minggu_ke' => $i
                    ],
                    [
                        'id_danru' => $danru->id_user,
                        'status_dokumen' => 'Draft',
                    ]
                );
            }
        }

        // Generate ONE LaporanBulanan for the whole month (Anggota)
        $chief = User::where('role', 'Chief')->first() ?? User::where('role', 'Admin')->first();
        if ($chief) {
            LaporanBulanan::firstOrCreate(
                [
                    'regu' => 'Semua',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'id_danru_pembuat' => $chief->id_user,
                    'status_dokumen' => 'Draft',
                ]
            );

            // Generate ONE LaporanBulanan for Danru evaluation
            LaporanBulanan::firstOrCreate(
                [
                    'regu' => 'Laporan_Danru',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'id_danru_pembuat' => $chief->id_user,
                    'status_dokumen' => 'Draft',
                ]
            );
        }
    }

    private function attachSignableStatus($laporanList, $type)
    {
        return $laporanList->map(function ($lap) use ($type) {
            $bulanNum = $this->parseBulanToNumber($lap->bulan);
            $endOfMonth = \Carbon\Carbon::createFromDate($lap->tahun, $bulanNum, 1)->endOfMonth();
            
            if ($type === 'bulanan') {
                $lap->is_signable = \Carbon\Carbon::now()->gt($endOfMonth);
                
                $unapprovedWeeklyCount = \App\Models\LaporanMingguan::where('bulan', $lap->bulan)
                    ->where('tahun', $lap->tahun)
                    ->where('status_dokumen', '!=', 'Approved')
                    ->count();
                $lap->all_weekly_approved = $unapprovedWeeklyCount === 0;
            } else {
                $startOfWeek1 = self::getStartOfWeek1($lap->tahun, $bulanNum);
                // endOfWeekDate is Friday at 23:59:59 of that specific week (each week is 7 days)
                $endOfWeekDate = $startOfWeek1->copy()->addDays(($lap->minggu_ke - 1) * 7 + 6)->endOfDay();
                $lap->is_signable = \Carbon\Carbon::now()->gt($endOfWeekDate);
            }
            return $lap;
        });
    }

    public static function getWeekNumberForDate($date = null)
    {
        $carbon = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        $tahun = $carbon->year;
        $bulanNum = str_pad($carbon->month, 2, '0', STR_PAD_LEFT);
        
        $startOfWeek1 = self::getStartOfWeek1($tahun, $bulanNum);
        
        if ($carbon->lt($startOfWeek1)) {
            return 1;
        }
        
        $diffDays = $startOfWeek1->diffInDays($carbon->copy()->startOfDay());
        $weekNum = (int) floor($diffDays / 7) + 1;
        
        if ($weekNum > 4) {
            $weekNum = 4;
        }
        if ($weekNum < 1) {
            $weekNum = 1;
        }
        
        return $weekNum;
    }

    public function mingguan(Request $request)
    {
        $user = Auth::user();
        
        $now = \Carbon\Carbon::now();
        $defaultBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $defaultBulan = $defaultBulanMap[$now->month];
        $defaultTahun = $now->year;
        $defaultMingguKe = self::getWeekNumberForDate($now);

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = (int)$request->input('tahun', $defaultTahun);
        $minggu_ke = (int)$request->input('minggu_ke', $defaultMingguKe);
        $filter_regu = $request->input('filter_regu');

        $this->autoGenerateLaporan($bulan, $tahun);

        $laporanMingguanQuery = \App\Models\LaporanMingguan::with('danru')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('minggu_ke', '<=', 4);
        
        if (strtolower(trim($user->role)) === 'danru') {
            $laporanMingguanQuery->where('regu', trim($user->regu));
        } else if ((strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') && $filter_regu) {
            $laporanMingguanQuery->where('regu', $filter_regu);
        }
        $laporanMingguan = $this->attachSignableStatus($laporanMingguanQuery->get(), 'mingguan');

        $performanceData = $this->getLaporanData($bulan, $tahun, $minggu_ke, $filter_regu);

        // Fetch list of distinct regu for the filter dropdown from regus table
        $reguList = \App\Models\Regu::orderBy('nama_regu', 'asc')->pluck('nama_regu')->values();

        return Inertia::render('Laporan/Mingguan', [
            'laporanMingguan' => $laporanMingguan,
            'performanceData' => $performanceData,
            'selectedBulan' => $bulan,
            'selectedTahun' => $tahun,
            'selectedMinggu' => $minggu_ke,
            'filterRegu' => $filter_regu,
            'reguList' => $reguList,
            'currentUser' => [
                'id_user' => $user->id_user,
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'regu' => $user->regu,
                'ttd_url' => $user->ttd_url,
            ],
        ]);
    }

    public function bulanan(Request $request)
    {
        $user = Auth::user();
        
        if (strtolower(trim($user->role)) === 'danru') {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat laporan bulanan.');
        }

        $now = \Carbon\Carbon::now();
        $defaultBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $defaultBulan = $defaultBulanMap[$now->month];
        $defaultTahun = $now->year;

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = (int)$request->input('tahun', $defaultTahun);
        $filter_regu = $request->input('filter_regu');

        $this->autoGenerateLaporan($bulan, $tahun);

        $laporanQuery = LaporanBulanan::with('danruPembuat')
            ->where('tahun', $tahun)
            ->whereIn('regu', ['Semua', 'Laporan_Danru']);
        $laporanBulanan = $this->attachSignableStatus($laporanQuery->get(), 'bulanan');
        $laporanBulanan = collect($laporanBulanan)->sortBy(function ($item) {
            return $this->parseBulanToNumber($item->bulan);
        })->values();

        $detailedMonthlyData = $this->getDetailedMonthlyData($bulan, $tahun, $filter_regu);

        // Fetch list of distinct regu for the filter dropdown from regus table
        $reguList = \App\Models\Regu::orderBy('nama_regu', 'asc')->pluck('nama_regu')->values();

        return Inertia::render('Laporan/Bulanan', [
            'laporanBulanan' => $laporanBulanan,
            'detailedMonthlyData' => $detailedMonthlyData,
            'selectedBulan' => $bulan,
            'selectedTahun' => $tahun,
            'filterRegu' => $filter_regu,
            'reguList' => $reguList,
            'currentUser' => [
                'id_user' => $user->id_user,
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'regu' => $user->regu,
                'ttd_url' => $user->ttd_url,
            ],
        ]);
    }

    public function sign(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string',
            'role' => 'required|in:Danru,Chief,Klien',
        ]);

        $report = LaporanBulanan::findOrFail($id);
        $role = $request->input('role');
        $signature = $request->input('signature');

        $useSaved = $request->input('use_saved');
        
        if ($useSaved && $request->user() && $request->user()->ttd_url) {
            $fileUrl = '/storage/' . $request->user()->ttd_url;
        } else {
            if (preg_match('/^data:image\/(\w+);base64,/', $signature, $type)) {
                $signatureData = substr($signature, strpos($signature, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    return redirect()->back()->with('error', 'Format gambar tidak valid');
                }

                $signatureData = str_replace(' ', '+', $signatureData);
                $signatureData = base64_decode($signatureData);

                if ($signatureData === false) {
                    return redirect()->back()->with('error', 'Gagal decode base64');
                }
            } else {
                return redirect()->back()->with('error', 'Format signature tidak valid');
            }

            $fileName = 'signature_' . $id . '_' . $role . '_' . time() . '.' . $type;
            $path = 'signatures/' . $fileName;

            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $signatureData);
            $fileUrl = '/storage/' . $path;
        }

        if ($role === 'Danru') {
            $report->ttd_danru_url = $fileUrl;
            $report->tgl_ttd_danru = now();
            $report->status_dokumen = 'Review_Chief';
        } elseif ($role === 'Chief') {
            $report->ttd_chief_url = $fileUrl;
            $report->tgl_ttd_chief = now();
            $report->status_dokumen = 'Review_Klien';
        } elseif ($role === 'Klien') {
            $report->ttd_klien_url = $fileUrl;
            $report->tgl_ttd_klien = now();
            $report->status_dokumen = 'Approved';
        }

        $report->save();
        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan!');
    }

    public function signMingguan(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string',
            'role' => 'required|in:Danru,Chief',
        ]);

        $report = \App\Models\LaporanMingguan::findOrFail($id);
        $role = $request->input('role');
        $signature = $request->input('signature');

        $useSaved = $request->input('use_saved');

        if ($useSaved && $request->user() && $request->user()->ttd_url) {
            $fileUrl = '/storage/' . $request->user()->ttd_url;
        } else {
            if (preg_match('/^data:image\/(\w+);base64,/', $signature, $type)) {
                $signatureData = substr($signature, strpos($signature, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    return redirect()->back()->with('error', 'Format gambar tidak valid');
                }

                $signatureData = str_replace(' ', '+', $signatureData);
                $signatureData = base64_decode($signatureData);

                if ($signatureData === false) {
                    return redirect()->back()->with('error', 'Gagal decode base64');
                }
            } else {
                return redirect()->back()->with('error', 'Format signature tidak valid');
            }

            $fileName = 'signature_mingguan_' . $id . '_' . $role . '_' . time() . '.' . $type;
            $path = 'signatures/' . $fileName;

            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $signatureData);
            $fileUrl = '/storage/' . $path;
        }

        if ($role === 'Danru') {
            $report->ttd_danru_url = $fileUrl;
            $report->tgl_ttd_danru = now();
            $report->status_dokumen = 'Review_Chief';
        } elseif ($role === 'Chief') {
            $report->ttd_chief_url = $fileUrl;
            $report->tgl_ttd_chief = now();
            $report->status_dokumen = 'Approved';
        }

        $report->save();
        return redirect()->back()->with('success', 'Tanda tangan Laporan Mingguan berhasil disimpan!');
    }

    public function exportLaporan(Request $request)
    {
        $type = $request->query('type', 'pdf');
        $minggu_ke = (int)$request->query('minggu_ke', 1);
        $bulan = $request->query('bulan', 'Juli');
        $tahun = (int)$request->query('tahun', 2026);
        $filter_regu = $request->query('filter_regu');

        $performanceData = $this->getLaporanData($bulan, $tahun, $minggu_ke, $filter_regu);
        
        $user = Auth::user();
        $laporanMingguanQuery = \App\Models\LaporanMingguan::with('danru')
            ->where('minggu_ke', $minggu_ke)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);
            
        if ($user && strtolower(trim($user->role)) === 'danru') {
            $laporanMingguanQuery->where('regu', trim($user->regu));
        } else if ($filter_regu) {
            $laporanMingguanQuery->where('regu', $filter_regu);
        }
        $laporanMingguan = $laporanMingguanQuery->first();

        if ($type === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LaporanExport($performanceData, $bulan, $tahun, $minggu_ke), "laporan_mingguan_{$minggu_ke}_{$bulan}_{$tahun}.xlsx");
        } else if ($type === 'pdf') {
            $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            $allRegus = collect($performanceData)->pluck('regu')->unique()->filter()->map(function($r) {
                return preg_replace('/^regu\s*/i', '', trim($r));
            })->values();
            if ($allRegus->count() > 0) {
                $reguDisplay = 'REGU ' . $allRegus->implode(', ');
            } else {
                $reguDisplay = isset($laporanMingguan) && $laporanMingguan->regu ? strtoupper($laporanMingguan->regu) : 'REGU 1';
            }
            $danruName = isset($laporanMingguan) && $laporanMingguan->danru ? $laporanMingguan->danru->nama_lengkap : '';
            $dateObj = \Carbon\Carbon::now();
            $tanggalRekap = $dateObj->format('d') . ' ' . strtoupper($bulanList[$dateObj->format('n') - 1]) . ' ' . $dateObj->format('Y');
            $bulanText = is_numeric($bulan) ? $bulanList[(int)$bulan-1] : $bulan;

            $actualData = array_values($performanceData ?? []);
            $chunks = array_chunk($actualData, 10);
            if (empty($chunks)) {
                $chunks = [[]];
            }
            $totalPages = count($chunks);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan', [
                'performanceData' => $performanceData,
                'laporanMingguan' => $laporanMingguan,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'minggu_ke' => $minggu_ke,
                'reguDisplay' => $reguDisplay,
                'danruName' => $danruName,
                'tanggalRekap' => $tanggalRekap,
                'bulanText' => $bulanText,
                'chunks' => $chunks,
                'totalPages' => $totalPages,
                'logoBase64' => config('pdf_logo.base64'),
            ])->setPaper('a4', 'landscape');
            return $pdf->stream("laporan_mingguan_{$minggu_ke}_{$bulan}_{$tahun}.pdf");
        }

        return redirect()->back()->with('error', 'Tipe export tidak valid');
    }

    private function getDanruMonthlyData($bulan, $tahun)
    {
        $bulanNum = str_pad($this->parseBulanToNumber($bulan), 2, '0', STR_PAD_LEFT);

        $danrus = User::where('role', 'Danru')->where('status_aktif', 1)->get();
        $perPerson = [];
        $totalPengawasan = 0; $countPengawasan = 0;
        $totalPelaporan = 0; $countPelaporan = 0;
        $totalPenyelesaian = 0; $countPenyelesaian = 0;

        foreach ($danrus as $danru) {
            $scores = [
                'Pengawasan Personel' => 5,
                'Ketepatan Pelaporan' => 5,
                'Penyelesaian Masalah' => 5,
            ];

            $pelanggarans = \App\Models\CatatanPelanggaran::where('id_anggota', $danru->id_user)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->get();

            foreach ($pelanggarans as $p) {
                if (isset($scores[$p->kategori_indikator])) {
                    if (preg_match('/Skor (\d+)/', $p->tingkat_penilaian, $matches)) {
                        // Keep the lowest score if multiple infractions
                        $score = (int)$matches[1];
                        if ($score < $scores[$p->kategori_indikator]) {
                            $scores[$p->kategori_indikator] = $score;
                        }
                    }
                }
            }

            $avg_score = array_sum($scores) / count($scores);
            $avg_percentage = ($avg_score / 5) * 100;

            $perPerson[] = [
                'id_user' => $danru->id_user,
                'nama_lengkap' => $danru->nama_lengkap,
                'role' => $danru->role,
                'regu' => $danru->regu,
                'indicator_scores' => $scores,
                'avg_score' => $avg_score,
                'avg_percentage' => $avg_percentage,
            ];

            $totalPengawasan += $scores['Pengawasan Personel']; $countPengawasan++;
            $totalPelaporan += $scores['Ketepatan Pelaporan']; $countPelaporan++;
            $totalPenyelesaian += $scores['Penyelesaian Masalah']; $countPenyelesaian++;
        }

        $perIndicator = [];
        $indicators = [
            ['name' => 'Pengawasan Personel', 'total' => $totalPengawasan, 'count' => $countPengawasan],
            ['name' => 'Ketepatan Pelaporan', 'total' => $totalPelaporan, 'count' => $countPelaporan],
            ['name' => 'Penyelesaian Masalah', 'total' => $totalPenyelesaian, 'count' => $countPenyelesaian],
        ];

        foreach ($indicators as $ind) {
            $avgScore = $ind['count'] > 0 ? $ind['total'] / $ind['count'] : 5;
            $achievedPercentage = ($avgScore / 5) * 100;
            
            $keterangan = $achievedPercentage >= 100 ? 'Tercapai' : 'Tidak tercapai';

            $perIndicator[] = [
                'indikator' => $ind['name'],
                'target' => 100,
                'achieved_percentage' => $achievedPercentage,
                'keterangan' => $keterangan,
            ];
        }

        return [
            'perPerson' => $perPerson,
            'perIndicator' => $perIndicator,
        ];
    }

    public function exportLaporanBulanan(Request $request)
    {
        $type = $request->query('type', 'pdf');
        $jenis = $request->query('jenis', 'anggota'); // anggota atau danru
        $bulan = $request->query('bulan', 'Juli');
        $tahun = (int)$request->query('tahun', 2026);
        $filter_regu = $request->query('filter_regu');

        $user = Auth::user();

        if ($jenis === 'danru') {
            $detailedMonthlyData = $this->getDanruMonthlyData($bulan, $tahun);
            $laporanQuery = LaporanBulanan::with('danruPembuat')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->where('regu', 'Laporan_Danru');
        } else {
            $detailedMonthlyData = $this->getDetailedMonthlyData($bulan, $tahun, $filter_regu);
            $laporanQuery = LaporanBulanan::with('danruPembuat')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->where('regu', 'Semua');
        }
        
        $laporanBulanan = $laporanQuery->first();

        if ($type === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LaporanBulananWorkflowExport($tahun), "laporan_bulanan_{$bulan}_{$tahun}.xlsx");
        } else if ($type === 'pdf') {
            $bulanList = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            $dateObj = \Carbon\Carbon::now();
            $tanggalRekap = $dateObj->format('d') . ' ' . strtoupper($bulanList[$dateObj->format('n') - 1]) . ' ' . $dateObj->format('Y');
            $bulanText = is_numeric($bulan) ? $bulanList[(int)$bulan-1] : $bulan;

            $totalWeeks = isset($detailedMonthlyData['totalWeeks']) ? $detailedMonthlyData['totalWeeks'] : 4;
            $personnelList = collect($detailedMonthlyData['perPerson'] ?? []);
            
            $rowsPerPage = $jenis === 'danru' ? 3 : 20;
            $chunks = $personnelList->chunk($rowsPerPage);
            if ($chunks->isEmpty()) {
                $chunks = collect([collect([])]);
            }
            $totalPages = $chunks->count();

            $grandTotalScore = 0;
            $validPersonCount = 0;
            foreach($personnelList as $person) {
                if ($person['avg_percentage'] !== null) {
                    $grandTotalScore += $person['avg_percentage'];
                    $validPersonCount++;
                }
            }

            $viewName = $jenis === 'danru' ? 'exports.laporan_bulanan_danru' : 'exports.laporan_bulanan';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, [
                'detailedMonthlyData' => $detailedMonthlyData,
                'laporanBulananObj' => $laporanBulanan,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'tanggalRekap' => $tanggalRekap,
                'bulanText' => $bulanText,
                'totalWeeks' => $totalWeeks,
                'chunks' => $chunks,
                'totalPages' => $totalPages,
                'grandTotalScore' => $grandTotalScore,
                'validPersonCount' => $validPersonCount,
                'rowsPerPage' => $rowsPerPage,
                'logoBase64' => config('pdf_logo.base64'),
            ])->setPaper('a4', 'landscape');
            return $pdf->stream("laporan_bulanan_{$jenis}_{$bulan}_{$tahun}.pdf");
        }

        return redirect()->back()->with('error', 'Tipe export tidak valid');
    }
}
