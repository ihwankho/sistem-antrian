<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'nama' => 'Administrator',
            'nama_pengguna' => 'adminpn',
            'password' => Hash::make('admin123'), // text biasa (tidak di-hash)
            'role' => 1,
            'id_loket' => null,
            'foto' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
