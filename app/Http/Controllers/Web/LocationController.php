<?php

namespace App\Http\Controllers\Web;

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

    public function index()
    {
        $locations = $this->locationService->getAllLocations();
        return view('locations.index', compact('locations'));
    }

    public function store(StoreLocationRequest $request)
    {
        $this->locationService->createLocation($request->validated());
        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil ditambahkan!');
    }
}