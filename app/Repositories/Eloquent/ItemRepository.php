<?php
namespace App\Repositories\Eloquent;

use App\Models\Item;
use App\Repositories\Contracts\ItemRepositoryInterface;

class ItemRepository implements ItemRepositoryInterface
{
    public function getAllForUser($user)
    {
        $query = Item::with(['category', 'location', 'condition'])->latest();

        // LBAC ISOLATION LOGIC
        if (!$user->hasRole(['Super Admin', 'Wakasek Sarpras'])) {
            $query->where('location_id', $user->location_id);
        }

        return $query->paginate(15);
    }

    public function countByCategoryAndYear($categoryId, $year)
    {
        return Item::where('category_id', $categoryId)
                   ->whereYear('created_at', $year)
                   ->count();
    }

    public function create(array $data)
    {
        return Item::create($data);
    }
}