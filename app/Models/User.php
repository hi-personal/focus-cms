<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'login',
        'nicename',
        'display_name',
        'status',
        'role'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status'    =>  'disabled',
        'role'      =>  'reader',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * A User "has many" posts.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }


    /**
     * Method meta
     *
     * @return void
     */
    public function meta()
    {
        return $this->hasMany(UserMeta::class);
    }

    /**
     * Method updateOrCreateMeta
     *
     * @param $name $name [explicite description]
     * @param $value $value [explicite description]
     *
     * @return void
     */
    public function updateOrCreateMeta($name, $value)
    {
        return $this->metas()->updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );
    }

    /**
     * Method metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class);
    }

    /**
     * Method links
     *
     * @return HasMany
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    /**
     * Method booted
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->login)) {
                $user->login = $user->name;
            }

            if (empty($user->nicename)) {
                $user->nicename = $user->name;
            }
        });
    }
}
