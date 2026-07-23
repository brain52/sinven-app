<?php
namespace App\Services;

use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Models\Category;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class ItemService
{
    protected $itemRepo;

    public function __construct(ItemRepositoryInterface $itemRepo)
    {
        $this->itemRepo = $itemRepo;
    }

    public function getItems($user)
    {
        return $this->itemRepo->getAllForUser($user);
    }

    public function createItem(array $data)
    {
        DB::beginTransaction();
        try {
            // 1. Generate Inventory Code (Format: DEPT-CAT-YEAR-XXXX)
            $category = Category::findOrFail($data['category_id']);
            $location = Location::with('department')->findOrFail($data['location_id']);
            
            $deptCode = $location->department ? $location->department->code : 'UMUM';
            $catCode = $category->prefix_code;
            $year = Carbon::now()->format('Y');

            $countThisYear = $this->itemRepo->countByCategoryAndYear($category->id, $year);
            $sequence = str_pad($countThisYear + 1, 4, '0', STR_PAD_LEFT); 

            $data['inventory_code'] = "{$deptCode}-{$catCode}-{$year}-{$sequence}";
            $data['status'] = 'Tersedia'; // Default awal

            // 2. Simpan ke Database
            $item = $this->itemRepo->create($data);
            
            DB::commit();
            return $item;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}