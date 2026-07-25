<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ItemService;
use App\Http\Requests\StoreItemRequest;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function index(Request $request, $location_id)
    {
        $items = $this->itemService->getItems($request->user());
        return response()->json(['success' => true, 'data' => $items]);
    }

    public function store(StoreItemRequest $request, $location_id)
    {
        $data = $request->validated();
        $data['location_id'] = $location_id; // Injeksi paksa dari parameter URL yang sudah divalidasi LBAC

        $item = $this->itemService->createItem($data);

        return response()->json([
            'success' => true,
            'message' => 'Barang berhasil dicatat.',
            'data' => $item
        ], 201);
    }
    /**
     * Men-generate QR Code berformat SVG untuk sebuah barang.
     * QR Code ini berisi Inventory Code unik yang bisa di-scan.
     */
    public function generateQr($location_id, $id)
    {
        try {
            // Pastikan barang tersebut ada di lokasi yang sesuai
            $item = \App\Models\Item::where('location_id', $location_id)->findOrFail($id);

            // Generate QR Code format SVG (Vektor agar tidak pecah saat dicetak)
            // Menggunakan Facade dari package Simple QrCode
            $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(250)
                    ->generate($item->inventory_code);

            // Kembalikan response berupa file gambar SVG murni
            return response($qr)->header('Content-Type', 'image/svg+xml');
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan'], 404);
        }
    }
}