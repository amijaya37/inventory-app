<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_issue_id')->constrained('goods_issues')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->string('serial_no')->nullable();
            $table->string('condition_status', 30)->default('good');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_id']);
            $table->index(['condition_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_issue_items');
    }
};
