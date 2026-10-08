<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayrollEntry;
use App\Models\StaffMember;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        return response()->json(PayrollEntry::query()->with('staffMember')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_member_id' => ['required', 'exists:staff_members,id'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,paid'],
            'paid_date' => ['nullable', 'date'],
        ]);

        $staff = StaffMember::query()->findOrFail($data['staff_member_id']);
        $basic = (float) $staff->basic_salary;
        $allowances = (float) ($data['allowances'] ?? 0);
        $deductions = (float) ($data['deductions'] ?? 0);
        $net = max(0, $basic + $allowances - $deductions);

        $entry = PayrollEntry::query()->updateOrCreate(
            [
                'staff_member_id' => $staff->id,
                'month' => $data['month'],
                'year' => $data['year'],
            ],
            [
                'basic_salary' => $basic,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'net_pay' => $net,
                'status' => $data['status'] ?? 'draft',
                'paid_date' => $data['paid_date'] ?? null,
            ]
        );

        return response()->json(['data' => $entry->load('staffMember')], 201);
    }
}
