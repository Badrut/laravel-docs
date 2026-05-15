<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            
            // Main Project Info
            $table->string('project_name');
            $table->date('start_date')->nullable(); // Ditambahkan untuk fix bug 1970
            $table->date('end_date')->nullable();   // Ditambahkan
            $table->string('target_days')->nullable();
            $table->string('implementation_period')->nullable(); 
            $table->string('attendance_period')->nullable();     

            // Contractor Data
            $table->string('contractor_name')->nullable();
            $table->text('contractor_address')->nullable();
            $table->string('person_in_charge')->nullable();

            // Officials Data (Snapshot)
            $table->string('principal_name')->nullable();
            $table->string('principal_nip')->nullable();
            $table->string('facilities_head_name')->nullable(); // Waka Sarpras
            $table->string('facilities_head_nip')->nullable();
            $table->string('treasurer_name')->nullable(); // Bendahara
            $table->string('treasurer_nip')->nullable();
            $table->string('admin_head_name')->nullable(); // Kasubag TU
            $table->string('admin_head_nip')->nullable();

            // Document Dates & Numbers
            $table->date('budget_date')->nullable(); // Tanggal RAB
            
            $table->date('invitation_date')->nullable();
            $table->string('invitation_number')->nullable();
            
            $table->date('contractor_date')->nullable();
            $table->string('contractor_number')->nullable();
            
            $table->date('work_order_date')->nullable(); // Tanggal SPK
            $table->string('work_order_number')->nullable(); // Nomor SPK
            
            $table->date('handover_report_date')->nullable(); // BA Penyerahan
            $table->string('handover_report_number')->nullable();
            
            $table->date('payment_report_date')->nullable(); // BA Pembayaran
            $table->string('payment_report_number')->nullable();
            
            $table->date('receipt_date')->nullable(); // Kwitansi
            $table->string('receipt_number')->nullable();
            $table->string('receipt_receiver')->nullable();
            
            // Taxes (Diubah jadi unsignedBigInteger)
            $table->unsignedBigInteger('tax_vat')->default(0); // PPN
            $table->unsignedBigInteger('tax_pph21')->default(0);
            $table->unsignedBigInteger('tax_pph22')->default(0);
            $table->unsignedBigInteger('tax_pph23')->default(0);
            $table->unsignedBigInteger('tax_pph_final')->default(0);
            $table->string('kop_school')->nullable();
            $table->string('kop_contractor')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};