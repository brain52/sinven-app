<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory; 

    protected $fillable = [
        'inventory_code', 'name', 'category_id', 'location_id', 'description', 
        'status', 'condition', 'condition_id', 
        'acquisition_date', 'expected_lifespan_years',
        'brand_id', 'unit', 'room_id', 'serial_number', 'supplier_id', 'tech_specs', 
        'purchase_date', 'purchase_price', 'invoice_number', 'funding_source',
        'warranty_months', 'warranty_expiry', 'notes', 'photo_path', 'ip_address', 'mac_address',
        'next_service_date'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
    ];

    // --- RELASI KE MASTER DATA ---
    public function category() { return $this->belongsTo(Category::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function condition() { return $this->belongsTo(Condition::class); }
    
    public function roomData() { return $this->belongsTo(Room::class, 'room_id'); }
    public function brandData() { return $this->belongsTo(Brand::class, 'brand_id'); }
    public function supplierData() { return $this->belongsTo(Supplier::class, 'supplier_id'); }
}