<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Enums\AuditEvent;
use App\Domain\Inventory\Models\TransactionDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionDocumentService
{
    public function __construct(private readonly InventoryAuditService $auditService) {}

    public function upload(Model $transaction, UploadedFile $file, string $documentType, string $module, User $actor): TransactionDocument
    {
        $disk = 'private';
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $storedName = $documentType.'_'.Str::uuid().'.'.$extension;
        $folder = sprintf('transaction-documents/%s/%s/%s_%s', $module, now()->format('Y/m'), Str::singular($module), $transaction->getKey());
        $path = $folder.'/'.$storedName;

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()) ?: '');

        try {
            return DB::transaction(function () use ($transaction, $file, $documentType, $module, $disk, $path, $storedName, $extension, $actor): TransactionDocument {
                $document = TransactionDocument::query()->create([
                    'documentable_type' => $transaction::class,
                    'documentable_id' => $transaction->getKey(),
                    'module' => $module,
                    'document_type' => $documentType,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => $storedName,
                    'disk' => $disk,
                    'path' => $path,
                    'extension' => $extension,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize() ?: 0,
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'uploaded_by' => $actor->id,
                ]);

                $this->auditService->log(AuditEvent::Upload, $module, $transaction, $actor, after: [
                    'document_id' => $document->id,
                    'document_type' => $document->document_type,
                    'original_name' => $document->original_name,
                ], meta: ['size' => $document->size, 'mime_type' => $document->mime_type]);

                return $document;
            });
        } catch (\Throwable $throwable) {
            Storage::disk($disk)->delete($path);
            throw $throwable;
        }
    }

    public function delete(TransactionDocument $document, User $actor): void
    {
        DB::transaction(function () use ($document, $actor): void {
            $before = $document->toArray();
            $document->forceFill(['deleted_by' => $actor->id])->save();
            $document->delete();
            $this->auditService->log(AuditEvent::Delete, $document->module, $document, $actor, before: $before, meta: ['reason' => 'document_deleted']);
        });
    }
}
