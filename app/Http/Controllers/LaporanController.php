<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LaporanBulanan;
use App\Models\LaporanMingguan;
use App\Models\CatatanPelanggaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $bulan = $request->input('bulan', 'Juli');
        $tahun = (int)$request->input('tahun', 2026);
        $minggu_ke = (int)$request->input('minggu_ke', 1);

        // 1. Fetch Monthly Reports (Laporan Bulanan)
        $laporanQuery = LaporanBulanan::with('danruPembuat');
        
        // If Danru, only show reports of their regu
        if ($user->role === 'Danru') {
            $laporanQuery->where('regu', $user->regu);
        }
        
        $laporanBulanan = $laporanQuery->get();

        // 2. Fetch Security Officers (Anggota)
        $anggotaQuery = User::where('role', 'Anggota')->where('status_aktif', 1);
        
        if ($user->role === 'Danru') {
            $anggotaQuery->where('regu', $user->regu);
        }
        
        $anggotaList = $anggotaQuery->get();

        // 3. Load Weekly Shift Schedules (Laporan Mingguan) for grouping
        $weeklyShifts = LaporanMingguan::where('minggu_ke', $minggu_ke)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get();

        // 4. Calculate weekly scores & group by shift
        $performanceData = [];
        $shiftGroups = [
            'Shift Pagi' => [],
            'Shift Siang' => [],
            'Shift Malam' => [],
        ];

        $indicators = ['Kedisiplinan', 'Kehadiran', 'Kerapihan', 'Komunikasi'];

        foreach ($anggotaList as $officer) {
            // Fetch violations for this week
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

                // Reduction Logic:
                // 1 berat -> 1
                // 1 sedang -> 2
                // 2 ringan -> 3
                // 1 ringan -> 4
                // 0 -> 5
                $score = 5;
                if ($berat >= 1) {
                    $score = 1;
                } elseif ($sedang >= 1) {
                    $score = 2;
                } elseif ($ringan >= 2) {
                    $score = 3;
                } elseif ($ringan == 1) {
                    $score = 4;
                }

                $scores[$ind] = $score;
                $totalScore += $score;
            }

            $percentage = ($totalScore / 20) * 100;

            // Determine shift for the week
            // Search in LaporanMingguan first
            $weeklyRecord = $weeklyShifts->firstWhere('regu', $officer->regu);
            if ($weeklyRecord) {
                $shift = $weeklyRecord->shift_berjalan;
            } else {
                // Fallback rotation mock based on officer ID and week number
                $rotation = ($officer->id_user + $minggu_ke) % 3;
                $shift = ['Shift Pagi', 'Shift Siang', 'Shift Malam'][$rotation];
            }

            $officerData = [
                'id_user' => $officer->id_user,
                'nama_lengkap' => $officer->nama_lengkap,
                'regu' => $officer->regu,
                'shift' => $shift,
                'scores' => $scores,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'violations' => $violations,
            ];

            $performanceData[] = $officerData;
            $shiftGroups[$shift][] = $officerData;
        }

        return Inertia::render('Laporan/DashboardLaporan', [
            'laporanBulanan' => $laporanBulanan,
            'performanceData' => $performanceData,
            'shiftGroups' => $shiftGroups,
            'selectedBulan' => $bulan,
            'selectedTahun' => $tahun,
            'selectedMinggu' => $minggu_ke,
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
            'signature' => 'required|string', // Base64 data url
            'role' => 'required|in:Danru,Chief,Klien',
        ]);

        $report = LaporanBulanan::findOrFail($id);
        $role = $request->input('role');
        $signature = $request->input('signature');

        if ($role === 'Danru') {
            $report->ttd_danru_url = $signature;
            $report->status_dokumen = 'Review_Chief';
        } elseif ($role === 'Chief') {
            $report->ttd_chief_url = $signature;
            $report->status_dokumen = 'Review_Klien';
        } elseif ($role === 'Klien') {
            $report->ttd_klien_url = $signature;
            $report->status_dokumen = 'Approved';
        }

        $report->save();

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan!');
    }
}
