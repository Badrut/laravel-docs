<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kop_surats', function (Blueprint $table) {
            $table->id();
            $table->string('name');               // Nama identifikasi, misal: "Kop SMKN 1 Karanganyar 2026"
            $table->enum('type', ['sekolah', 'kontraktor']); // Jenis kop surat
            $table->string('image_path');          // Path ke file gambar
            $table->timestamps();
        });

        // Tambah FK di projects
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('kop_school_id')->nullable()->after('kop_contractor');
            $table->unsignedBigInteger('kop_contractor_id')->nullable()->after('kop_school_id');
            $table->foreign('kop_school_id')->references('id')->on('kop_surats')->onDelete('set null');
            $table->foreign('kop_contractor_id')->references('id')->on('kop_surats')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['kop_school_id']);
            $table->dropForeign(['kop_contractor_id']);
            $table->dropColumn(['kop_school_id', 'kop_contractor_id']);
        });

        Schema::dropIfExists('kop_surats');
    }
};
