<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang diizinkan untuk diisi secara massal (Mass Assignment Protection)
     */
    protected $fillable = [
        'item_id', 'reported_by', 'technician_id', 'problem_description', 
        'reported_at', 'completed_at', 'cost', 'status', 'resolution_notes'
    ];

    /**
     * Mengonversi string waktu dari database menjadi objek Carbon secara otomatis.
     */
    protected $casts = [
        'reported_at' => 'datetime',
        'completed_at' => 'datetime',
        'cost' => 'decimal:2'
    ];

    // --- Definisi Relasi ORM ---

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}