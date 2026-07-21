<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regu extends Model
{
    protected $table = 'regus';
    protected $primaryKey = 'id_regu';

    protected $fillable = [
        'nama_regu',
    ];
}
