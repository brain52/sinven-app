<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true, 
            'data' => Supplier::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', 
            'description' => 'nullable|string'
        ]);

        $supplier = Supplier::create($request->all());

        return response()->json([
            'success' => true, 
            'message' => 'Vendor berhasil ditambahkan',
            'data' => $supplier
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255', 
            'description' => 'nullable|string'
        ]);

        $supplier->update($request->all());

        return response()->json([
            'success' => true, 
            'message' => 'Vendor berhasil diperbarui',
            'data' => $supplier
        ]);
    }

    public function destroy(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vendor berhasil dihapus'
        ]);
    }
}