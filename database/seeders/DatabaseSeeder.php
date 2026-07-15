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
        // 1. Seed Admin User
        DB::table('users')->insert([
            'nama_lengkap' => 'Administrator',
            'role' => 'Admin',
            'regu' => null,
            'status_aktif' => 1,
            'username' => 'admin',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Users
        // Danru, Chief, Klien
        $danruId = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Budi Santoso (Danru)',
            'role' => 'Danru',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'danru',
            'password' => Hash::make('password'),
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Anggota officers
        $anggota1 = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Agus Saputra',
            'role' => 'Anggota',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'agus',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $anggota2 = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Rudi Hermawan',
            'role' => 'Anggota',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'rudi',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $anggota3 = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Dedi Wijaya',
            'role' => 'Anggota',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'dedi',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $anggota4 = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Eko Prasetyo',
            'role' => 'Anggota',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'eko',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $anggota5 = DB::table('users')->insertGetId([
            'nama_lengkap' => 'Hendra Setiawan',
            'role' => 'Anggota',
            'regu' => 'Regu 1',
            'status_aktif' => 1,
            'username' => 'hendra',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Seed Violations (catatan_pelanggaran)
        // Agus Saputra (anggota1) -> 0 violations -> Level 5 (Sangat Baik)

        // Rudi Hermawan (anggota2) -> 1 Ringan violation -> Level 4 (Baik)
        DB::table('catatan_pelanggaran')->insert([
            'id_anggota' => $anggota2,
            'id_danru_penilai' => $danruId,
            'tanggal_kejadian' => '2026-07-02',
            'minggu_ke' => 1,
            'bulan' => 'Juli',
            'tahun' => 2026,
            'kategori_indikator' => 'Kerapihan',
            'tingkat_pelanggaran' => 'Ringan',
            'deskripsi_kejadian' => 'Baju seragam tidak disetrika dengan rapi saat apel pagi.',
            'created_at' => now(),
        ]);

        // Dedi Wijaya (anggota3) -> 2 Ringan violations -> Level 3 (Cukup)
        DB::table('catatan_pelanggaran')->insert([
            [
                'id_anggota' => $anggota3,
                'id_danru_penilai' => $danruId,
                'tanggal_kejadian' => '2026-07-03',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'kategori_indikator' => 'Kedisiplinan',
                'tingkat_pelanggaran' => 'Ringan',
                'deskripsi_kejadian' => 'Terlambat 5 menit masuk pos jaga.',
                'created_at' => now(),
            ],
            [
                'id_anggota' => $anggota3,
                'id_danru_penilai' => $danruId,
                'tanggal_kejadian' => '2026-07-10',
                'minggu_ke' => 2,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'kategori_indikator' => 'Komunikasi',
                'tingkat_pelanggaran' => 'Ringan',
                'deskripsi_kejadian' => 'Lupa melaporkan kondisi cuaca via HT pada pukul 23:00.',
                'created_at' => now(),
            ]
        ]);

        // Eko Prasetyo (anggota4) -> 1 Sedang violation -> Level 2 (Kurang)
        DB::table('catatan_pelanggaran')->insert([
            'id_anggota' => $anggota4,
            'id_danru_penilai' => $danruId,
            'tanggal_kejadian' => '2026-07-05',
            'minggu_ke' => 1,
            'bulan' => 'Juli',
            'tahun' => 2026,
            'kategori_indikator' => 'Kehadiran',
            'tingkat_pelanggaran' => 'Sedang',
            'deskripsi_kejadian' => 'Meninggalkan pos tanpa izin selama 15 menit.',
            'created_at' => now(),
        ]);

        // Hendra Setiawan (anggota5) -> 1 Berat violation -> Level 1 (Sangat Kurang)
        DB::table('catatan_pelanggaran')->insert([
            'id_anggota' => $anggota5,
            'id_danru_penilai' => $danruId,
            'tanggal_kejadian' => '2026-07-08',
            'minggu_ke' => 2,
            'bulan' => 'Juli',
            'tahun' => 2026,
            'kategori_indikator' => 'Kedisiplinan',
            'tingkat_pelanggaran' => 'Berat',
            'deskripsi_kejadian' => 'Tertidur pulas di pos utama saat shift malam berlangsung.',
            'created_at' => now(),
        ]);

        // 4. Seed Monthly Reports (laporan_bulanan)
        DB::table('laporan_bulanan')->insert([
            [
                'id_danru_pembuat' => $danruId,
                'regu' => 'Regu 1',
                'bulan' => 'Juli',
                'tahun' => 2026,
                'status_dokumen' => 'Draft',
                'ttd_danru_url' => null,
                'ttd_chief_url' => null,
                'ttd_klien_url' => null,
                'file_pdf_url' => null,
                'created_at' => now(),
            ],
            [
                'id_danru_pembuat' => $danruId,
                'regu' => 'Regu 1',
                'bulan' => 'Juni',
                'tahun' => 2026,
                'status_dokumen' => 'Approved',
                'ttd_danru_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAASUlEQVRoge3PsQ0AIAwEsbz/0DygoxIp8m4GqrrkVgMAAAAAAIC/yG93Q6qqaunl7vYdAAAAAPghT/vL7wAAAAAAAAAAAAAAAAAAALgG824AEd8E63QAAAAASUVORK5CYII=',
                'ttd_chief_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAASUlEQVRoge3PsQ0AIAwEsbz/0DygoxIp8m4GqrrkVgMAAAAAAIC/yG93Q6qqaunl7vYdAAAAAPghT/vL7wAAAAAAAAAAAAAAAAAAALgG824AEd8E63QAAAAASUVORK5CYII=',
                'ttd_klien_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAASUlEQVRoge3PsQ0AIAwEsbz/0DygoxIp8m4GqrrkVgMAAAAAAIC/yG93Q6qqaunl7vYdAAAAAPghT/vL7wAAAAAAAAAAAAAAAAAAALgG824AEd8E63QAAAAASUVORK5CYII=',
                'file_pdf_url' => '/downloads/reports/juni-2026.pdf',
                'created_at' => now()->subMonth(),
            ]
        ]);

        // 5. Seed Weekly Reports (laporan_mingguan)
        DB::table('laporan_mingguan')->insert([
            [
                'id_danru' => $danruId,
                'regu' => 'Regu 1',
                'shift_berjalan' => 'Shift Pagi',
                'minggu_ke' => 1,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'file_pdf_url' => null,
                'created_at' => now(),
            ],
            [
                'id_danru' => $danruId,
                'regu' => 'Regu 1',
                'shift_berjalan' => 'Shift Malam',
                'minggu_ke' => 2,
                'bulan' => 'Juli',
                'tahun' => 2026,
                'file_pdf_url' => null,
                'created_at' => now(),
            ]
        ]);
    }
}
