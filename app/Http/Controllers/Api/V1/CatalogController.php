<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Product;
use Illuminate\Http\JsonResponse;

final class CatalogController
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->where('active', true)
            ->with(['plans' => fn ($q) => $q->where('active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['data' => $products]);
    }
}
