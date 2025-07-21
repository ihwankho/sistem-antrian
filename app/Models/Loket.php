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
}
