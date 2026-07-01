<?php

namespace App\Http\Controllers\Web\Master;

use App\Domain\Master\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $locations = Location::query()
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master-data.locations.index', [
            'locations' => $locations,
            'search' => $search,
        ]);
    }
}

