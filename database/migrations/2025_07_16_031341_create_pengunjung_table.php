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
        Schema::create('pengunjungs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengunjung');
            $table->string('nik', 16)->unique();
            $table->string('no_hp', 15);
            $table->enum('jenis_kelamin',['laki-laki', 'perempuan']);
            $table->text('alamat');
            $table->string('foto_ktp')->nullable();
            $table->string('foto_wajah')->nullable();
            $table->unsignedBigInteger('id_departemen');
            $table->timestamps();

            $table->foreign('id_departemen')->references('id')->on('departemens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengunjungs');
    }
};
