<?php

namespace App\Http\Controllers\Web\Master;

use App\Domain\Master\Models\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $suppliers = Supplier::query()
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master-data.suppliers.index', [
            'suppliers' => $suppliers,
            'search' => $search,
        ]);
    }
}

