<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Catégorie parente
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Sous-catégories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Produits de cette catégorie
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}