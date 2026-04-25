<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'cover_image',
        'published'
    ];

    protected $casts = [
        'published' => 'boolean',
    ];
}