<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true, 
            'data' => Brand::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', 
            'description' => 'nullable|string'
        ]);

        $brand = Brand::create($request->all());

        return response()->json([
            'success' => true, 
            'message' => 'Merek berhasil ditambahkan',
            'data' => $brand
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255', 
            'description' => 'nullable|string'
        ]);

        $brand->update($request->all());

        return response()->json([
            'success' => true, 
            'message' => 'Merek berhasil diperbarui',
            'data' => $brand
        ]);
    }

    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Merek berhasil dihapus'
        ]);
    }
}