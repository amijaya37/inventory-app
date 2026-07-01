<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->nullable()->unique();
            $table->string('source_type')->default('purchase');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('warehouse_location_id')->constrained('locations')->restrictOnDelete();
            $table->string('po_no', 100)->nullable();
            $table->string('invoice_no', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('receipt_date');
            $table->string('po_file_path')->nullable();
            $table->string('invoice_file_path')->nullable();
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['receipt_date', 'status']);
            $table->index(['supplier_id', 'receipt_date']);
            $table->index('warehouse_location_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
