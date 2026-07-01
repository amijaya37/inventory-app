<?php

namespace App\Http\Controllers\Web\Stock;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'stock_status' => ['nullable', 'in:all,low,empty'],
        ]);

        $query = Stock::query()
            ->select('stocks.*')
            ->with([
                'item:id,sku,name,category_id,minimum_stock,unit,is_serialized',
                'item.category:id,name',
                'location:id,name,code',
            ])
            ->join('items', 'items.id', '=', 'stocks.item_id');

        if (! empty($filters['category_id'])) {
            $query->where('items.category_id', $filters['category_id']);
        }

        if (! empty($filters['location_id'])) {
            $query->where('stocks.location_id', $filters['location_id']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = '%'.$filters['keyword'].'%';
            $query->where(function ($query) use ($keyword): void {
                $query->where('items.name', 'like', $keyword)
                    ->orWhere('items.sku', 'like', $keyword);
            });
        }

        if (($filters['stock_status'] ?? 'all') === 'low') {
            $query->where('stocks.qty_available', '>', 0)
                ->whereColumn('stocks.qty_available', '<=', 'items.minimum_stock');
        }

        if (($filters['stock_status'] ?? 'all') === 'empty') {
            $query->where('stocks.qty_available', 0);
        }

        $summary = [
            'total_item_location' => (clone $query)->count(),
            'total_qty_on_hand' => (clone $query)->sum('stocks.qty_on_hand'),
            'total_qty_reserved' => (clone $query)->sum('stocks.qty_reserved'),
            'total_qty_available' => (clone $query)->sum('stocks.qty_available'),
            'low_stock_count' => (clone $query)->where('stocks.qty_available', '>', 0)->whereColumn('stocks.qty_available', '<=', 'items.minimum_stock')->count(),
            'empty_stock_count' => (clone $query)->where('stocks.qty_available', 0)->count(),
        ];

        $stocks = $query
            ->orderBy('items.name')
            ->orderBy('stocks.location_id')
            ->paginate(25)
            ->withQueryString();

        return view('stocks.index', [
            'stocks' => $stocks,
            'summary' => $summary,
            'categories' => Category::query()->select('id', 'name')->orderBy('name')->get(),
            'locations' => Location::query()->select('id', 'name', 'code')->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }
}
