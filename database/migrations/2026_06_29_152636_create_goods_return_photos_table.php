<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_return_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_return_item_id')->constrained('goods_return_items')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('goods_return_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_return_photos');
    }
};
