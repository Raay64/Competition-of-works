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
        Schema::table('submission_comments', function (Blueprint $table) {
            // Добавляем поле для отметки "полезный комментарий"
            $table->boolean('is_helpful')->default(false)->after('body');

            // Добавляем поле для ответов на комментарии (самореференс)
            $table->foreignId('parent_id')
                ->nullable()
                ->after('is_helpful')
                ->constrained('submission_comments')
                ->onDelete('cascade');

            // Индексы для ускорения поиска
            $table->index('is_helpful');
            $table->index('parent_id');
            $table->index(['submission_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submission_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['is_helpful', 'parent_id']);
        });
    }
};
