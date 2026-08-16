<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Maintenance;

class DashboardController extends Controller
{
    public function index(Request $request, $location_id)
    {
        try {
            // 1. Hitung Total Aset dan Valuasi 
            // (Aman, karena tabel items pasti punya location_id dan purchase_price)
            $totalAssets = Item::where('location_id', $location_id)->count();
            $totalValuation = Item::where('location_id', $location_id)->sum('purchase_price'); 

            // 2. Hitung Ruangan/Laboratorium
            $totalRooms = 7; 

            // 3. Hitung Pinjaman Aktif 
            // (Dikembalikan ke logika ASLI Anda agar tidak error)
            $activeLoans = Borrowing::whereIn('status', ['pending', 'borrowed', 'Dipinjam'])->count();

            // 4. Hitung Barang yang sedang diservis 
            // (Dikembalikan ke logika ASLI Anda agar tidak error)
            $activeMaintenances = Maintenance::whereNotIn('status', ['completed', 'Selesai'])->count();

            // 5. Ambil 5 Transaksi Peminjaman Terbaru (Logika ASLI Anda)
            $recentLoans = Borrowing::with(['user', 'item'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($loan) {
                    return [
                        'code' => 'BRW-' . date('Y', strtotime($loan->created_at)) . '-' . str_pad($loan->id, 4, '0', STR_PAD_LEFT),
                        'borrower' => $loan->user->name ?? 'User ID: ' . $loan->user_id,
                        'department' => 'Umum', 
                        'status' => $loan->status,
                        'dueDate' => $loan->expected_return_at ?? $loan->created_at->addDays($loan->duration_days ?? 7)->format('Y-m-d H:i:s'),
                    ];
                });

            // 6. Ambil 5 Catatan Servis Terbaru (Logika ASLI Anda)
            $recentMaintenances = Maintenance::with(['item'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($maint) {
                    return [
                        'code' => 'MNT-' . date('Y', strtotime($maint->created_at)) . '-' . str_pad($maint->id, 4, '0', STR_PAD_LEFT),
                        'asset' => $maint->item->name ?? 'Unknown Asset',
                        'type' => 'Perbaikan',
                        'status' => (strtolower($maint->status) === 'completed' || strtolower($maint->status) === 'selesai') ? 'Completed' : 'In Progress',
                        'scheduled' => $maint->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            // 7. Ambil 5 Jadwal Servis / Kalibrasi Mendatang
            $upcomingServices = Item::where('location_id', $location_id)
                ->whereNotNull('next_service_date')
                ->whereDate('next_service_date', '>=', now()) 
                ->orderBy('next_service_date', 'asc') 
                ->take(5)
                ->get(['inventory_code', 'name', 'next_service_date']);

            return response()->json([
                'success' => true,
                'data' => [
                    'metrics' => [
                        'total_assets' => $totalAssets,
                        // Pastikan dikirim sebagai angka numerik (float) agar terbaca oleh Vue
                        'total_valuation' => (float) $totalValuation, 
                        'total_rooms' => $totalRooms,
                        'active_loans' => $activeLoans,
                        'active_maintenances' => $activeMaintenances,
                    ],
                    'recent_loans' => $recentLoans,
                    'recent_maintenances' => $recentMaintenances,
                    'upcoming_services' => $upcomingServices, 
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                // Tambahkan pesan error asli dari Laravel untuk mempermudah detektif jika ada error lagi
                'message' => 'Gagal memuat data dashboard: ' . $e->getMessage() 
            ], 500);
        }
    }
}