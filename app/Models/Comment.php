<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Comment extends Model
{
    use QueryCacheable;
    use HasFactory;
    use NodeTrait;

    protected static bool $flushCacheOnUpdate = true;
    protected int $cacheFor = 3600;

    protected $casts = [
        'user_id' => 'int',
        'rating' => 'int',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'film_id',
        'user_id',
        'text',
        'rating',
        'created_at',
    ];

    protected $visible = [
        'id',
        'text',
        'rating',
        'created_at',
        'children',
        'user',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name']);
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class, 'film_id');
    }

    public function loadAllUsers(Comment $comment): void
    {
        foreach ($comment->children as $child) {
            $child->load('user');
            $this->loadAllUsers($child);
        }
    }
}
