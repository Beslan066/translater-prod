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
        Schema::table('sentences', function (Blueprint $table) {
            // Кто подтвердил предложение (корректор)
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // Когда было подтверждено
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sentences', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn(['reviewed_by', 'reviewed_at']);
        });
    }
};
