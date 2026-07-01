<?php

namespace App\Http\Controllers\Web\Transaction;

use App\Actions\Inventory\GoodsReturn\PostGoodsReturnAction;
use App\Actions\Inventory\GoodsReturn\StoreGoodsReturnAction;
use App\Domain\Inventory\Models\GoodsReturn;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\PostGoodsReturnRequest;
use App\Http\Requests\Transaction\StoreGoodsReturnRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoodsReturnController extends Controller
{
    public function index(Request $request): View
    {
        $goodsReturns = GoodsReturn::query()
            ->with(['originUser', 'originLocation', 'warehouseLocation', 'postedBy'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('return_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('return_date', '<=', $request->date('date_to')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $q = '%'.$request->string('q')->toString().'%';
                $query->where(function ($query) use ($q): void {
                    $query->where('return_no', 'like', $q)->orWhere('origin_pic_name', 'like', $q);
                });
            })
            ->latest('return_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.goods-returns.index', ['goodsReturns' => $goodsReturns]);
    }

    public function create(): View
    {
        return view('transactions.goods-returns.create', [
            'items' => Item::query()->where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreGoodsReturnRequest $request, StoreGoodsReturnAction $action): RedirectResponse
    {
        $goodsReturn = $action->execute($request->validated(), $request->user());

        return redirect()->route('goods-returns.show', $goodsReturn)->with('success', 'Draft barang tarikan berhasil dibuat.');
    }

    public function show(GoodsReturn $goodsReturn): View
    {
        $goodsReturn->load(['items.item', 'items.photos', 'originUser', 'originLocation', 'warehouseLocation', 'creator', 'postedBy']);

        return view('transactions.goods-returns.show', ['goodsReturn' => $goodsReturn]);
    }

    public function post(PostGoodsReturnRequest $request, GoodsReturn $goodsReturn, PostGoodsReturnAction $action): RedirectResponse
    {
        $postedReturn = $action->execute($goodsReturn, $request->user());

        return redirect()->route('goods-returns.show', $postedReturn)->with('success', 'Barang tarikan berhasil diposting. Stok hanya bertambah untuk final action return_to_stock.');
    }
}
