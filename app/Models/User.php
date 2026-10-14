<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;               // IMPORTANTE
use Illuminate\Notifications\Notifiable;  // IMPORTANTE
use MongoDB\Laravel\Eloquent\Model;

class User extends Model implements AuthenticatableContract, MustVerifyEmail
{
    use Authenticatable, MustVerifyEmailTrait, Notifiable;

    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'email_verified_at',   // campo para fecha de verificación
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
