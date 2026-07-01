<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('qty_on_hand')->default(0);
            $table->unsignedInteger('qty_reserved')->default(0);
            $table->integer('qty_available')->storedAs('qty_on_hand - qty_reserved');
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();
            $table->unique(['item_id', 'location_id'], 'stocks_item_location_unique');
            $table->index(['location_id', 'item_id'], 'stocks_location_item_index');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE stocks ADD CONSTRAINT chk_stocks_qty_available_non_negative CHECK (qty_on_hand >= qty_reserved)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
