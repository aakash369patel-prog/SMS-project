<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query()->with('recorder')->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        return response()->json($query->paginate(25));
    }

    public function summary()
    {
        $total = (float) Expense::query()->sum('amount');
        $month = (float) Expense::query()->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');

        return response()->json([
            'data' => [
                'total_expenses' => $total,
                'current_month' => $month,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'paid_to' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'in:cash,bank,online,cheque'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['recorded_by'] = $request->user()->id;
        $expense = Expense::query()->create($data);

        return response()->json(['data' => $expense->load('recorder')], 201);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:60'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'expense_date' => ['sometimes', 'date'],
            'paid_to' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'in:cash,bank,online,cheque'],
            'notes' => ['nullable', 'string'],
        ]);

        $expense->update($data);

        return response()->json(['data' => $expense->fresh('recorder')]);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return response()->json(['message' => 'Expense deleted.']);
    }
}
