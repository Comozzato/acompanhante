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
        Schema::create('midia', function (Blueprint $table) {
            $table->id();
             $table->foreignId('feed_id')
              ->constrained('feed')
              ->onDelete('cascade');
            $table->string('midia');  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midia');
    }
};
