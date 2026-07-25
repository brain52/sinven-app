<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;

class Item extends Model
{
    // LogsActivity dimatikan sementara
    use HasFactory, SoftDeletes; 

    protected $fillable = [
        'inventory_code', 'name', 'category_id', 'location_id', 
        'condition_id', 'serial_number', 'purchase_date', 'price', 'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'price' => 'decimal:2',
    ];

    /* FITUR SPATIE DIMATIKAN SEMENTARA
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
    */

    public function category() { return $this->belongsTo(Category::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function condition() { return $this->belongsTo(Condition::class); }
}