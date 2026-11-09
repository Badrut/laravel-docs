<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'contractor_name',
                'contractor_address',
                'person_in_charge',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('contractor_name')->nullable()->after('contractor_id');
            $table->text('contractor_address')->nullable()->after('contractor_name');
            $table->string('person_in_charge')->nullable()->after('contractor_address');
        });
    }
};
