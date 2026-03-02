<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // GET /api/search?q=smartphone
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $perPage = $request->get('per_page', 15);

        if (empty($query)) {
            return response()->json([
                'message' => 'Paramètre q obligatoire',
                'data'    => []
            ], 422);
        }

        $products = Product::with(['category', 'activePromotion'])
            ->where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('short_description', 'like', '%' . $query . '%')
                  ->orWhere('sku', 'like', '%' . $query . '%');
            })
            ->orderBy('views_count', 'desc')
            ->paginate($perPage);

        return response()->json([
            'query'   => $query,
            'total'   => $products->total(),
            'data'    => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
            ]
        ]);
    }
}