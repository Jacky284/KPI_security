<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CatatanPelanggaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AnggotaController extends Controller
{
    private function checkAccess()
    {
        $role = Auth::user()?->role;
        if (!in_array($role, ['Admin', 'Chief', 'Danru'])) {
            abort(403, 'Akses ditolak.');
        }
    }

    public function index()
    {
        $this->checkAccess();

        $user = Auth::user();
        
        $userRole = strtolower(trim($user->role));
        
        if ($userRole === 'chief' || $userRole === 'admin') {
            $query = User::whereIn('role', ['Danru', 'Anggota'])->where('status_aktif', 1);
        } else if ($userRole === 'danru') {
            $query = User::where('role', 'Anggota')->where('status_aktif', 1)->where('regu', trim($user->regu));
        } else {
            $query = User::where('role', 'Anggota')->where('status_aktif', 1);
        }

        $anggota = $query->orderBy('regu', 'asc')->orderBy('nama_lengkap', 'asc')->get();

        $now = Carbon::now();
        $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $currentYear = $now->year;

        $anggotaData = $anggota->map(function($user) use ($now) {
            // Calculate Usia
            $usia = '-';
            if ($user->tanggal_lahir) {
                $usia = Carbon::parse($user->tanggal_lahir)->age . ' Tahun';
            }

            // Calculate Hari Kerja (total days since joining)
            $hariKerja = 0;
            if ($user->created_at) {
                $hariKerja = Carbon::parse($user->created_at)->startOfDay()->diffInDays($now->copy()->startOfDay());
            }

            return [
                'id_user' => $user->id_user,
                'nama_lengkap' => $user->nama_lengkap,
                'role' => $user->role,
                'regu' => $user->regu,
                'foto_profil' => $user->foto_profil,
                'usia' => $usia,
                'hari_kerja' => $hariKerja,
            ];
        });

        $reguList = \App\Models\Regu::orderBy('nama_regu', 'asc')->pluck('nama_regu')->values();

        return Inertia::render('Anggota/Index', [
            'anggota' => $anggotaData,
            'reguList' => $reguList,
        ]);
    }

    public function show($id)
    {
        $this->checkAccess();

        $anggota = User::findOrFail($id);
        $user = Auth::user();

        // Danru can only view their own regu's members
        if ($user->role === 'Danru' && $anggota->regu !== $user->regu) {
            abort(403, 'Anda hanya dapat melihat profil anggota dari regu Anda sendiri.');
        }

        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $defaultBulanMap = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $currentBulanStr = $defaultBulanMap[$currentMonth];

        // 1. Get Riwayat Pelanggaran
        $riwayatPelanggaran = CatatanPelanggaran::with('danruPenilai')
            ->where('id_anggota', $id)
            ->orderBy('tanggal_penilaian', 'desc')
            ->get();

        // 2. Get Jadwal Minggu Ini
        // We will fetch the whole month's schedule and the frontend can parse "Minggu Ini"

        // 3. Generate 3-Month Performance Data (Per Indicator)
        $trendData = [];
        $reqBulan = request('awal_bulan');
        $reqTahun = request('tahun', $currentYear);
        
        $monthsArr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $defaultIndicators = $anggota->role === 'Danru'
            ? ['Pengawasan Personel', 'Ketepatan Pelaporan', 'Penyelesaian Masalah']
            : ['Disiplin Kerja', 'Kehadiran', 'Penampilan & Kerapihan', 'Komunikasi & Pelayanan'];
        
        if ($reqBulan && in_array($reqBulan, $monthsArr)) {
            $awalBulanNum = array_search($reqBulan, $monthsArr) + 1;
            $startDate = Carbon::createFromDate($reqTahun, $awalBulanNum, 1);
        } else {
            $startDate = $now->copy()->subMonths(2)->startOfMonth();
        }

        for ($i = 0; $i < 3; $i++) {
            $targetDate = clone $startDate;
            $targetDate->addMonths($i);
            
            $tMonth = $targetDate->month;
            $tYear = $targetDate->year;
            $tBulanStr = $defaultBulanMap[$tMonth];
            
            $violationsThisMonth = CatatanPelanggaran::where('id_anggota', $id)
                ->where('bulan', $tBulanStr)
                ->where('tahun', $tYear)
                ->get();
            

                
            $monthData = [
                'name' => substr($tBulanStr, 0, 3)
            ];
            
            foreach ($defaultIndicators as $ind) {
                $deduction = 0;
                foreach ($violationsThisMonth as $v) {
                    if ($v->kategori_indikator === $ind) {
                        $tingkat = $v->tingkat_penilaian;
                        $penalty = 0;
                        if (in_array($tingkat, ['Ringan 1 kali', 'Kurang rapi 1 kali', 'Terlambat 1 kali', 'Komplain ringan'])) {
                            $penalty = 20;
                        } elseif (in_array($tingkat, ['Ringan 2 kali', 'Kurang rapi 2 kali', 'Terlambat 2 kali', 'Komplain sedang'])) {
                            $penalty = 40;
                        } elseif (in_array($tingkat, ['Sedang', 'Seragam tidak lengkap', 'Tidak hadir dengan izin', 'Sering mendapat teguran'])) {
                            $penalty = 60;
                        } elseif (in_array($tingkat, ['Berat', 'Penampilan tidak sesuai Standar', 'Mangkir / Alpha', 'Komplain berat'])) {
                            $penalty = 80;
                        } elseif (preg_match('/Skor (\d+)/', $tingkat, $matches)) {
                            $penalty = (5 - (int)$matches[1]) * 20;
                        }
                        $deduction += $penalty;
                    }
                }
                
                $hasSchedule = true;
                
                $monthData[$ind] = $hasSchedule ? max(0, 100 - $deduction) : 0;
            }
            
            $trendData[] = $monthData;
        }

        // 4. Calculate Indicator Breakdown (Last 3 Months Aggregated)
        $indicatorDeductionPagi = [];
        $indicatorDeductionMalam = [];
        $indicatorHasPagi = [];
        $indicatorHasMalam = [];

        $defaultIndicators = $anggota->role === 'Danru'
            ? ['Pengawasan Personel', 'Ketepatan Pelaporan', 'Penyelesaian Masalah']
            : ['Kedisiplinan', 'Kehadiran', 'Kerapihan', 'Komunikasi'];
        foreach ($defaultIndicators as $ind) {
            $indicatorDeductionPagi[$ind] = 0;
            $indicatorDeductionMalam[$ind] = 0;
            $indicatorHasPagi[$ind] = false;
            $indicatorHasMalam[$ind] = false;
        }

        // We assume indicators are present for both shifts since schedule is not automatic anymore.
        foreach ($defaultIndicators as $ind) {
            $indicatorHasPagi[$ind] = true;
            $indicatorHasMalam[$ind] = true;
        }

        $startOf3Months = $startDate->copy()->startOfMonth();
        $endOf3Months = $startDate->copy()->addMonths(2)->endOfMonth();

        $violations3Months = CatatanPelanggaran::where('id_anggota', $id)
            ->whereBetween('tanggal_penilaian', [$startOf3Months->format('Y-m-d'), $endOf3Months->format('Y-m-d')])
            ->get();

        foreach ($violations3Months as $v) {
            $deduction = 5;
            if ($v->tingkat_penilaian === 'Sedang') $deduction = 10;
            if ($v->tingkat_penilaian === 'Berat') $deduction = 25;

            $kategori = $v->kategori_indikator ?: 'Lainnya';
            if (!isset($indicatorDeductionPagi[$kategori])) {
                $indicatorDeductionPagi[$kategori] = 0;
                $indicatorDeductionMalam[$kategori] = 0;
                $indicatorHasPagi[$kategori] = true;
                $indicatorHasMalam[$kategori] = true;
            }

            $shift = $v->shift ?? 'Libur';
            $lowerD = strtolower((string)$shift);
            if (strpos($lowerD, 'malam') !== false) {
                $shift = 'Malam';
            } elseif (strpos($lowerD, 'pagi') !== false || strpos($lowerD, 'siang') !== false) {
                $shift = 'Pagi';
            } else {
                $shift = 'Pagi'; // Default to pagi if non-shift
            }

            if ($shift === 'Malam') {
                $indicatorDeductionMalam[$kategori] += $deduction;
            } else {
                $indicatorDeductionPagi[$kategori] += $deduction;
            }
        }

        $indicatorTrendData = [];
        foreach ($indicatorDeductionPagi as $ind => $pDeduction) {
            $mDeduction = $indicatorDeductionMalam[$ind];
            $hPagi = $indicatorHasPagi[$ind];
            $hMalam = $indicatorHasMalam[$ind];

            $indicatorTrendData[] = [
                'name' => $ind,
                'Pagi' => $hPagi ? max(0, 100 - $pDeduction) : 0,
                'Malam' => $hMalam ? max(0, 100 - $mDeduction) : 0,
            ];
        }

        $jadwalBulanIni = [];

        return Inertia::render('Anggota/Detail', [
            'anggota' => $anggota,
            'riwayatPelanggaran' => $riwayatPelanggaran,
            'jadwalBulanIni' => $jadwalBulanIni,
            'trendData' => $trendData,
            'indicatorTrendData' => $indicatorTrendData,
            'filters' => [
                'awal_bulan' => $defaultBulanMap[$startDate->month],
                'tahun' => $startDate->year
            ]
        ]);
    }

    public function pindahRegu(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user?->role, ['Admin', 'Chief'])) {
            abort(403, 'Akses ditolak. Hanya Admin atau Chief yang dapat memindahkan regu.');
        }

        $request->validate([
            'regu' => 'required|string',
        ]);

        $anggota = User::findOrFail($id);
        $anggota->update([
            'regu' => $request->regu
        ]);

        return redirect()->back()->with('success', 'Anggota berhasil dipindahkan ke ' . $request->regu);
    }
    public function saveSignature(Request $request, $id)
    {
        $user = Auth::user();
        
        // Ensure user is updating their own signature, or admin/chief
        if ($user->id_user != $id && !in_array($user->role, ['Admin', 'Chief'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'signature' => 'required|string'
        ]);

        $anggota = User::findOrFail($id);

        try {
            $imageParts = explode(';base64,', $request->signature);
            if (count($imageParts) !== 2) {
                return redirect()->back()->with('error', 'Format tanda tangan tidak valid.');
            }
            
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageType = $imageTypeAux[1];
            $imageBase64 = base64_decode($imageParts[1]);
            
            $fileName = 'ttd_' . $anggota->id_user . '_' . time() . '.' . $imageType;
            $path = 'ttd_pribadi/' . $fileName;

            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageBase64);

            // Update user
            $anggota->update([
                'ttd_url' => $path
            ]);

            return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan tanda tangan.');
        }
    }
}
