<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'description',
        'published',
    ];

    public $translatable = ['title', 'content', 'description'];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
        ];
    }
}
