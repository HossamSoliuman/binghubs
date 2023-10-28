<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;
    protected $fillable = [
        'phone',
        'firstname',
        'lastname',
        'address',
        'city',
        'state',
        'zip_code',
        'age',
        'income_range',
    ];
}
