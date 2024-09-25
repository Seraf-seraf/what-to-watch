<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Favorite
 *
 * @property int $id
 * @property int $user_id
 * @property int $film_id
 *
 * @property Film $film
 *
 * @package App\Models
 */
class Favorite extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'film_id',
    ];

    protected $visible = [
        'film',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class, 'film_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
