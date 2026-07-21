<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalBulanan extends Model
{
    protected $table = 'jadwal_bulanans';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'id_anggota',
        'id_danru_pembuat',
        'bulan',
        'tahun',
        'jadwal_harian',
    ];

    protected $casts = [
        'jadwal_harian' => 'array',
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota', 'id_user');
    }

    public function danruPembuat()
    {
        return $this->belongsTo(User::class, 'id_danru_pembuat', 'id_user');
    }
}
