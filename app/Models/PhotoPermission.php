<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoPermission extends Model
{
    use HasFactory;
    protected $fillable = [
        'permission_Given_By',
        'permission_Given_To',
        'path',
    ];
}
