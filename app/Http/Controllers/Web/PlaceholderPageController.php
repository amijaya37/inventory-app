<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PlaceholderPageController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard');
    }
}
