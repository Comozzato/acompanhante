<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feed', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['imagem', 'video', 'texto'])->index(); // Tipo de mídia
            $table->string('titulo')->nullable();
            $table->text('conteudo')->nullable(); // Texto principal do post
            $table->string('midia_path')->nullable(); // Caminho para imagem/vídeo (quando aplicável)
            $table->boolean('ativo')->default(true); // Visível no feed?
            $table->timestamp('publicado_em')->nullable(); // Agendamento opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed');
    }
};
