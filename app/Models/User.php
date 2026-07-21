<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama_lengkap',
        'role',
        'regu',
        'status_aktif',
        'username',
        'password',
        'tempat_lahir',
        'tanggal_lahir',
        'foto_profil',
        'sisa_cuti',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function catatanPelanggaran(): HasMany
    {
        return $this->hasMany(CatatanPelanggaran::class, 'id_anggota', 'id_user');
    }

    public function penilaianPelanggaran(): HasMany
    {
        return $this->hasMany(CatatanPelanggaran::class, 'id_danru_penilai', 'id_user');
    }

    public function laporanBulanan(): HasMany
    {
        return $this->hasMany(LaporanBulanan::class, 'id_danru_pembuat', 'id_user');
    }

    public function laporanMingguan(): HasMany
    {
        return $this->hasMany(LaporanMingguan::class, 'id_danru', 'id_user');
    }
}
