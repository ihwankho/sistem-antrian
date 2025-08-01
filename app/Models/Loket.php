<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loket extends Model
{
    use HasFactory;

    protected $fillable = ['nama_loket'];

    // (Opsional) Relasi ke User
    public function users()
    {
        return $this->hasMany(User::class, 'id_loket');
    }

    public function departemens()
    {
        return $this->hasMany(Departemen::class, 'id_loket');
    }

    public function pelayanans()
    {
        return $this->hasManyThrough(
            Pelayanan::class,
            Departemen::class,
            'id_loket',        // foreign key di departemens
            'id_departemen',   // foreign key di pelayanans
            'id',              // primary key di lokets
            'id'               // primary key di departemens
        );
    }
}
