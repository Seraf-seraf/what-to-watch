<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property string $email
 * @property string|null $password
 * @property string $name
 * @property string|null $file
 *
 * @property Collection|Comment[] $comments
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    public $timestamps = false;

    protected $table = 'users';

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'email',
        'password',
        'name',
        'file',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->roles()->where(['name' => 'admin'])->exists();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'users_roles', 'user_id', 'role_id');
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, Favorite::class, 'user_id', 'film_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function setAsAdmin(): void
    {
        if ($adminRole = Role::firstWhere(['name' => 'admin'])) {
            $this->roles()->attach($adminRole);
        }
    }
}
