<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->image == "") {
            return "";
        }
        return asset('uploads/products/small/' . $this->image);
    }
    function product_images()
    {
        return $this->hasMany(ProductImage::class);
    }
    function product_sizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
