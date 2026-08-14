<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Item;
// INI ADALAH 3 BARIS IMPORT YANG WAJIB DITAMBAHKAN UNTUK FITUR MUTASI
use App\Models\AssetTransfer; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request, $location_id)
    {
        $items = Item::with(['category', 'condition', 'roomData', 'brandData', 'supplierData'])
            ->where('location_id', $location_id)
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(Request $request, $location_id)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'category_id'     => 'nullable|integer',
            'unit'            => 'nullable|string',
            'room_id'         => 'nullable|integer',
            'brand_id'        => 'nullable|integer',
            'serial_number'   => 'nullable|string',
            'supplier_id'     => 'nullable|integer',
            'tech_specs'      => 'nullable|string',
            'purchase_date'   => 'nullable|date',
            'purchase_price'  => 'nullable|numeric|min:0',
            'invoice_number'  => 'nullable|string',
            'funding_source'  => 'nullable|string',
            'warranty_months' => 'nullable|integer|min:0',
            'warranty_expiry' => 'nullable|date',
            'condition_id'    => 'nullable|integer',
            'status'          => 'nullable|string',
            'notes'           => 'nullable|string',
            'ip_address'      => 'nullable|ip',
            'mac_address'     => 'nullable|string|max:100',
        ]);

        $validated['inventory_code'] = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        $validated['location_id'] = $location_id;

        $validated['category_id'] = $request->category_id ?? 1;
        $validated['condition_id'] = $request->condition_id ?? 1;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('items', 'public');
            $validated['photo_path'] = $path;
        }

        $item = Item::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Aset berhasil didaftarkan!',
            'data'    => $item
        ], 201);
    }

    public function generateQr($location_id, $id)
    {
        try {
            $item = Item::where('location_id', $location_id)->findOrFail($id);

            $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(250)
                ->generate($item->inventory_code);

            return response($qr)->header('Content-Type', 'image/svg+xml');
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan'], 404);
        }
    }

    public function update(Request $request, $location_id, $id)
    {
        $item = Item::where('location_id', $location_id)->findOrFail($id);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category_id'    => 'required',
            'condition_id'   => 'required',
            'status'         => 'nullable|string',
            'room_id'        => 'nullable|integer',
            'purchase_date'  => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'ip_address'     => 'nullable|ip',
            'mac_address'    => 'nullable|string|max:100',
        ]);

        $item->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil diupdate',
            'data'    => $item
        ]);
    }

    public function destroy($location_id, $id)
    {
        try {
            $item = Item::where('location_id', $location_id)->findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang gagal dihapus karena masih terkait dengan data Peminjaman atau Servis.'
            ], 422);
        }
    }

    public function getLogs()
    {
        $logs = \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'data' => $logs]);
    }

   public function transfer(Request $request, $location_id, $item_id)
    {
        // 1. UBAH VALIDASI: Cek ke tabel 'rooms', bukan 'locations'
        $request->validate([
            'to_location_id' => 'required|exists:rooms,id',
            'reason' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($item_id);
            
            // 2. PERBAIKAN LOGIKA: Ruangan di tabel items menggunakan kolom 'room_id'
            $oldRoomId = $item->room_id;
            $newRoomId = $request->to_location_id;

            // Mencegah pemindahan ke ruangan yang sama
            if ($oldRoomId == $newRoomId) {
                throw new \Exception("Aset sudah berada di ruangan tersebut.");
            }

            AssetTransfer::create([
                'item_id' => $item->id,
                'from_location_id' => $oldRoomId, // Tetap gunakan nama kolom migration awal
                'to_location_id' => $newRoomId,
                'transferred_by' => $request->user()->id,
                'transfer_date' => Carbon::now(),
                'reason' => $request->reason
            ]);

            // 3. UBAH DATA ITEM: Update kolom 'room_id'
            $item->room_id = $newRoomId;
            $item->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Aset berhasil dimutasi ke ruangan baru.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // Gunakan status 422 agar pesan error bisa ditangkap oleh pop-up Vue Anda
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}