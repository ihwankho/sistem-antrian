<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Antrian extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_antrian',
        'status_antrian',
        'id_pengunjung',
        'id_pelayanan',
    ];
}
