<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaffMember;
use Illuminate\Http\Request;

class StaffMemberController extends Controller
{
    public function index()
    {
        return response()->json(StaffMember::query()->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_code' => ['required', 'string', 'max:30', 'unique:staff_members,employee_code'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:staff_members,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:80'],
            'job_title' => ['nullable', 'string', 'max:80'],
            'hire_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        return response()->json(['data' => StaffMember::query()->create($data)], 201);
    }

    public function update(Request $request, StaffMember $staffMember)
    {
        $data = $request->validate([
            'employee_code' => ['sometimes', 'string', 'max:30', 'unique:staff_members,employee_code,'.$staffMember->id],
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'email' => ['sometimes', 'email', 'unique:staff_members,email,'.$staffMember->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:80'],
            'job_title' => ['nullable', 'string', 'max:80'],
            'hire_date' => ['nullable', 'date'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);
        $staffMember->update($data);

        return response()->json(['data' => $staffMember]);
    }

    public function destroy(StaffMember $staffMember)
    {
        $staffMember->delete();

        return response()->json(['message' => 'Staff member deleted.']);
    }
}
