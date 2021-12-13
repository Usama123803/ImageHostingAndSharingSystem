<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uploadphoto extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_ID',
        'upload_Photo',
        'path',
        'privacy',
    ];
}
