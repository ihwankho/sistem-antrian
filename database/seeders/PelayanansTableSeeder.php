<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelayanansTableSeeder extends Seeder
{
    public function run()
    {
        // Ambil id departemen berdasarkan nama
        $hukum = DB::table('departemens')->where('nama_departemen', 'Hukum')->first();
        $perdata = DB::table('departemens')->where('nama_departemen', 'Perdata')->first();
        $pidana = DB::table('departemens')->where('nama_departemen', 'Pidana')->first();

        // Daftar layanan per departemen
        $layananHukum = [
            'Permohonan Waarmaking',
            'Permohonan Surat Keterangan tidak tersangkut perkara pidana',
            'Permohonan Surat Keterangan tidak sedang dicabut hak pilihnya',
            'Permohonan pendaftaran surat kuasa',
            'Permohonan Legalisasi surat',
            'Permohonan Informasi sesuai dengan SK KMA 2-144',
            'Penanganan Pengaduan/SIWAS MARI',
            'Permohonan surat izin melaksanakan penelitian dan riset',
            'Layanan-layanan lain yang berhubungan dengan pelayanan Kepaniteraan Hukum',
        ];

        $layananPerdata = [
            'Pendaftaran perkara Gugatan biasa',
            'Pendaftaran perkara Gugatan Sederhana',
            'Pendaftaran Verzet atas putusan Verstek',
            'Pendaftaran perkara Perlawanan/Bantahan',
            'Pendaftaran perkara Permohonan',
            'Pendaftaran permohonan Banding',
            'Pendaftaran permohonan Kasasi',
            'Pendaftaran permohonan Peninjauan Kembali',
            'Permohonan dan Pengambilan Sisa Panjar biaya perkara',
            'Permohonan dan Pengambilan Turunan/Salinan Putusan',
            'Pendaftaran permohonan Eksekusi',
            'Permohonan pengambilan uang Eksekusi',
            'Pendaftaran permohonan Konsinyasi',
            'Permohonan pengambilan uang Konsinyasi',
            'Permohonan pencabutan gugatan, permohonan Banding, Kasasi, Peninjauan Kembali, Eksekusi, dan Konsinyasi',
            'Permohonan Pendaftaran Keberatan Putusan KPPU',
            'Permohonan Pendaftaran Keberatan Putusan BPSK',
            'Layanan-layanan lain yang berhubungan dengan perkara',
        ];

        $layananPidana = [
            'Menerima pelimpahan berkas perkara pidana biasa, Perikanan Singkat, Ringan dan Cepat/ lalu lintas dari Penuntut Umum/Penyidik',
            'Menerima pendaftaran permohonan praperadilan',
            'Menerima permohonan perlawanan Banding, Kasasi, dan Peninjauan Kembali dan Grasi',
            'Menerima permohonan pencabutan perlawanan Banding, Kasasi, dan Peninjauan Kembali dan Grasi',
            'Menerima permohonan izin/persetujuan penyitaan dan menyerahkan izin/persetujuan penyitaan yang sudah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi',
            'Menerima permohonan izin persetujuan pemusnahan Barang bukti dan/atau pelelangan Barang bukti',
            'Menerima permohonan penetapan perpanjangan penahanan yang sudah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi',
            'Menerima permohonan pembantaran dan menyerahkan persetujuan pembantaran yang sudah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi',
            'Menerima permohonan izin besuk dan menyerahkan pemberian izin besuk',
            'Menerima permohonan dan menyerahkan izin berobat bagi terdakwa yang telah ditanda tangani oleh Ketua Pengadilan Negeri Banyuwangi',
            'Layanan-layanan lain yang berhubungan dengan proses dan penyelesaian perkara pidana kekhususan',
        ];

        // Insert layanan Hukum
        if ($hukum) {
            foreach ($layananHukum as $layanan) {
                DB::table('pelayanans')->insert([
                    'nama_layanan' => $layanan,
                    'id_departemen' => $hukum->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Insert layanan Perdata
        if ($perdata) {
            foreach ($layananPerdata as $layanan) {
                DB::table('pelayanans')->insert([
                    'nama_layanan' => $layanan,
                    'id_departemen' => $perdata->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Insert layanan Pidana
        if ($pidana) {
            foreach ($layananPidana as $layanan) {
                DB::table('pelayanans')->insert([
                    'nama_layanan' => $layanan,
                    'id_departemen' => $pidana->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
