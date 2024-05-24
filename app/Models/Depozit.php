<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depozit extends Model
{
  const UPDATED_AT = null;
    use HasFactory;
    protected $fillable = [
      'lid_id',
      'user_id',
      'depozit',
      'created_at'
    ];
}
