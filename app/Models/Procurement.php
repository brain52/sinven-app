<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    protected $fillable = [
        'po_number', 'title', 'order_date', 'status',
        'total_items_cost', 'shipping_cost', 'service_fee', 'grand_total',
        'created_by', 'notes'
    ];

    // Relasi: Satu Pengadaan memiliki banyak Barang (Items)
    public function items()
    {
        return $this->hasMany(ProcurementItem::class);
    }
}