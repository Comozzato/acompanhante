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
        Schema::table('sessions', function (Blueprint $table) {

            $table->string('access_token')->nullable()->unique()->after('payload');
            $table->string('refresh_token')->nullable()->unique()->after('access_token');
            $table->timestamp('expires_at')->nullable()->after('refresh_token');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            //
            $table->dropColumn(['access_token', 'refresh_token', 'expires_at']);
        });
    }
};
