<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balans extends Model
{
  const UPDATED_AT = null;
  const CREATED_AT = null;
  use HasFactory;
    protected $fillable = [
      'user_id',
      'balans',
      'date',
      'time'
    ];
}
