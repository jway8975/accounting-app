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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['monthly', 'yearly']); // 月报 or 年报
            $table->string('period'); // 2025-02 or 2025
            $table->text('content'); // AI 生成的报表内容
            $table->decimal('total_income', 10, 2)->default(0); // 总收入
            $table->decimal('total_expense', 10, 2)->default(0); // 总支出
            $table->json('category_breakdown')->nullable(); // 分类支出（JSON 格式）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
