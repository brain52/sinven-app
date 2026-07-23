<?php

namespace App\Services;

use App\Repositories\Contracts\LocationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class LocationService
{
    protected $locationRepo;

    public function __construct(LocationRepositoryInterface $locationRepo)
    {
        $this->locationRepo = $locationRepo;
    }

    public function getAllLocations()
    {
        return $this->locationRepo->getAll();
    }

    public function createLocation(array $data)
    {
        DB::beginTransaction();
        try {
            $location = $this->locationRepo->create($data);
            // activity()->performedOn($location)->log('CREATE_LOCATION'); // Audit Trail
            DB::commit();
            return $location;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat lokasi: ' . $e->getMessage());
            throw $e;
        }
    }
}