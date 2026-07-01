<?php

namespace App\Http\Controllers\Web\Transaction;

use App\Actions\Inventory\GoodsReceipt\PostGoodsReceiptAction;
use App\Actions\Inventory\GoodsReceipt\StoreGoodsReceiptAction;
use App\Domain\Inventory\DTOs\GoodsReceiptData;
use App\Domain\Inventory\Models\GoodsReceipt;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Domain\Master\Models\Supplier;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\PostGoodsReceiptRequest;
use App\Http\Requests\Transaction\StoreGoodsReceiptRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class GoodsReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $receipts = GoodsReceipt::query()
            ->with(['supplier', 'warehouseLocation', 'creator'])
            ->withCount('items')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(function ($query) use ($search): void {
                    $query->where('receipt_no', 'like', $search)
                        ->orWhere('po_no', 'like', $search)
                        ->orWhere('invoice_no', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest('receipt_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.goods-receipts.index', compact('receipts'));
    }

    public function create(): View
    {
        return view('transactions.goods-receipts.create', [
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(),
            'items' => Item::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreGoodsReceiptRequest $request, StoreGoodsReceiptAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $poFilePath = $request->file('po_file')?->store('documents/goods-receipts/po', 'local') ?: null;
        $invoiceFilePath = $request->file('invoice_file')?->store('documents/goods-receipts/invoices', 'local') ?: null;

        try {
            $receipt = $action->execute(
                data: GoodsReceiptData::fromArray([
                    ...$validated,
                    'source_type' => $validated['source_type'] ?? 'purchase',
                    'items' => $validated['items'],
                ]),
                actor: $request->user(),
                poFilePath: $poFilePath,
                invoiceFilePath: $invoiceFilePath,
                remarks: $validated['remarks'] ?? null,
                purchaseDate: $validated['purchase_date'] ?? null,
            );

            return redirect()->route('goods-receipts.show', $receipt)->with('success', 'Draft barang masuk berhasil dibuat.');
        } catch (Throwable $throwable) {
            if ($poFilePath) {
                Storage::disk('local')->delete($poFilePath);
            }
            if ($invoiceFilePath) {
                Storage::disk('local')->delete($invoiceFilePath);
            }

            report($throwable);

            return back()->withInput()->withErrors(['goods_receipt' => 'Gagal membuat barang masuk.']);
        }
    }

    public function show(GoodsReceipt $goodsReceipt): View
    {
        $goodsReceipt->load(['supplier', 'warehouseLocation', 'items.item', 'creator', 'poster', 'documents.uploader']);

        return view('transactions.goods-receipts.show', ['receipt' => $goodsReceipt]);
    }

    public function post(PostGoodsReceiptRequest $request, GoodsReceipt $goodsReceipt, PostGoodsReceiptAction $action): RedirectResponse
    {
        try {
            $receipt = $action->execute($goodsReceipt, $request->user());

            return redirect()->route('goods-receipts.show', $receipt)->with('success', 'Barang masuk berhasil diposting dan stok sudah bertambah.');
        } catch (Throwable $throwable) {
            report($throwable);

            return back()->withErrors(['posting' => $throwable->getMessage()]);
        }
    }
}
