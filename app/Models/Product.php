<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'image_url',
        'calories',
        'rating',
        'tags',
        'is_available'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'float',
        'rating' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getPriceKAttribute(): string
    {
        $thousands = $this->price / 1000;
        return (floor($thousands) == $thousands ? number_format($thousands, 0) : number_format($thousands, 1)) . 'K';
    }

    public function getImageUrlAttribute($value): string
    {
        if (!empty($value) && $value !== '/placeholder.svg') {
            return $value;
        }

        // Warm, appetizing fallback photo when no image is stored
        return 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=80';
    }
}
