<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoketsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('lokets')->insert([
            [
                'nama_loket' => 'Hukum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_loket' => 'Perdata',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_loket' => 'Pidana',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
