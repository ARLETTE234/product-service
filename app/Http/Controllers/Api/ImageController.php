<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    // POST /api/v1/products/{id}/images
    public function upload(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $file      = $request->file('image');
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs('products/' . $productId, $filename, 'public');

        // Sauvegarder dans la table product_images
        $image = \DB::table('product_images')->insertGetId([
            'product_id' => $productId,
            'path'       => $path,
            'url'        => Storage::url($path),
            'is_primary' => !$this->hasPrimaryImage($productId),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'id'         => $image,
            'url'        => Storage::url($path),
            'is_primary' => !$this->hasPrimaryImage($productId),
        ], 201);
    }

    // GET /api/v1/products/{id}/images
    public function index($productId)
    {
        Product::findOrFail($productId);

        $images = \DB::table('product_images')
            ->where('product_id', $productId)
            ->orderBy('is_primary', 'desc')
            ->get();

        return response()->json($images);
    }

    // DELETE /api/v1/products/{id}/images/{imageId}
    public function destroy($productId, $imageId)
    {
        $image = \DB::table('product_images')
            ->where('id', $imageId)
            ->where('product_id', $productId)
            ->first();

        if (!$image) {
            return response()->json(['message' => 'Image non trouvée'], 404);
        }

        // Supprimer le fichier
        Storage::disk('public')->delete($image->path);

        // Supprimer de la base
        \DB::table('product_images')->where('id', $imageId)->delete();

        return response()->json(['message' => 'Image supprimée avec succès']);
    }

    // PUT /api/v1/products/{id}/images/{imageId}/primary
    public function setPrimary($productId, $imageId)
    {
        // Retirer primary de toutes les images
        \DB::table('product_images')
            ->where('product_id', $productId)
            ->update(['is_primary' => false]);

        // Mettre primary sur l'image choisie
        \DB::table('product_images')
            ->where('id', $imageId)
            ->where('product_id', $productId)
            ->update(['is_primary' => true]);

        return response()->json(['message' => 'Image principale définie avec succès']);
    }

    private function hasPrimaryImage($productId)
    {
        return \DB::table('product_images')
            ->where('product_id', $productId)
            ->where('is_primary', true)
            ->exists();
    }
}