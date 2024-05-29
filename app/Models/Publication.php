<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasFactory;

    protected $primaryKey = 'doi';
    protected $keyType = 'string';
    protected $fillable = ['doi', 'data'];
    public $incrementing = false;
   
    protected $casts = [
        'data' => 'array',
    ];
}
