<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyPlugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'file_path',
        'file_name',
        'file_size',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];
}
