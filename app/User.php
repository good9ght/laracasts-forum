<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * set the route key
     *
     * @return mixed
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * A user can have many threads.
     *
     * @return void
     */
    public function threads()
    {
        return $this->hasMany(Thread::class)->latest();
    }
    /**
     * A user can have many activities.
     *
     * @return void
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
