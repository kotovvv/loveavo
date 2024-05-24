<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use HasApiTokens;

class Provider extends Authenticatable
{
    use HasFactory,Notifiable;

    protected $guard = 'provs';

    protected $fillable = [
      'name',
      'tel',
      'active',
      'related_users_id',
      'office_id',
      'user_id'
  ];

    protected $hidden = [
      'password',
      'remember_token'
  ];
}
