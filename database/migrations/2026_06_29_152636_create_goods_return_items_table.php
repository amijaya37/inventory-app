<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_return_id')->constrained('goods_returns')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->string('serial_no')->nullable();
            $table->string('condition_status', 30);
            $table->string('final_action', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['goods_return_id', 'item_id']);
            $table->index(['condition_status', 'final_action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_return_items');
    }
};
