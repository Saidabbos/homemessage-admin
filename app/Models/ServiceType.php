<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServiceType extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'duration',
        'price',
        'image',
        'status',
    ];

    public $translatable = ['name', 'description'];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'price' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        return asset('images/placeholder.svg');
    }
}
