<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'sku',
        'price',
        'compare_price',
        'stock_quantity',
        'status',
        'is_featured',
        'weight',
        'dimensions',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'compare_price' => 'decimal:2',
        'dimensions'    => 'array',
        'meta_keywords' => 'array',
        'is_featured'   => 'boolean',
    ];

    // Relations
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    // Promotion active en ce moment
    public function activePromotion()
    {
        return $this->hasOne(Promotion::class)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    // Prix effectif (promo ou normal)
    public function getEffectivePriceAttribute()
    {
        $promo = $this->activePromotion;
return $promo ? $promo->promo_price : $this->price;
    }
}