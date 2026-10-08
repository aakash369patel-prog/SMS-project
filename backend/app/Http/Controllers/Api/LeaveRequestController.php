<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index()
    {
        return response()->json(LeaveRequest::query()->with('staffMember')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'leave_type' => ['required', 'in:sick,casual,annual,unpaid,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string'],
        ]);

        return response()->json(['data' => LeaveRequest::query()->create($data)->load('staffMember')], 201);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);
        $leaveRequest->update($data);

        return response()->json(['data' => $leaveRequest->load('staffMember')]);
    }
}
