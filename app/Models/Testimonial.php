<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'client_name',
        'client_role',
        'comment',
        'rating',
        'sort_order',
        'status',
    ];

    public $translatable = ['client_name', 'client_role', 'comment'];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'rating' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
