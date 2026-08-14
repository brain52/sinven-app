<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    // Tambahkan baris ini agar data tidak ditolak
    protected $fillable = ['name', 'description'];
}