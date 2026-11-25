<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = ['username', 'password', 'nama', 'dibuat_pada'];

    protected $hidden = ['password'];
}
