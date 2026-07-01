<?php

namespace App\Http\Controllers\Web\Stock;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockCardController extends Controller
{
    public function index(Request $request, Stock $stock): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $stock->load([
            'item:id,sku,name,unit,minimum_stock,category_id',
            'item.category:id,name',
            'location:id,name,code',
        ]);

        $stockCards = StockCard::query()
            ->with(['postedBy:id,name'])
            ->where('item_id', $stock->item_id)
            ->where('location_id', $stock->location_id)
            ->when(! empty($filters['date_from']), fn ($query) => $query->whereDate('trx_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($query) => $query->whereDate('trx_date', '<=', $filters['date_to']))
            ->orderByDesc('trx_date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('stocks.cards.index', [
            'stock' => $stock,
            'stockCards' => $stockCards,
            'filters' => $filters,
        ]);
    }
}
