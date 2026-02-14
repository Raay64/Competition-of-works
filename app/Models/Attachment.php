<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'user_id',
        'original_name',
        'mime',
        'size',
        'storage_key',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SCANNED = 'scanned';
    public const STATUS_REJECTED = 'rejected';

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
