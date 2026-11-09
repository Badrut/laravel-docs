<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');              // Nama Perusahaan
            $table->string('person_in_charge')->nullable(); // Pemilik Usaha / Penanggung Jawab
            $table->text('address')->nullable();          // Alamat Kontraktor
            $table->string('phone')->nullable();          // No. Telepon (opsional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
