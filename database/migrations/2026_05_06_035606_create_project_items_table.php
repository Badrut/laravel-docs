<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key to projects table
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Type of item: 'material' or 'labor'
            $table->enum('type', ['material', 'labor']); 
            
            $table->string('item_name'); 
            $table->unsignedInteger('quantity'); // Diubah
            $table->string('unit');      
            $table->unsignedBigInteger('unit_price'); // Diubah
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_items');
    }
};