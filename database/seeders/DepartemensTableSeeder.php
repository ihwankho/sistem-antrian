<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartemensTableSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua loket sesuai urutan ID
        $lokets = DB::table('lokets')->orderBy('id')->get();

        // Daftar nama departemen sesuai urutan
        $departemens = [
            'Hukum',
            'Perdata',
            'Pidana',
            // tambahkan sesuai jumlah loket
        ];

        foreach ($lokets as $index => $loket) {
            DB::table('departemens')->insert([
                'nama_departemen' => $departemens[$index] ?? 'Departemen ' . $loket->nama_loket,
                'id_loket' => $loket->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
