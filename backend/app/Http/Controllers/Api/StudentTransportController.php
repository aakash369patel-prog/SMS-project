<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentTransportAssignment;
use Illuminate\Http\Request;

class StudentTransportController extends Controller
{
    public function index()
    {
        $items = StudentTransportAssignment::query()
            ->with(['student', 'route', 'bus'])
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $items->items(), 'meta' => [
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
        ]]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'transport_route_id' => ['required', 'exists:transport_routes,id'],
            'bus_id' => ['nullable', 'exists:buses,id'],
            'pickup_point' => ['nullable', 'string', 'max:150'],
            'drop_point' => ['nullable', 'string', 'max:150'],
            'trip_type' => ['required', 'in:pickup,drop,both'],
        ]);

        $assignment = StudentTransportAssignment::query()->updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'transport_route_id' => $data['transport_route_id'],
            ],
            $data
        );

        return response()->json(['data' => $assignment->load(['student', 'route', 'bus'])], 201);
    }

    public function destroy(StudentTransportAssignment $studentTransport)
    {
        $studentTransport->delete();

        return response()->json(['message' => 'Assignment removed.']);
    }
}
