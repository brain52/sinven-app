<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Condition;
use Illuminate\Http\Request;

class ConditionController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Condition::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $condition = Condition::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kondisi fisik berhasil ditambahkan',
            'data' => $condition
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $condition = Condition::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $condition->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Kondisi fisik berhasil diperbarui',
            'data' => $condition
        ]);
    }

    public function destroy(string $id)
    {
        $condition = Condition::findOrFail($id);
        $condition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kondisi fisik berhasil dihapus'
        ]);
    }
}