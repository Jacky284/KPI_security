<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\JadwalBulanan;
use App\Models\CatatanPelanggaran;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // Wipe old dummy data we created earlier today
        CatatanPelanggaran::where('deskripsi_penilaian', 'Pelanggaran dummy generik')->delete();
        JadwalBulanan::whereIn('bulan', [5, 6, 7, '05', '06', '07'])->delete();

        $users = User::whereIn('role', ['Danru', 'Anggota'])->get();
        $year = 2026;
        $months = [5, 6, 7];
        $monthNames = [5 => 'Mei', 6 => 'Juni', 7 => 'Juli'];

        $anggotaTingkat = [
            "Disiplin Kerja" => ["Ringan 1 kali", "Ringan 2 kali", "Sedang", "Berat"],
            "Penampilan & Kerapihan" => ["Kurang rapi 1 kali", "Kurang rapi 2 kali", "Seragam tidak lengkap", "Penampilan tidak sesuai Standar"],
            "Kehadiran" => ["Terlambat 1 kali", "Terlambat 2 kali", "Tidak hadir dengan izin", "Mangkir / Alpha"],
            "Komunikasi & Pelayanan" => ["Komplain ringan", "Komplain sedang", "Sering mendapat teguran", "Komplain berat"],
        ];

        foreach ($users as $user) {
            $isDanru = $user->role === 'Danru';
            $indicators = $isDanru 
                ? ['Pengawasan Personel', 'Ketepatan Pelaporan', 'Penyelesaian Masalah']
                : ['Disiplin Kerja', 'Kehadiran', 'Penampilan & Kerapihan', 'Komunikasi & Pelayanan'];

            foreach ($months as $month) {
                // 1. Generate Jadwal
                $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
                $jadwalHarian = [];
                $workingDays = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $shift = collect(['Pagi', 'Malam', 'Libur', 'Pagi', 'Pagi', 'Malam'])->random();
                    $jadwalHarian[$d] = $shift;
                    if ($shift !== 'Libur') {
                        $workingDays[] = $d;
                    }
                }

                JadwalBulanan::updateOrCreate(
                    [
                        'id_anggota' => $user->id_user,
                        'bulan' => str_pad($month, 2, '0', STR_PAD_LEFT),
                        'tahun' => $year
                    ],
                    [
                        'jadwal_harian' => $jadwalHarian
                    ]
                );

                // 2. Generate Pelanggaran (0 to 3 per month) ONLY on working days
                $numViolations = rand(0, 3);
                for ($i = 0; $i < $numViolations; $i++) {
                    if (empty($workingDays)) break;
                    
                    $randDay = collect($workingDays)->random();
                    $tanggal = Carbon::create($year, $month, $randDay);
                    $mingguKe = ceil($tanggal->day / 7);
                    if ($mingguKe > 4) $mingguKe = 4;

                    $kategori = collect($indicators)->random();
                    $tingkat = $isDanru 
                        ? collect(["Skor 4", "Skor 3", "Skor 2", "Skor 1"])->random()
                        : collect($anggotaTingkat[$kategori])->random();

                    $idPenilai = 1;
                    if ($isDanru) {
                        $chief = User::where('role', 'Chief')->first();
                        if ($chief) $idPenilai = $chief->id_user;
                    } else {
                        $danru = User::where('role', 'Danru')->where('regu', $user->regu)->first();
                        if ($danru) $idPenilai = $danru->id_user;
                    }

                    CatatanPelanggaran::create([
                        'id_anggota' => $user->id_user,
                        'id_penilai' => $idPenilai,
                        'tanggal_penilaian' => $tanggal->format('Y-m-d'),
                        'minggu_ke' => $mingguKe,
                        'bulan' => $monthNames[$month],
                        'tahun' => $year,
                        'kategori_indikator' => $kategori,
                        'deskripsi_penilaian' => 'Pelanggaran dummy generik',
                        'tingkat_penilaian' => $tingkat,
                    ]);
                }
            }
        }
    }
}
