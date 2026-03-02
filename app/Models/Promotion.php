<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'discount_value',
        'discount_type',
        'promo_price',
        'starts_at',
        'ends_at',
        'is_active'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'promo_price'    => 'decimal:2',
        'is_active'      => 'boolean',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
    ];

    // Relation avec le produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Vérifier si la promotion est en cours
    public function isActive()
    {
        return $this->is_active
            && $this->starts_at <= now()
            && $this->ends_at >= now();
    }
}