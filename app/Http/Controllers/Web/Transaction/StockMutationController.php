<?php

namespace App\Http\Controllers\Web\Transaction;

use App\Actions\Inventory\StockMutation\PostStockMutationAction;
use App\Actions\Inventory\StockMutation\StoreStockMutationAction;
use App\Domain\Inventory\Models\StockMutation;
use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Location;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\PostStockMutationRequest;
use App\Http\Requests\Transaction\StoreStockMutationRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMutationController extends Controller
{
    public function index(Request $request): View
    {
        $mutations = StockMutation::query()
            ->with(['sourceLocation', 'destinationLocation', 'requester', 'postedBy'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('mutation_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('mutation_date', '<=', $request->date('date_to')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $q = '%'.$request->string('q')->toString().'%';
                $query->where('mutation_no', 'like', $q);
            })
            ->latest('mutation_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('transactions.stock-mutations.index', ['mutations' => $mutations]);
    }

    public function create(): View
    {
        return view('transactions.stock-mutations.create', [
            'items' => Item::query()->where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::query()->where('is_active', true)->orderBy('name')->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreStockMutationRequest $request, StoreStockMutationAction $action): RedirectResponse
    {
        $mutation = $action->execute($request->validated(), $request->user());

        return redirect()->route('stock-mutations.show', $mutation)->with('success', 'Draft mutasi barang berhasil dibuat.');
    }

    public function show(StockMutation $stockMutation): View
    {
        $stockMutation->load(['items.item', 'sourceLocation', 'destinationLocation', 'requester', 'creator', 'postedBy']);

        return view('transactions.stock-mutations.show', ['mutation' => $stockMutation]);
    }

    public function post(PostStockMutationRequest $request, StockMutation $stockMutation, PostStockMutationAction $action): RedirectResponse
    {
        $postedMutation = $action->execute($stockMutation, $request->user());

        return redirect()->route('stock-mutations.show', $postedMutation)->with('success', 'Mutasi barang berhasil diposting. Stok asal berkurang dan stok tujuan bertambah.');
    }
}
