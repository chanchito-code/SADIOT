<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;               // IMPORTANTE
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;  // IMPORTANTE
use Illuminate\Notifications\Notifiable;

class User extends Model implements AuthenticatableContract, MustVerifyEmail
{
    use Authenticatable, Notifiable, MustVerifyEmailTrait;

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
