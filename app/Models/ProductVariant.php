<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'attributes',
        'price',
        'stock_quantity',
        'is_active'
    ];

    protected $casts = [
        'attributes'  => 'array',
        'price'       => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    // Relation avec le produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}