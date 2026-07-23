<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $dummyTtd = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAASUlEQVRoge3PsQ0AIAwEsbz/0DygoxIp8m4GqrrkVgMAAAAAAIC/yG93Q6qqaunl7vYdAAAAAPghT/vL7wAAAAAAAAAAAAAAAAAAALgG824AEd8E63QAAAAASUVShortcut';

        // 0. Seed Regus (Initial active regus)
        DB::table('regus')->insert([
            ['nama_regu' => 'Regu 1', 'created_at' => now(), 'updated_at' => now()],
            ['nama_regu' => 'Regu 2', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 1. Seed Admin User
        DB::table('users')->insert([
            'nama_lengkap' => 'Administrator',
            'role' => 'Admin',
            'regu' => null,
            'status_aktif' => 1,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1990-05-15',
            'sisa_cuti' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Danru (Umur 24 - 32 tahun), Chief (Umur 30 - 35 tahun), Klien
        $danru1Id = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Budi Santoso (Danru Regu 1)',
            'role' => 'Danru',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'danru',
            'password' => Hash::make('password'),
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1995-08-20', // Umur 30 (24-32)
            'sisa_cuti' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $danru2Id = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Suryadi (Danru Regu 2)',
            'role' => 'Danru',
            'regu' => 'Regu 2',
            'status_aktif' => 1,
            'username' => 'danru2',
            'password' => Hash::make('password'),
            'tempat_lahir' => 'Semarang',
            'tanggal_lahir' => '1997-03-10', // Umur 29 (24-32)
            'sisa_cuti' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $chiefId = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Hendrawan (Chief)',
            'role' => 'Chief',
            'regu' => null,
            'status_aktif' => 1,
            'username' => 'chief',
            'password' => Hash::make('password'),
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1992-11-25', // Umur 33 (30-35)
            'sisa_cuti' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $klienId = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Bapak Asep (Klien)',
            'role' => 'Klien',
            'regu' => null,
            'status_aktif' => 1,
            'username' => 'klien',
            'password' => Hash::make('password'),
            'tempat_lahir' => 'Yogyakarta',
            'tanggal_lahir' => '1985-04-05',
            'sisa_cuti' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Anggota officers (Umur 19 - 25 tahun -> Lahir 2001 - 2007)
        // Regu 1 Anggota officers (5 officers)
        $regu1Anggotas = [
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Agus Saputra',
                'role' => 'Anggota',
                'regu' => 'Regu 1',
                'status_aktif' => 1,
                'username' => 'agus',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Bogor',
                'tanggal_lahir' => '2004-06-12', // Umur 22 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Rudi Hermawan',
                'role' => 'Anggota',
                'regu' => 'Regu 1',
                'status_aktif' => 1,
                'username' => 'rudi',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Bekasi',
                'tanggal_lahir' => '2002-09-18', // Umur 23 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Dedi Wijaya',
                'role' => 'Anggota',
                'regu' => 'Regu 1',
                'status_aktif' => 1,
                'username' => 'dedi',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Tangerang',
                'tanggal_lahir' => '2005-01-30', // Umur 21 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Eko Prasetyo',
                'role' => 'Anggota',
                'regu' => 'Regu 1',
                'status_aktif' => 1,
                'username' => 'eko',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Depok',
                'tanggal_lahir' => '2003-11-08', // Umur 22 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Hendra Setiawan',
                'role' => 'Anggota',
                'regu' => 'Regu 1',
                'status_aktif' => 1,
                'username' => 'hendra',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Sukabumi',
                'tanggal_lahir' => '2006-04-22', // Umur 20 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
        ];

        // Regu 2 Anggota officers (4 officers)
        $regu2Anggotas = [
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Jeni Mudaya',
                'role' => 'Anggota',
                'regu' => 'Regu 2',
                'status_aktif' => 1,
                'username' => 'jeni',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Malang',
                'tanggal_lahir' => '2004-02-14', // Umur 22 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Sukimay Ngawi',
                'role' => 'Anggota',
                'regu' => 'Regu 2',
                'status_aktif' => 1,
                'username' => 'sukimay',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Solo',
                'tanggal_lahir' => '2003-07-07', // Umur 23 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Ami Suritem',
                'role' => 'Anggota',
                'regu' => 'Regu 2',
                'status_aktif' => 1,
                'username' => 'ami',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Cirebon',
                'tanggal_lahir' => '2005-12-19', // Umur 20 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
            DB::table('users')->insertGetId([
                'nama_lengkap' => 'Bambang Tri',
                'role' => 'Anggota',
                'regu' => 'Regu 2',
                'status_aktif' => 1,
                'username' => 'bambang',
                'password' => Hash::make('password'),
                'tempat_lahir' => 'Magelang',
                'tanggal_lahir' => '2001-10-05', // Umur 24 (19-25)
                'sisa_cuti' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]),
        ];

        // 3. Seed Monthly Schedules with exact requested distribution:
        // Regu 1 (5 officers): Every day exactly 2 Pagi, 2 Malam, 1 Libur
        // Regu 2 (4 officers): Every day exactly 2 Pagi, 1 Malam, 1 Libur
        $months = ['05', '06', '07'];

        $poolRegu1 = ['Pagi', 'Pagi', 'Malam', 'Malam', 'Libur'];
        $poolRegu2 = ['Pagi', 'Pagi', 'Malam', 'Libur'];

        foreach ($months as $mStr) {
            $daysInMonth = $mStr === '06' ? 30 : 31;

            // Generate daily schedules for Regu 1
            $schedulesRegu1 = [];
            foreach ($regu1Anggotas as $idx => $anggotaId) {
                $schedulesRegu1[$anggotaId] = [];
            }
            for ($d = 1; $d <= $daysInMonth; $d++) {
                foreach ($regu1Anggotas as $idx => $anggotaId) {
                    $shift = $poolRegu1[($idx + $d - 1) % 5];
                    $schedulesRegu1[$anggotaId][(string)$d] = $shift;
                }
            }
            foreach ($regu1Anggotas as $anggotaId) {
                DB::table('jadwal_bulanans')->insert([
                    'id_anggota' => $anggotaId,
                    'id_danru_pembuat' => $danru1Id,
                    'bulan' => $mStr,
                    'tahun' => 2026,
                    'jadwal_harian' => json_encode($schedulesRegu1[$anggotaId]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Generate daily schedules for Regu 2
            $schedulesRegu2 = [];
            foreach ($regu2Anggotas as $idx => $anggotaId) {
                $schedulesRegu2[$anggotaId] = [];
            }
            for ($d = 1; $d <= $daysInMonth; $d++) {
                foreach ($regu2Anggotas as $idx => $anggotaId) {
                    $shift = $poolRegu2[($idx + $d - 1) % 4];
                    $schedulesRegu2[$anggotaId][(string)$d] = $shift;
                }
            }
            foreach ($regu2Anggotas as $anggotaId) {
                DB::table('jadwal_bulanans')->insert([
                    'id_anggota' => $anggotaId,
                    'id_danru_pembuat' => $danru2Id,
                    'bulan' => $mStr,
                    'tahun' => 2026,
                    'jadwal_harian' => json_encode($schedulesRegu2[$anggotaId]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Seed Violations (catatan_pelanggaran)
        DB::table('catatan_pelanggaran')->insert([
            [
                'id_anggota' => $regu1Anggotas[1], // Rudi
                'id_penilai' => $danru1Id,
                'tanggal_penilaian' => '2026-07-02',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'kategori_indikator' => 'Kerapihan',
                'tingkat_penilaian' => 'Ringan',
                'deskripsi_penilaian' => 'Baju seragam tidak disetrika dengan rapi saat apel pagi.',
                'status_tindak_lanjut' => 'Sudah',
                'created_at' => now(),
            ],
            [
                'id_anggota' => $regu1Anggotas[2], // Dedi
                'id_penilai' => $danru1Id,
                'tanggal_penilaian' => '2026-07-03',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'kategori_indikator' => 'Kedisiplinan',
                'tingkat_penilaian' => 'Ringan',
                'deskripsi_penilaian' => 'Terlambat 5 menit masuk pos jaga.',
                'status_tindak_lanjut' => 'Sudah',
                'created_at' => now(),
            ],
            [
                'id_anggota' => $regu2Anggotas[0], // Jeni
                'id_penilai' => $danru2Id,
                'tanggal_penilaian' => '2026-07-04',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'kategori_indikator' => 'Komunikasi',
                'tingkat_penilaian' => 'Sedang',
                'deskripsi_penilaian' => 'Tidak memberikan laporan patroli jam 02:00.',
                'status_tindak_lanjut' => 'Sudah',
                'created_at' => now(),
            ],
        ]);

        // 5. Seed Monthly Reports (laporan_bulanan)
        DB::table('laporan_bulanan')->insert([
            [
                'id_danru_pembuat' => $danru1Id,
                'regu' => 'Regu 1',
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now(),
                'ttd_klien_url' => $dummyTtd,
                'tgl_ttd_klien' => now(),
                'file_pdf_url' => '/downloads/reports/juli-2026-regu1.pdf',
                'created_at' => now(),
            ],
            [
                'id_danru_pembuat' => $danru2Id,
                'regu' => 'Regu 2',
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now(),
                'ttd_klien_url' => $dummyTtd,
                'tgl_ttd_klien' => now(),
                'file_pdf_url' => '/downloads/reports/juli-2026-regu2.pdf',
                'created_at' => now(),
            ],
            [
                'id_danru_pembuat' => $danru1Id,
                'regu' => 'Regu 1',
                'bulan' => 'Juni',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now()->subMonth(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now()->subMonth(),
                'ttd_klien_url' => $dummyTtd,
                'tgl_ttd_klien' => now()->subMonth(),
                'file_pdf_url' => '/downloads/reports/juni-2026.pdf',
                'created_at' => now()->subMonth(),
            ]
        ]);

        // 6. Seed Weekly Reports (laporan_mingguan)
        DB::table('laporan_mingguan')->insert([
            [
                'id_danru' => $danru1Id,
                'regu' => 'Regu 1',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now(),
                'file_pdf_url' => null,
                'created_at' => now(),
            ],
            [
                'id_danru' => $danru1Id,
                'regu' => 'Regu 1',
                'minggu_ke' => 2,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now(),
                'file_pdf_url' => null,
                'created_at' => now(),
            ],
            [
                'id_danru' => $danru2Id,
                'regu' => 'Regu 2',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now(),
                'file_pdf_url' => null,
                'created_at' => now(),
            ],
            [
                'id_danru' => $danru2Id,
                'regu' => 'Regu 2',
                'minggu_ke' => 2,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => $dummyTtd,
                'tgl_ttd_danru' => now(),
                'ttd_chief_url' => $dummyTtd,
                'tgl_ttd_chief' => now(),
                'file_pdf_url' => null,
                'created_at' => now(),
            ]
        ]);
    }
}
