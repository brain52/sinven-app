<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    // 1. Menampilkan Semua Daftar Pengadaan
    public function index()
    {
        // Mengambil data pengadaan beserta detail barangnya, diurutkan dari yang terbaru
        $procurements = Procurement::with('items')->latest()->get();
        return response()->json(['success' => true, 'data' => $procurements]);
    }

    // 2. Menyimpan Data Pengadaan Baru (Header + Detail Barang)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'order_date' => 'required|date',
            'shipping_cost' => 'nullable|numeric|min:0',
            'service_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',

            // Validasi Array/Daftar Barang yang dibeli
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.item_type' => 'required|in:Asset,Consumable',
        ]);

        DB::beginTransaction();
        try {
            // Membuat Nomor PO Otomatis (Contoh: PO-20260815-4321)
            $poNumber = 'PO-' . date('Ymd') . '-' . rand(1000, 9999);

            // Menghitung Sub Total dari seluruh barang
            $totalItemsCost = 0;
            foreach ($request->items as $item) {
                $totalItemsCost += ($item['quantity'] * $item['unit_price']);
            }

            // Menghitung Grand Total (Subtotal + Ongkir + Layanan)
            $shipping = $request->shipping_cost ?? 0;
            $service = $request->service_fee ?? 0;
            $grandTotal = $totalItemsCost + $shipping + $service;

            // A. Simpan Data Induk (Header) ke tabel procurements
            $procurement = Procurement::create([
                'po_number' => $poNumber,
                'title' => $request->title,
                'order_date' => $request->order_date,
                'status' => 'Pending',
                'total_items_cost' => $totalItemsCost,
                'shipping_cost' => $shipping,
                'service_fee' => $service,
                'grand_total' => $grandTotal,
                'created_by' => $request->user()->id ?? 1,
                'notes' => $request->notes,
            ]);

            // B. Simpan Detail Barang ke tabel procurement_items
            foreach ($request->items as $item) {
                ProcurementItem::create([
                    'procurement_id' => $procurement->id,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'item_type' => $item['item_type'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daftar Pengadaan (PO) berhasil disimpan!',
                'data' => $procurement->load('items') // Tampilkan data yang baru disimpan
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. (Opsional) Fitur Hapus Pengadaan jika batal
    public function destroy($id)
    {
        $procurement = Procurement::findOrFail($id);
        $procurement->delete(); // Ini otomatis akan menghapus itemnya juga karena onDelete('cascade')

        return response()->json(['success' => true, 'message' => 'Data pengadaan berhasil dihapus.']);
    }

    // 4. Proses Terima Barang (Ubah Status & Auto-Insert ke Master Data)
    public function receive($location_id, $id)
    {
        // Ambil data PO beserta rincian barangnya
        $procurement = Procurement::with('items')->findOrFail($id);
        
        if ($procurement->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'PO sudah diproses sebelumnya.'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Ubah status PO menjadi Diterima
            $procurement->status = 'Diterima';
            $procurement->save();

            // 2. KEAJAIBAN MIGRASI OTOMATIS KE MASTER DATA
            foreach ($procurement->items as $poItem) {
                
                // HANYA pindahkan barang yang tipenya 'Asset' (Consumable dibiarkan saja)
                if ($poItem->item_type === 'Asset') {
                    
                    // Lakukan looping sebanyak Qty (Input Massal otomatis)
                    for ($i = 0; $i < $poItem->quantity; $i++) {
                        
                        // Beri penomoran otomatis jika jumlah barang lebih dari 1
                        $itemName = $poItem->quantity > 1 
                            ? $poItem->item_name . ' - Unit ' . ($i + 1) 
                            : $poItem->item_name;

                        // Buat data aset baru di tabel Master Data (items)
                        \App\Models\Item::create([
                            'name' => $itemName,
                            'inventory_code' => 'INV-' . date('Ymd') . '-' . rand(1000, 9999),
                            
                            // Kita masukkan ke ID 1 (misal: "Gudang Transit" atau default)
                            // Admin nanti tinggal "Mutasi" atau "Edit" dari halaman Master Data
                            'category_id' => 1, 
                            'brand_id' => 1,
                            'room_id' => 1,
                            'condition_id' => 1,
                            
                            'unit' => 'Unit',
                            'purchase_date' => $procurement->order_date,
                            'purchase_price' => $poItem->unit_price, // Harga asli tanpa ongkir
                            'status' => 'Tersedia',
                            'notes' => 'Aset otomatis dari ' . $procurement->po_number,
                            'location_id' => 1
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Barang diterima dan Aset berhasil dimasukkan ke Master Data!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }
}
