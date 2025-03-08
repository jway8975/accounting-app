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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();  // 自动递增ID
            $table->unsignedBigInteger('user_id')->index(); // 添加索引，提高查询速度
            $table->date('date');  // 交易日期
            $table->decimal('amount', 10, 2); // 收支金额
            $table->tinyInteger('type')->comment('1=income, 2=expense');
            $table->string('category')->nullable(); // 交易类别（如：餐饮、购物）
            $table->text('description')->nullable(); // 交易描述
            $table->timestamps(); // 记录创建/更新时间
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
