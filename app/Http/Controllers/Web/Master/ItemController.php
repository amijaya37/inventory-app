<?php

namespace App\Http\Controllers\Web\Master;

use App\Domain\Master\Models\Item;
use App\Domain\Master\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $categoryId = $request->input('category_id');

        $items = Item::query()
            ->when($search, function ($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with('category')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->get();

        return view('master-data.items.index', [
            'items' => $items,
            'categories' => $categories,
            'search' => $search,
            'categoryId' => $categoryId,
        ]);
    }
}

