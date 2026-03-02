<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // GET /api/products/{id}/reviews
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reviews);
    }

    // POST /api/products/{id}/reviews
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'user_id'   => 'required|integer',
            'user_name' => 'required|string|max:255',
            'rating'    => 'required|integer|min:1|max:5',
            'title'     => 'nullable|string|max:255',
            'comment'   => 'nullable|string',
        ]);

        $validated['product_id'] = $productId;

        // Vérifier si l'utilisateur a déjà laissé un avis
        $existing = Review::where('product_id', $productId)
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Vous avez déjà laissé un avis pour ce produit'
            ], 422);
        }

        $review = Review::create($validated);

        // Mettre à jour la note moyenne du produit
        $this->updateProductRating($productId);

        return response()->json($review, 201);
    }

    // PUT /api/reviews/{id}/approve
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => true]);

        return response()->json(['message' => 'Avis approuvé avec succès']);
    }

    // DELETE /api/reviews/{id}
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $productId = $review->product_id;
        $review->delete();

        // Recalculer la note moyenne
        $this->updateProductRating($productId);

        return response()->json(['message' => 'Avis supprimé avec succès']);
    }

    // Recalculer la note moyenne du produit
    private function updateProductRating($productId)
    {
        $product = Product::findOrFail($productId);

        $stats = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        $product->update([
            'rating_average' => round($stats->average ?? 0, 2),
            'rating_count'   => $stats->count ?? 0,
        ]);
    }
}