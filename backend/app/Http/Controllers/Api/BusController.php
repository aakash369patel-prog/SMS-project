<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function index()
    {
        return response()->json(Bus::query()->with('route')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'transport_route_id' => ['nullable', 'exists:transport_routes,id'],
            'registration_number' => ['required', 'string', 'max:30', 'unique:buses,registration_number'],
            'capacity' => ['required', 'integer', 'min:1'],
            'driver_name' => ['nullable', 'string'],
            'driver_phone' => ['nullable', 'string', 'max:20'],
        ]);

        return response()->json(['data' => Bus::query()->create($data)], 201);
    }

    public function update(Request $request, Bus $bus)
    {
        $data = $request->validate([
            'transport_route_id' => ['nullable', 'exists:transport_routes,id'],
            'registration_number' => ['sometimes', 'string', 'max:30', 'unique:buses,registration_number,'.$bus->id],
            'capacity' => ['sometimes', 'integer', 'min:1'],
            'driver_name' => ['nullable', 'string'],
            'driver_phone' => ['nullable', 'string', 'max:20'],
        ]);
        $bus->update($data);

        return response()->json(['data' => $bus->load('route')]);
    }

    public function destroy(Bus $bus)
    {
        $bus->delete();

        return response()->json(['message' => 'Bus deleted.']);
    }
}
