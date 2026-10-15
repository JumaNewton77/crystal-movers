<?php

namespace App\Models;

use App\Models\Concerns\FlushesSiteCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryCategory extends Model
{
    use FlushesSiteCache;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class, 'gallery_category_id');
    }
}
