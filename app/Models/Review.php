<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'user_name',
        'rating',
        'title',
        'comment',
        'is_verified_purchase',
        'is_approved'
    ];

    protected $casts = [
        'rating'               => 'integer',
        'is_verified_purchase' => 'boolean',
        'is_approved'          => 'boolean',
    ];

    // Relation avec le produit
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scope — uniquement les avis approuvés
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
}