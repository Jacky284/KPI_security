<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatatanHarian extends Model
{
    use HasFactory;

    protected $table = 'catatan_harians';
    protected $primaryKey = 'id_catatan';

    protected $fillable = [
        'id_danru',
        'id_anggota',
        'tanggal',
        'jam_kejadian',
        'shift',
        'pos_lokasi',
        'minggu_ke',
        'bulan',
        'tahun',
        'indikator',
        'deskripsi',
        'arahan',
        'status_tindak_lanjut',
        'keterangan',
    ];

    public function danru()
    {
        return $this->belongsTo(User::class, 'id_danru', 'id_user');
    }

    public function anggota()
    {
        return $this->belongsTo(User::class, 'id_anggota', 'id_user');
    }
}
