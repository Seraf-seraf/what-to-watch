<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Comment extends Model
{
    use QueryCacheable;
    use HasFactory;

    protected static bool $flushCacheOnUpdate = true;

    public $timestamps = false;

    protected int $cacheFor = 3600;

    protected $table = 'comments';

    protected $casts = [
        'user_id' => 'int',
        'rating' => 'int',
        'comment_id' => 'int',
        'created_at' => 'timestamp',
    ];

    protected $fillable = [
        'id',
        'film_id',
        'user_id',
        'text',
        'rating',
        'comment_id',
        'created_at',
    ];

    protected $visible = [
        'id',
        'text',
        'rating',
        'created_at',
        'comments',
        'user',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name']);
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class, 'film_id');
    }
}
