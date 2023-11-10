<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Extraction extends Model
{
    use HasFactory;
    
    protected $fillable=[
			'extracted_from',
			'extracted_from_type',
			'extraction_result',
    ];



}
