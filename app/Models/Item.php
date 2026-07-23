<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_code', 'name', 'category_id', 'location_id', 
        'condition_id', 'serial_number', 'purchase_date', 'price', 'status'
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function condition() { return $this->belongsTo(Condition::class); }
}