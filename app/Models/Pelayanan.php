<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelayanan extends Model
{
    protected $fillable = ['nama_layanan', 'id_departemen'];

    public function departemen()
    {
        return $this->belongsTo(Departemen::class, 'id_departemen');
    }
}
