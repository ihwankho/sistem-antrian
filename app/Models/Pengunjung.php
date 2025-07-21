<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengunjung extends Model
{
    protected $table = 'pengunjungs';
    protected $fillable = [
        'nama_pengunjung',
        'nik',
        'no_hp',
        'jenis_kelamin',
        'alamat',
        'foto_ktp',
        'foto_wajah',
        'id_departemen'
    ];

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }
}
