<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Créer des catégories
        $electronique = Category::create([
            'name'      => 'Électronique',
            'slug'      => 'electronique',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $vetements = Category::create([
            'name'      => 'Vêtements',
            'slug'      => 'vetements',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Créer des produits
        Product::create([
            'category_id'       => $electronique->id,
            'name'              => 'iPhone 15 Pro',
            'slug'              => 'iphone-15-pro',
            'short_description' => 'Le dernier iPhone avec puce A17 Pro',
            'price'             => 999.99,
            'stock_quantity'    => 50,
            'status'            => 'active',
            'is_featured'       => true,
            'sku'               => 'IPH15PRO',
        ]);

        Product::create([
            'category_id'       => $electronique->id,
            'name'              => 'Samsung Galaxy S24',
            'slug'              => 'samsung-galaxy-s24',
            'short_description' => 'Smartphone Android haut de gamme',
            'price'             => 849.99,
            'stock_quantity'    => 30,
            'status'            => 'active',
            'is_featured'       => false,
            'sku'               => 'SAMS24',
        ]);

        Product::create([
            'category_id'       => $vetements->id,
            'name'              => 'T-shirt Premium',
            'slug'              => 't-shirt-premium',
            'short_description' => 'T-shirt 100% coton bio',
            'price'             => 29.99,
            'stock_quantity'    => 100,
            'status'            => 'active',
            'is_featured'       => false,
            'sku'               => 'TSHIRT001',
        ]);

        echo "✅ Données de test créées avec succès !\n";
    }
}

