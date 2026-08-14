<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'duration',
        'price',
        'is_published'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_published' => 'boolean'
    ];

    //One course has one instructor(owned)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
