<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Promo
 *
 * @property int $id
 * @property int $film_id
 *
 * @property Film $film
 *
 * @package App\Models
 */
class Promo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'promo';

    protected $fillable = [
        'film_id',
    ];

    protected $visible = [
        'id',
        'film',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }
}
