<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Memulai pembersihan database...\n";

Schema::disableForeignKeyConstraints();

// Wiping transaction tables
DB::table('catatan_harians')->truncate();
DB::table('catatan_pelanggaran')->truncate();
DB::table('laporan_bulanan')->truncate();
DB::table('laporan_mingguan')->truncate();
DB::table('jadwal_bulanans')->truncate();

// Keep Administrator but wipe others
$adminId = 1;
DB::table('users')->where('id_user', '!=', $adminId)->delete();
// We don't truncate users because we want to keep ID 1

// We also truncate Regus? The user says "seluruh databases, kecuali akun yang administrator ini". 
// Usually regu is master data. But let's just wipe it to be safe, they can recreate.
DB::table('regus')->truncate();

Schema::enableForeignKeyConstraints();

echo "Pembersihan selesai! Hanya akun Administrator yang tersisa.\n";
