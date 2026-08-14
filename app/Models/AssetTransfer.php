<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $fillable = [
        'item_id', 'from_location_id', 'to_location_id', 
        'transferred_by', 'transfer_date', 'reason'
    ];

    public function item() {
        return $this->belongsTo(Item::class);
    }
}