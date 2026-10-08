<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransportRoute;
use Illuminate\Http\Request;

class TransportRouteController extends Controller
{
    public function index()
    {
        return response()->json(TransportRoute::query()->withCount('assignments')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'unique:transport_routes,code'],
            'start_point' => ['nullable', 'string'],
            'end_point' => ['nullable', 'string'],
            'morning_departure' => ['nullable', 'date_format:H:i'],
            'afternoon_departure' => ['nullable', 'date_format:H:i'],
        ]);

        return response()->json(['data' => TransportRoute::query()->create($data)], 201);
    }

    public function update(Request $request, TransportRoute $transportRoute)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'code' => ['sometimes', 'string', 'max:30', 'unique:transport_routes,code,'.$transportRoute->id],
            'start_point' => ['nullable', 'string'],
            'end_point' => ['nullable', 'string'],
            'morning_departure' => ['nullable', 'date_format:H:i'],
            'afternoon_departure' => ['nullable', 'date_format:H:i'],
        ]);
        $transportRoute->update($data);

        return response()->json(['data' => $transportRoute]);
    }

    public function destroy(TransportRoute $transportRoute)
    {
        $transportRoute->delete();

        return response()->json(['message' => 'Route deleted.']);
    }
}
