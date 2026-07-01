<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Models\GoodsIssue;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GoodsIssueDocumentService
{
    public function generateHandoverPdf(GoodsIssue $goodsIssue): string
    {
        $goodsIssue->loadMissing(['items.item', 'sourceLocation', 'targetLocation', 'picUser', 'recipientUser', 'postedBy']);

        $pdf = Pdf::loadView('transactions.goods-issues.handover-pdf', [
            'goodsIssue' => $goodsIssue,
        ])->setPaper('a4', 'portrait');

        $documentNo = $goodsIssue->document_no ?: str_replace('BK-', 'ST-', (string) $goodsIssue->issue_no);
        $fileName = $documentNo.'-'.$goodsIssue->issue_no.'.pdf';
        $path = 'documents/goods-issues/'.$fileName;

        Storage::disk('local')->put($path, $pdf->output());
        $goodsIssue->forceFill(['handover_document_path' => $path])->save();

        return $path;
    }
}
