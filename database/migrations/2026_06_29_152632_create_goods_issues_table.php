<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no')->unique();
            $table->date('issue_date');
            $table->foreignId('source_location_id')->constrained('locations')->restrictOnDelete();
            $table->string('recipient_type', 30)->default('external');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_name');
            $table->string('recipient_department')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->foreignId('target_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('pic_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->string('document_no')->nullable()->unique();
            $table->string('handover_document_path')->nullable();
            $table->string('status', 30)->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['issue_date', 'status']);
            $table->index(['source_location_id', 'status']);
            $table->index(['target_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_issues');
    }
};
