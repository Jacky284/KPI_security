<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CatatanPelanggaran;
use App\Models\JadwalBulanan;
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
        
        // If Chief, show Danru instead of Anggota
        if ($user->role === 'Chief') {
            $query = User::where('role', 'Danru');
        } else {
            $query = User::where('role', 'Anggota');
            
            // If Danru, only show their own regu members
            if ($user->role === 'Danru') {
                $query->where('regu', trim($user->regu));
            }
        }

        $anggota = $query->orderBy('nama_lengkap', 'asc')->get();

        $now = Carbon::now();
        $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $currentYear = $now->year;

        // Fetch schedules for all members this month
        $jadwals = JadwalBulanan::where('bulan', $currentMonth)
            ->where('tahun', $currentYear)
            ->get()
            ->keyBy('id_anggota');

        $anggotaData = $anggota->map(function($user) use ($now, $jadwals) {
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
                'regu' => $user->regu,
                'foto_profil' => $user->foto_profil,
                'usia' => $usia,
                'hari_kerja' => $hariKerja,
            ];
        });

        return Inertia::render('Anggota/Index', [
            'anggota' => $anggotaData,
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
            ->orderBy('tanggal_kejadian', 'desc')
            ->get();

        // 2. Get Jadwal Minggu Ini
        // We will fetch the whole month's schedule and the frontend can parse "Minggu Ini"
        $jadwalBulanIni = JadwalBulanan::where('id_anggota', $id)
            ->where('bulan', str_pad($currentMonth, 2, '0', STR_PAD_LEFT))
            ->where('tahun', $currentYear)
            ->first();

        // 3. Generate 3-Month Performance Data (Siang vs Malam)
        // Basic Logic: Start with 100 base score per shift category, subtract based on violations.
        // A simple way to approximate this without knowing every single schedule day is:
        // We look at the last 3 months.
        
        $trendData = [];
        for ($i = 2; $i >= 0; $i--) {
            $targetDate = $now->copy()->subMonths($i);
            $tMonth = $targetDate->month;
            $tYear = $targetDate->year;
            $tBulanStr = $defaultBulanMap[$tMonth];
            
            // Get violations for this month
            $violationsThisMonth = CatatanPelanggaran::where('id_anggota', $id)
                ->where('bulan', $tBulanStr)
                ->where('tahun', $tYear)
                ->get();
            
            // Need to determine if violation was during Siang or Malam.
            // We fetch the schedule for that month to check what shift they had on tanggal_kejadian.
            $jadwalThatMonth = JadwalBulanan::where('id_anggota', $id)
                ->where('bulan', str_pad($tMonth, 2, '0', STR_PAD_LEFT))
                ->where('tahun', $tYear)
                ->first();
                
            $scoreSiang = 100;
            $scoreMalam = 100;

            foreach ($violationsThisMonth as $v) {
                // Deduction points (Ringan: -5, Sedang: -10, Berat: -25)
                $deduction = 5;
                if ($v->tingkat_pelanggaran === 'Sedang') $deduction = 10;
                if ($v->tingkat_pelanggaran === 'Berat') $deduction = 25;

                // Determine shift from schedule
                $shift = 'Siang'; // default
                if ($jadwalThatMonth && $jadwalThatMonth->jadwal_harian) {
                    $day = Carbon::parse($v->tanggal_kejadian)->format('j');
                    $dailyShift = $jadwalThatMonth->jadwal_harian[$day] ?? 'Libur';
                    if (strpos(strtolower($dailyShift), 'malam') !== false) {
                        $shift = 'Malam';
                    } else if (strpos(strtolower($dailyShift), 'pagi') !== false || strpos(strtolower($dailyShift), 'siang') !== false) {
                        $shift = 'Siang';
                    }
                }

                if ($shift === 'Malam') {
                    $scoreMalam -= $deduction;
                } else {
                    $scoreSiang -= $deduction;
                }
            }

            // Ensure scores don't drop below 0
            $scoreSiang = max(0, $scoreSiang);
            $scoreMalam = max(0, $scoreMalam);

            $trendData[] = [
                'name' => substr($tBulanStr, 0, 3), // e.g. "Mei", "Jun", "Jul"
                'Siang' => $scoreSiang,
                'Malam' => $scoreMalam,
            ];
        }

        return Inertia::render('Anggota/Detail', [
            'anggota' => $anggota,
            'riwayatPelanggaran' => $riwayatPelanggaran,
            'jadwalBulanIni' => $jadwalBulanIni,
            'trendData' => $trendData,
        ]);
    }
}
