<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * Class Film
 *
 * @property int $id
 * @property string $name
 * @property string $posterImage
 * @property string $previewImage
 * @property string $backgroundImage
 * @property string $backgroundColor
 * @property string $videoLink
 * @property string $previewVideoLink
 * @property string $description
 * @property int $rating
 * @property int $scoresCount
 * @property string $director
 * @property array $starring
 * @property int $runTime
 * @property string $genre
 * @property int $released
 * @property bool $isFavorite
 *
 * @property Collection|Favorite[] $favorites
 * @property Collection|Promo[] $promos
 *
 * @package App\Models
 */
class Film extends Model
{
    use QueryCacheable;
    use HasFactory;

    public const STATUS_READY = 'ready';
    public const STATUS_PENDING = 'pending';

    public const STATUS_MODERATE = 'moderate';

    public $timestamps = false;

    protected int $cacheFor = 86400;

    protected static bool $flushCacheOnUpdate = true;

    public function getCacheTagsToInvalidateOnUpdate($relation = null, $pivotedModels = null): array
    {
        return [
            "film:{$this->id}",
            'films',
        ];
    }


    protected $casts = [
        'rating' => 'int',
        'scoresCount' => 'int',
        'starring' => Json::class,
        'genre' => Json::class,
        'runTime' => 'int',
        'released' => 'string',
        'isFavorite' => 'bool',
    ];

    protected $fillable = [
        'name',
        'posterImage',
        'previewImage',
        'backgroundImage',
        'backgroundColor',
        'videoLink',
        'previewVideoLink',
        'description',
        'genre',
        'released',
        'director',
        'starring',
        'runTime',
        'imdb_id',
        'status'
    ];

    protected $appends = [
        'is_favorite'
    ];

    public function scopeOrdered($query, ?string $orderBy = null, ?string $orderTo = null)
    {
        return $query
            ->when($orderBy === 'rating', fn($query) => $query->withAvg('scores as rating', 'rating'))
            ->orderBy($orderBy ?? 'released', $orderTo ?? 'desc');
    }

    public function getScoresCountAttribute(): int
    {
        return $this->scores()->count();
    }

    public function scores(): HasMany
    {
        return $this->comments()->whereNotNull('rating');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'film_id');
    }

    public function getIsFavoriteAttribute(): bool
    {
        return $this->favorites()->where('user_id', auth()->user()?->id)->exists();
    }

    public function getRatingAttribute(): int|string
    {
        return number_format($this->comments()->whereNotNull('rating')->avg('rating'), 2) ?? 0;
    }

    public function promo(): HasOne
    {
        return $this->hasOne(Promo::class, 'film_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'film_id', 'id');
    }

    public function getModerateFilmsIds(): \Illuminate\Support\Collection
    {
        return Film::query()->where('status', Film::STATUS_MODERATE)->pluck('imdb_id');
    }

    public function getPendingFilmsIds(): \Illuminate\Support\Collection
    {
        return Film::query()->where('status', Film::STATUS_PENDING)->pluck('imdb_id');
    }
}
