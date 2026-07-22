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
        $anggotaQuery = User::where('role', 'Anggota')->where('status_aktif', 1);
        if (strtolower(trim($user->role)) === 'danru') {
            $anggotaQuery->where('regu', trim($user->regu));
        } else if ((strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') && $filter_regu) {
            $anggotaQuery->where('regu', $filter_regu);
        }
        $anggotaList = $anggotaQuery->get();
        $indicators = ['Kedisiplinan', 'Kehadiran', 'Kerapihan', 'Komunikasi'];
        $targets = [
            'Kedisiplinan' => 90,
            'Kehadiran' => 100,
            'Kerapihan' => 100,
            'Komunikasi' => 85,
        ];

        $perPerson = [];
        $indicatorTotals = [
            'Kedisiplinan' => ['total' => 0, 'max' => 0],
            'Kehadiran' => ['total' => 0, 'max' => 0],
            'Kerapihan' => ['total' => 0, 'max' => 0],
            'Komunikasi' => ['total' => 0, 'max' => 0],
        ];

        $allViolations = CatatanPelanggaran::whereIn('id_anggota', $anggotaList->pluck('id_user'))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        $bulanNum = str_pad($this->parseBulanToNumber($bulan), 2, '0', STR_PAD_LEFT);
        $firstDayOfMonth = \Carbon\Carbon::createFromDate($tahun, $bulanNum, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $firstDay = $firstDayOfMonth->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $totalWeeks = (int) ceil(($daysInMonth + $firstDay - 1) / 7);

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
                $startDayOfWeek = ($m - 1) * 7 - $firstDay + 2;
                $endDayOfWeek = $m * 7 - $firstDay + 1;
                $startDayOfWeek = max(1, $startDayOfWeek);
                $endDayOfWeek = min($daysInMonth, $endDayOfWeek);

                $passedWorkingDays = 0;
                $hasSchedule = false;
                for ($d = $startDayOfWeek; $d <= $endDayOfWeek; $d++) {
                    $shiftDay = isset($jadwalHarian[$d]) ? $jadwalHarian[$d] : null;
                    if ($shiftDay) {
                        $hasSchedule = true;
                        if ($shiftDay !== 'Libur') {
                            $dateOfD = clone $firstDayOfMonth;
                            $dateOfD->day($d)->startOfDay();
                            if (\Carbon\Carbon::now()->gte($dateOfD)) {
                                $passedWorkingDays++;
                            }
                        }
                    }
                }

                $totalWeekScore = 0;
                foreach ($indicators as $ind) {
                    $violationsForInd = $allViolations->where('id_anggota', $officer->id_user)
                        ->where('minggu_ke', $m)
                        ->where('kategori_indikator', $ind);
                    
                    $ringan = $violationsForInd->where('tingkat_pelanggaran', 'Ringan')->count();
                    $sedang = $violationsForInd->where('tingkat_pelanggaran', 'Sedang')->count();
                    $berat = $violationsForInd->where('tingkat_pelanggaran', 'Berat')->count();

                    $score = 5;
                    if ($berat >= 1) $score = 1;
                    elseif ($sedang >= 1) $score = 2;
                    elseif ($ringan >= 2) $score = 3;
                    elseif ($ringan == 1) $score = 4;

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
            $isTercapai = $achievedPercentage >= $targets[$ind];

            $perIndicator[] = [
                'indikator' => $ind,
                'target' => $targets[$ind],
                'achieved_percentage' => $achievedPercentage,
                'keterangan' => $isTercapai ? 'Tercapai' : 'Tidak Tercapai',
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
        $anggotaQuery = User::where('role', 'Anggota')->where('status_aktif', 1);
        
        if (strtolower(trim($user->role)) === 'danru') {
            $anggotaQuery->where('regu', trim($user->regu));
        } else if ((strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') && $filter_regu) {
            $anggotaQuery->where('regu', $filter_regu);
        }
        
        $anggotaList = $anggotaQuery->get();
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
        $indicators = ['Kedisiplinan', 'Kehadiran', 'Kerapihan', 'Komunikasi'];
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
                $violationsForInd = $violations->where('kategori_indikator', $ind);
                
                $ringan = $violationsForInd->where('tingkat_pelanggaran', 'Ringan')->count();
                $sedang = $violationsForInd->where('tingkat_pelanggaran', 'Sedang')->count();
                $berat = $violationsForInd->where('tingkat_pelanggaran', 'Berat')->count();

                $score = 5;
                if ($berat >= 1) $score = 1;
                elseif ($sedang >= 1) $score = 2;
                elseif ($ringan >= 2) $score = 3;
                elseif ($ringan == 1) $score = 4;

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
                'scores' => $scores,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'violations' => $violations,
            ];

            $performanceData[] = $officerData;
        }

        return $performanceData;
    }

    private function parseBulanToNumber($bulanStr)
    {
        $bulanMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
        ];
        return $bulanMap[strtolower($bulanStr)] ?? 1;
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

            LaporanBulanan::firstOrCreate(
                [
                    'regu' => $regu,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ],
                [
                    'id_danru_pembuat' => $danru->id_user,
                    'status_dokumen' => 'Draft',
                ]
            );

            for ($i = 1; $i <= $totalWeeks; $i++) {
                \App\Models\LaporanMingguan::firstOrCreate(
                    [
                        'regu' => $regu,
                        'bulan' => $bulan,
                        'tahun' => $tahun,
                        'minggu_ke' => $i,
                    ],
                    [
                        'id_danru' => $danru->id_user,
                    ]
                );
            }
        }
    }

    private function attachSignableStatus($laporanList, $type)
    {
        return $laporanList->map(function ($lap) use ($type) {
            $bulanNum = $this->parseBulanToNumber($lap->bulan);
            $endOfMonth = \Carbon\Carbon::createFromDate($lap->tahun, $bulanNum, 1)->endOfMonth();
            
            if ($type === 'bulanan') {
                $lap->is_signable = \Carbon\Carbon::now()->gt($endOfMonth);
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
            ],
        ]);
    }

    public function bulanan(Request $request)
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

        $bulan = $request->input('bulan', $defaultBulan);
        $tahun = (int)$request->input('tahun', $defaultTahun);
        $filter_regu = $request->input('filter_regu');

        $this->autoGenerateLaporan($bulan, $tahun);

        $laporanQuery = LaporanBulanan::with('danruPembuat')
            ->where('tahun', $tahun);
        if (strtolower(trim($user->role)) === 'danru') {
            $laporanQuery->where('regu', trim($user->regu));
        } else if ((strtolower(trim($user->role)) === 'chief' || strtolower(trim($user->role)) === 'admin') && $filter_regu) {
            $laporanQuery->where('regu', $filter_regu);
        }
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
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan', [
                'performanceData' => $performanceData,
                'laporanMingguan' => $laporanMingguan,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'minggu_ke' => $minggu_ke,
            ])->setPaper('a4', 'landscape');
            return $pdf->stream("laporan_mingguan_{$minggu_ke}_{$bulan}_{$tahun}.pdf");
        }

        return redirect()->back()->with('error', 'Tipe export tidak valid');
    }

    public function exportLaporanBulanan(Request $request)
    {
        $type = $request->query('type', 'pdf');
        $bulan = $request->query('bulan', 'Juli');
        $tahun = (int)$request->query('tahun', 2026);
        $filter_regu = $request->query('filter_regu');

        $detailedMonthlyData = $this->getDetailedMonthlyData($bulan, $tahun, $filter_regu);
        
        $user = Auth::user();
        $laporanQuery = LaporanBulanan::with('danruPembuat')
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);
            
        if ($user && strtolower(trim($user->role)) === 'danru') {
            $laporanQuery->where('regu', trim($user->regu));
        } else if ($filter_regu) {
            $laporanQuery->where('regu', $filter_regu);
        }
        
        $laporanBulanan = $laporanQuery->first();

        if ($type === 'excel') {
            // Kita akan menggunakan view export ini juga untuk sementara jika LaporanBulananWorkflowExport belum terupdate.
            // Biarkan as-is atau ubah sesuai kebutuhan jika excel belum support 2 table.
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\LaporanBulananWorkflowExport($tahun), "laporan_bulanan_{$bulan}_{$tahun}.xlsx");
        } else if ($type === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan_bulanan', [
                'detailedMonthlyData' => $detailedMonthlyData,
                'laporanBulananObj' => $laporanBulanan,
                'bulan' => $bulan,
                'tahun' => $tahun,
            ])->setPaper('a4', 'landscape');
            return $pdf->stream("laporan_bulanan_{$bulan}_{$tahun}.pdf");
        }

        return redirect()->back()->with('error', 'Tipe export tidak valid');
    }
}
