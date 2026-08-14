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
            // 1. Hitung Total Aset
            $totalAssets = Item::count();
            
            // 2. Hitung Ruangan/Laboratorium (Jika menggunakan tabel lokasi/kategori, sesuaikan di sini. Sementara kita set statis atau hitung kategori)
            $totalRooms = 7; // Anda bisa mengubahnya nanti jika ada tabel khusus Ruangan

            // 3. Hitung Pinjaman Aktif (Status 'pending', 'borrowed', atau 'Dipinjam')
            $activeLoans = Borrowing::whereIn('status', ['pending', 'borrowed', 'Dipinjam'])->count();

            // 4. Hitung Barang yang sedang diservis (Status bukan 'completed' dan 'Selesai')
            $activeMaintenances = Maintenance::whereNotIn('status', ['completed', 'Selesai'])->count();

            // 5. Ambil 5 Transaksi Peminjaman Terbaru
            $recentLoans = Borrowing::with(['user', 'item'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($loan) {
                    return [
                        'code' => 'BRW-' . date('Y', strtotime($loan->created_at)) . '-' . str_pad($loan->id, 4, '0', STR_PAD_LEFT),
                        'borrower' => $loan->user->name ?? 'User ID: ' . $loan->user_id,
                        'department' => 'Umum', // Sesuaikan jika ada relasi departemen
                        'status' => $loan->status,
                        'dueDate' => $loan->expected_return_at ?? $loan->created_at->addDays($loan->duration_days ?? 7)->format('Y-m-d H:i:s'),
                    ];
                });

            // 6. Ambil 5 Catatan Servis Terbaru
            $recentMaintenances = Maintenance::with(['item'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($maint) {
                    return [
                        'code' => 'MNT-' . date('Y', strtotime($maint->created_at)) . '-' . str_pad($maint->id, 4, '0', STR_PAD_LEFT),
                        'asset' => $maint->item->name ?? 'Unknown Asset',
                        'type' => 'Perbaikan',
                        // Cek menggunakan huruf kecil agar aman jika ada perbedaan kapitalisasi
                        'status' => (strtolower($maint->status) === 'completed' || strtolower($maint->status) === 'selesai') ? 'Completed' : 'In Progress',
                        'scheduled' => $maint->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            // 7. Ambil 5 Jadwal Servis / Kalibrasi Mendatang (PROAKTIF)
            $upcomingServices = Item::whereNotNull('next_service_date')
                ->whereDate('next_service_date', '>=', now()) // Hanya ambil tanggal hari ini atau masa depan
                ->orderBy('next_service_date', 'asc') // Urutkan dari yang paling dekat
                ->take(5)
                ->get(['inventory_code', 'name', 'next_service_date']);

            return response()->json([
                'success' => true,
                'data' => [
                    'metrics' => [
                        'total_assets' => $totalAssets,
                        'total_rooms' => $totalRooms,
                        'active_loans' => $activeLoans,
                        'active_maintenances' => $activeMaintenances,
                    ],
                    'recent_loans' => $recentLoans,
                    'recent_maintenances' => $recentMaintenances,
                    'upcoming_services' => $upcomingServices, // <-- Disisipkan di sini untuk dikirim ke Vue
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}