<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $locationId;

    /**
     * Konstruktor untuk menerima filter lokasi lab tertentu (LBAC).
     */
    public function __construct($locationId)
    {
        $this->locationId = $locationId;
    }

    /**
     * Ambil data barang berdasarkan lokasi yang dipilih beserta relasinya.
     */
    public function collection()
    {
        return Item::with(['category', 'condition', 'location'])
                    ->where('location_id', $this->locationId)
                    ->get();
    }

    /**
     * Judul kolom pada file Excel.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Kode Inventaris',
            'Nama Barang',
            'Kategori',
            'Kondisi',
            'Lokasi Lab',
            'Harga (Rp)',
            'Status',
            'Tanggal Pembelian'
        ];
    }

    /**
     * Memetakan data database ke dalam baris kolom Excel.
     */
    public function map($item): array
    {
        return [
            $item->id,
            $item->inventory_code,
            $item->name,
            $item->category->name ?? '-',
            $item->condition->name ?? '-',
            $item->location->name ?? '-',
            $item->price,
            $item->status,
            $item->purchase_date ? $item->purchase_date->format('Y-m-d') : '-'
        ];
    }
}