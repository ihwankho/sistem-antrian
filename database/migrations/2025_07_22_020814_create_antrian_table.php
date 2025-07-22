<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('antrians', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor_antrian');
            $table->tinyInteger('status_antrian')->default(1)->comment('1=Diambil, 2=Dipanggil, 3=Selesai');
            $table->unsignedBigInteger('id_pengunjung');
            $table->unsignedBigInteger('id_pelayanan');
            $table->timestamps();
            $table->foreign('id_pengunjung')->references('id')->on('pengunjungs')->onDelete('cascade');
            $table->foreign('id_pelayanan')->references('id')->on('pelayanans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrians');
    }
};
