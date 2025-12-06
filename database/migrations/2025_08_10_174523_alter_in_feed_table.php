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
            $table->string('publish')->default('Pendente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed', function (Blueprint $table) {
            //
            $table->dropColumn('publish');
        });
    }
};
