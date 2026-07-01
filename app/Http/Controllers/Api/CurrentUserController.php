<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentUserController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        return JsonResource::make($request->user());
    }
}
