<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departemen extends Model
{
    protected $fillable = [
        'id',
        'nama_departemen',
        'id_loket',
    ];
    public function loket()
    {
        return $this->belongsTo(Loket::class, 'id_loket');
    }
}
