<?php

namespace App\Http\Controllers\Web;

use App\Domain\Inventory\Enums\AuditEvent;
use App\Domain\Inventory\Models\GoodsIssue;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Inventory\Models\TransactionDocument;
use App\Domain\Inventory\Services\InventoryAuditService;
use App\Domain\Inventory\Services\TransactionDocumentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionDocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionDocumentController extends Controller
{
    public function storeGoodsReceipt(StoreTransactionDocumentRequest $request, GoodsReceipt $goodsReceipt, TransactionDocumentService $service): RedirectResponse
    {
        $request->user()->can('goods-receipts.documents.upload') || abort(403);
        $service->upload($goodsReceipt, $request->file('file'), $request->string('document_type')->toString(), 'goods_receipts', $request->user());

        return back()->with('success', 'Dokumen barang masuk berhasil diupload.');
    }

    public function storeGoodsIssue(StoreTransactionDocumentRequest $request, GoodsIssue $goodsIssue, TransactionDocumentService $service): RedirectResponse
    {
        $request->user()->can('goods-issues.documents.upload') || abort(403);
        $service->upload($goodsIssue, $request->file('file'), $request->string('document_type')->toString(), 'goods_issues', $request->user());

        return back()->with('success', 'Dokumen barang keluar berhasil diupload.');
    }

    public function download(TransactionDocument $document, InventoryAuditService $auditService): StreamedResponse
    {
        Gate::authorize('download', $document);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404, 'File tidak ditemukan.');
        $auditService->log(AuditEvent::Download, $document->module, $document, auth()->user(), meta: [
            'document_id' => $document->id,
            'original_name' => $document->original_name,
        ]);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    public function destroy(TransactionDocument $document, TransactionDocumentService $service): RedirectResponse
    {
        Gate::authorize('delete', $document);
        $service->delete($document, auth()->user());

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
