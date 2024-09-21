<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function film()
    {
        return $this->belongsTo(Film::class, 'film_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
