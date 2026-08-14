<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Room; // <-- Pastikan Model dipanggil
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Room::all()]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $room = Room::create($request->all());
        return response()->json(['success' => true, 'data' => $room], 201);
    }

    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255', 'description' => 'nullable|string']);
        $room->update($request->all());
        return response()->json(['success' => true, 'data' => $room]);
    }

    public function destroy(string $id)
    {
        $room = Room::findOrFail($id);
        $room->delete();
        return response()->json(['success' => true]);
    }
}