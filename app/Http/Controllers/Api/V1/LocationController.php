<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LocationService;
use App\Http\Requests\StoreLocationRequest;

class LocationController extends Controller
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function store(StoreLocationRequest $request)
    {
        $location = $this->locationService->createLocation($request->validated());
        return response()->json([
            'success' => true,
            'code' => 201,
            'message' => 'Lokasi berhasil dibuat.',
            'data' => $location
        ], 201);
    }
}