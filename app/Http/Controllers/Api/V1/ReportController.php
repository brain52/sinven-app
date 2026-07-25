<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Exports\ItemsExport;
use App\Models\Item;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Endpoint Export ke Excel (.xlsx)
     */
    public function exportExcel($location_id)
    {
        $fileName = 'laporan-inventaris-' . date('Y-m-d') . '.xlsx';
        return Excel::download(new ItemsExport($location_id), $fileName);
    }

    /**
     * Endpoint Export ke PDF (.pdf)
     */
    public function exportPdf($location_id)
    {
        $items = Item::with(['category', 'condition', 'location'])
                     ->where('location_id', $location_id)
                     ->get();

        $pdf = Pdf::loadView('reports.items-pdf', compact('items'));
        
        return $pdf->download('laporan-inventaris-' . date('Y-m-d') . '.pdf');
    }
}