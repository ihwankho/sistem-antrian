<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Antrian extends Model
{
    protected $fillable = [
        'nomor_antrian',
        'status_antrian',
        'id_pengunjung',
        'id_pelayanan',
    ];

    // Relasi ke pengunjung
    public function pengunjung()
    {
        return $this->belongsTo(Pengunjung::class, 'id_pengunjung');
    }

    // Relasi ke pelayanan
    public function pelayanan()
    {
        return $this->belongsTo(Pelayanan::class, 'id_pelayanan');
    }
}
