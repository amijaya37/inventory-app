<?php

namespace Tests\Feature;

use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Models\TransactionDocument;
use App\Domain\Master\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TransactionDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_goods_receipt_document_to_private_storage_and_audit_log(): void
    {
        Storage::fake('private');
        Permission::findOrCreate('documents.upload');
        Permission::findOrCreate('goods-receipts.documents.upload');

        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $user->givePermissionTo(['documents.upload', 'goods-receipts.documents.upload']);
        $receipt = $this->makeReceipt($user);

        $response = $this->actingAs($user)->post(route('goods-receipts.documents.store', $receipt), [
            'document_type' => 'invoice',
            'file' => UploadedFile::fake()->create('invoice.pdf', 512, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $document = TransactionDocument::query()->first();
        $this->assertNotNull($document);
        $this->assertSame('invoice', $document->document_type);
        Storage::disk('private')->assertExists($document->path);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'upload',
            'module' => 'goods_receipts',
            'reference_id' => $receipt->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_without_permission_cannot_download_document(): void
    {
        Storage::fake('private');
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $receipt = $this->makeReceipt($user);
        Storage::disk('private')->put('transaction-documents/test.pdf', 'dummy');

        $document = TransactionDocument::query()->create([
            'documentable_type' => GoodsReceipt::class,
            'documentable_id' => $receipt->id,
            'module' => 'goods_receipts',
            'document_type' => 'invoice',
            'original_name' => 'invoice.pdf',
            'stored_name' => 'test.pdf',
            'disk' => 'private',
            'path' => 'transaction-documents/test.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => 5,
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)->get(route('documents.download', $document))->assertForbidden();
    }

    public function test_download_creates_audit_log_for_allowed_user(): void
    {
        Storage::fake('private');
        Permission::findOrCreate('documents.download');
        Permission::findOrCreate('goods-receipts.documents.download');
        $user = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
        $user->givePermissionTo(['documents.download', 'goods-receipts.documents.download']);
        $receipt = $this->makeReceipt($user);
        Storage::disk('private')->put('transaction-documents/test.pdf', 'dummy');
        $document = TransactionDocument::query()->create([
            'documentable_type' => GoodsReceipt::class,
            'documentable_id' => $receipt->id,
            'module' => 'goods_receipts',
            'document_type' => 'invoice',
            'original_name' => 'invoice.pdf',
            'stored_name' => 'test.pdf',
            'disk' => 'private',
            'path' => 'transaction-documents/test.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size' => 5,
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)->get(route('documents.download', $document))->assertOk();
        $this->assertDatabaseHas('audit_logs', ['event' => 'download', 'module' => 'goods_receipts', 'reference_id' => $document->id]);
    }

    private function makeReceipt(User $user): GoodsReceipt
    {
        $location = Location::query()->create(['code' => 'WH-TST', 'name' => 'Warehouse Test', 'type' => 'warehouse']);

        return GoodsReceipt::query()->create([
            'receipt_no' => 'BM-TEST-'.uniqid(),
            'source_type' => 'purchase',
            'warehouse_location_id' => $location->id,
            'receipt_date' => now()->toDateString(),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);
    }
}
