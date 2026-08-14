<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    protected $fillable = [
        'item_id', 'disposal_type', 'reason', 'disposed_by', 'disposal_date'
    ];
}