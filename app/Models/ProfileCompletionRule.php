<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileCompletionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_key',
        'display_name',
        'weight',
        'is_required',
        'status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'status' => 'boolean',
    ];
}
