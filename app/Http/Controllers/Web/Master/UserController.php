<?php

namespace App\Http\Controllers\Web\Master;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%");
            })
            ->with(['location', 'roles'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('master-data.users.index', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}
