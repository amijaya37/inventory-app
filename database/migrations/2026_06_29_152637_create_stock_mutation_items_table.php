<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mutation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_mutation_id')->constrained('stock_mutations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->string('serial_no', 100)->nullable();
            $table->string('condition_status', 30)->default('layak_pakai');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_id']);
            $table->index(['stock_mutation_id', 'item_id'], 'mutation_item_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutation_items');
    }
};
