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
        Schema::table('catatan_harians', function (Blueprint $table) {
            $table->string('pos_lokasi')->nullable()->after('shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catatan_harians', function (Blueprint $table) {
            $table->dropColumn('pos_lokasi');
        });
    }
};
