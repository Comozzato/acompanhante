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
        Schema::table('feed', function (Blueprint $table) {
            //
            // $table->dropColumn('titulo');
            // $table->dropColumn('tipo');
            $table->dropColumn('midia_path_master');
            $table->dropColumn('midia_path_thumbnail1');
            $table->dropColumn('midia_path_thumbnail2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed', function (Blueprint $table) {
            //
        });
    }
};
