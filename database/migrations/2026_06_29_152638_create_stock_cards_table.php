<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('trx_date');
            $table->string('direction', 10);
            $table->string('movement_type', 50);
            $table->string('reference_type', 100);
            $table->unsignedBigInteger('reference_id');
            $table->string('reference_no', 100)->nullable();
            $table->unsignedInteger('qty');
            $table->unsignedInteger('qty_before');
            $table->unsignedInteger('qty_after');
            $table->decimal('unit_cost', 18, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['item_id', 'location_id', 'trx_date'], 'stock_cards_item_location_date_index');
            $table->index(['reference_type', 'reference_id'], 'stock_cards_reference_index');
            $table->index(['movement_type', 'trx_date'], 'stock_cards_movement_date_index');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE stock_cards ADD CONSTRAINT chk_stock_cards_qty_positive CHECK (qty > 0)');
            DB::statement("ALTER TABLE stock_cards ADD CONSTRAINT chk_stock_cards_direction CHECK (direction IN ('in', 'out'))");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_cards');
    }
};
