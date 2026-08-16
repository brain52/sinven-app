<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    protected $fillable = [
        'procurement_id', 'item_name', 'quantity', 
        'unit_price', 'subtotal', 'item_type'
    ];

    // Relasi kebalikannya
    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    }
}