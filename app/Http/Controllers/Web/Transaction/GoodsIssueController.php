<?php

namespace App\Http\Controllers\Web\Transaction;

use App\Actions\Inventory\GoodsIssue\PostGoodsIssueAction;
use App\Actions\Inventory\GoodsIssue\StoreGoodsIssueAction;
use App\Domain\Inventory\Models\GoodsIssue;
use App\Domain\Inventory\Services\GoodsIssueDocumentService;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\PostGoodsIssueRequest;
use App\Http\Requests\Transaction\StoreGoodsIssueRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoodsIssueController extends Controller
{
    public function index(Request $request): View
    {
        $goodsIssues = GoodsIssue::query()
            ->with(['sourceLocation', 'targetLocation', 'picUser'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $q = '%'.$request->string('q')->toString().'%';
                $query->where(function ($query) use ($q): void {
                    $query->where('issue_no', 'like', $q)
                        ->orWhere('document_no', 'like', $q)
                        ->orWhere('recipient_name', 'like', $q);
                });
            })
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.goods-issues.index', ['goodsIssues' => $goodsIssues]);
    }

    public function create(): View
    {
        return view('transactions.goods-issues.create', [
            'items' => Item::query()->where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreGoodsIssueRequest $request, StoreGoodsIssueAction $action): RedirectResponse
    {
        $goodsIssue = $action->execute($request->validated(), $request->user());

        return redirect()->route('goods-issues.show', $goodsIssue)->with('success', 'Draft barang keluar berhasil dibuat.');
    }

    public function show(GoodsIssue $goodsIssue): View
    {
        $goodsIssue->load(['items.item', 'sourceLocation', 'targetLocation', 'recipientUser', 'picUser', 'requestedBy', 'postedBy', 'documents.uploader']);

        return view('transactions.goods-issues.show', ['goodsIssue' => $goodsIssue]);
    }

    public function post(PostGoodsIssueRequest $request, GoodsIssue $goodsIssue, PostGoodsIssueAction $action, GoodsIssueDocumentService $documentService): RedirectResponse
    {
        $postedIssue = $action->execute($goodsIssue, $request->user());
        $documentService->generateHandoverPdf($postedIssue);

        return redirect()->route('goods-issues.show', $postedIssue)->with('success', 'Barang keluar berhasil diposting dan stok otomatis berkurang.');
    }

    public function handover(GoodsIssue $goodsIssue): StreamedResponse
    {
        $path = $goodsIssue->handover_document_path;

        abort_unless(is_string($path) && $path !== '', 404, 'Dokumen belum dibuat.');
        abort_unless(Storage::disk('local')->exists($path), 404, 'File dokumen tidak ditemukan.');

        return Storage::disk('local')->download($path);
    }
}
