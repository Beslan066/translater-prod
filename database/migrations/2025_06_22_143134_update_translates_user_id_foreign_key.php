<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('translates', function (Blueprint $table) {
            // 1. Удаляем старый foreign key
            $table->dropForeign(['user_id']);

            // 2. Делаем поле nullable (если еще не)
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 3. Добавляем новый foreign key с SET NULL
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL'); // При удалении пользователя user_id станет NULL
        });
    }

    public function down()
    {
        Schema::table('translates', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            // Возвращаем обратно (без onDelete)
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }
};
