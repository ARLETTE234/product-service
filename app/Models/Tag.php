<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    // Relation avec les produits
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}