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
}