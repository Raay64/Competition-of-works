<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'deadline_at',
        'is_active',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
