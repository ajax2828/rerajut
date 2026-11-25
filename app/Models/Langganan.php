<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Langganan extends Model
{
    protected $table = 'langganan';

    public $timestamps = false;

    protected $fillable = ['email', 'dibuat_pada'];
}
