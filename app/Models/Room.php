<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    // Tambahkan baris ini untuk membuka gembok keamanan Laravel
    protected $fillable = [
        'name',
        'description'
    ];
}