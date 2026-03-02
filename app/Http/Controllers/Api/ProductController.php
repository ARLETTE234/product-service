<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Liste des produits",
     *     tags={"Produits"},
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="min_price", in="query", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="max_price", in="query", required=false, @OA\Schema(type="number")),
     *     @OA\Parameter(name="is_featured", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Liste des produits")
     * )
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'activePromotion'])
            ->where('status', 'active');

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->is_featured);
        }
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{slug}",
     *     summary="Détail d'un produit",
     *     tags={"Produits"},
     *     @OA\Parameter(name="slug", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Détail du produit"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function show($slug)
    {
        $product = Product::with([
            'category',
            'variants',
            'activePromotion',
            'reviews' => function($q) { $q->where('is_approved', true); },
            'tags'
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        $product->increment('views_count');

        return response()->json($product);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Créer un produit",
     *     tags={"Produits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","category_id","price"},
     *             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", example=999.99),
     *             @OA\Property(property="stock_quantity", type="integer", example=50),
     *             @OA\Property(property="status", type="string", example="active"),
     *             @OA\Property(property="sku", type="string", example="IPH15PRO"),
     *             @OA\Property(property="short_description", type="string", example="Description courte")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Produit créé"),
     *     @OA\Response(response=422, description="Erreur de validation")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'price'             => 'required|numeric|min:0',
            'stock_quantity'    => 'integer|min:0',
            'status'            => 'in:draft,active,archived',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'sku'               => 'nullable|string|unique:products',
            'is_featured'       => 'boolean',
            'weight'            => 'nullable|numeric',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
        ]);

        $validated['slug'] = \Str::slug($validated['name']) . '-' . uniqid();

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     summary="Modifier un produit",
     *     tags={"Produits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="stock_quantity", type="integer"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Produit modifié"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'category_id'       => 'sometimes|exists:categories,id',
            'price'             => 'sometimes|numeric|min:0',
            'stock_quantity'    => 'sometimes|integer|min:0',
            'status'            => 'sometimes|in:draft,active,archived',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'is_featured'       => 'boolean',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     summary="Supprimer un produit",
     *     tags={"Produits"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Produit supprimé"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/products/{id}/related",
     *     summary="Produits similaires",
     *     tags={"Produits"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Produits similaires")
     * )
     */
    public function related($id)
    {
        $product = Product::findOrFail($id);

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->limit(8)
            ->get();

        return response()->json($related);
    }
}