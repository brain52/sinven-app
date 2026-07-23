<?php

namespace App\Repositories\Eloquent;

use App\Models\Location;
use App\Repositories\Contracts\LocationRepositoryInterface;

class LocationRepository implements LocationRepositoryInterface
{
    public function getAll()
    {
        return Location::with('department')->latest()->get();
    }

    public function getById($id)
    {
        return Location::findOrFail($id);
    }

    public function create(array $data)
    {
        return Location::create($data);
    }

    public function update($id, array $data)
    {
        $location = $this->getById($id);
        $location->update($data);
        return $location;
    }

    public function delete($id)
    {
        $location = $this->getById($id);
        return $location->delete();
    }
}