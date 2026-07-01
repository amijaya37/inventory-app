<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->date('return_date');
            $table->string('origin_type', 30)->default('location');
            $table->foreignId('origin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('origin_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('origin_pic_name');
            $table->string('origin_pic_phone')->nullable();
            $table->foreignId('warehouse_location_id')->constrained('locations')->restrictOnDelete();
            $table->text('return_reason');
            $table->string('status', 30)->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['return_date', 'status']);
            $table->index(['origin_type', 'origin_user_id']);
            $table->index(['origin_location_id']);
            $table->index(['warehouse_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_returns');
    }
};
