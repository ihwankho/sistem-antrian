<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'nama_pengguna',
        'password',
        'role',
        'id_loket',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function loket()
    {
        return $this->belongsTo(Loket::class, 'id_loket');
    }
    public function departemen()
    {
        return $this->hasOneThrough(
            Departemen::class,
            Loket::class,
            'id',        // id di lokets
            'id_loket',  // foreign key di departemens
            'id_loket',  // foreign key di users
            'id'         // id di lokets
        );
    }
}
