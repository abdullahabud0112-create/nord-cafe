<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public const TYPE_MINUMAN = 'minuman';

    public const TYPE_MAKANAN = 'makanan';

    public const TYPES = [self::TYPE_MINUMAN, self::TYPE_MAKANAN];

    protected $fillable = ['name', 'slug', 'type', 'icon', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
