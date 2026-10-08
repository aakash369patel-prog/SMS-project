<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecordFeePaymentRequest;
use App\Http\Requests\StoreStudentFeeRequest;
use App\Http\Resources\StudentFeeResource;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\Student;
use App\Models\StudentFee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;

class StudentFeeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'student_id' => ['nullable', 'exists:students,id'],
            'status' => ['nullable', 'string'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = StudentFee::query()
            ->with(['student.schoolClass', 'feeType'])
            ->latest();

        if (! empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['school_class_id'])) {
            $query->whereHas('student', fn ($q) => $q->where('school_class_id', $filters['school_class_id']));
        }

        if (! empty($filters['q'])) {
            $like = '%'.$filters['q'].'%';
            $query->where(function ($q) use ($like): void {
                $q->where('title', 'like', $like)
                    ->orWhereHas('student', function ($sq) use ($like): void {
                        $sq->where('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)
                            ->orWhere('admission_number', 'like', $like);
                    });
            });
        }

        return StudentFeeResource::collection($query->paginate(20));
    }

    public function store(StoreStudentFeeRequest $request): StudentFeeResource
    {
        $data = $request->validated();
        $feeType = FeeType::query()->findOrFail($data['fee_type_id']);

        $studentFee = StudentFee::query()->create([
            'student_id' => $data['student_id'],
            'fee_type_id' => $feeType->id,
            'title' => $data['title'] ?? $feeType->name,
            'amount' => $data['amount'] ?? $feeType->amount,
            'due_date' => $data['due_date'],
            'academic_year' => $data['academic_year'] ?? $feeType->academic_year,
            'status' => 'pending',
        ]);

        $studentFee->load(['student', 'feeType']);

        return new StudentFeeResource($studentFee);
    }

    public function show(StudentFee $studentFee): StudentFeeResource
    {
        $studentFee->load(['student', 'feeType', 'payments']);

        return new StudentFeeResource($studentFee);
    }

    public function destroy(StudentFee $studentFee): \Illuminate\Http\JsonResponse
    {
        $studentFee->delete();

        return response()->json(['message' => 'Student fee record deleted.']);
    }

    public function bulkAssignClass(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'due_date' => ['required', 'date'],
        ]);

        $feeType = FeeType::query()->findOrFail($data['fee_type_id']);
        $students = Student::query()->where('school_class_id', $data['school_class_id'])->get();

        DB::transaction(function () use ($students, $feeType, $data): void {
            foreach ($students as $student) {
                StudentFee::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'fee_type_id' => $feeType->id,
                        'academic_year' => $feeType->academic_year,
                    ],
                    [
                        'title' => $feeType->name,
                        'amount' => $feeType->amount,
                        'due_date' => $data['due_date'],
                        'status' => 'pending',
                    ]
                );
            }
        });

        return response()->json([
            'message' => 'Fees assigned to class successfully.',
            'students_count' => $students->count(),
        ]);
    }

    public function recordPayment(RecordFeePaymentRequest $request, StudentFee $studentFee): StudentFeeResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $studentFee): void {
            $payment = FeePayment::query()->create([
                'student_fee_id' => $studentFee->id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $request->user()->id,
            ]);

            $studentFee->refreshPaymentTotals();

            AuditLogger::log(
                $request->user(),
                'fee.payment',
                $studentFee,
                null,
                ['amount' => $payment->amount, 'payment_id' => $payment->id],
                $request
            );
        });

        $studentFee->load(['student', 'feeType', 'payments']);

        return new StudentFeeResource($studentFee);
    }

    public function applyDiscount(Request $request, StudentFee $studentFee): StudentFeeResource
    {
        $data = $request->validate([
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:scholarship,waiver,partial,other'],
            'discount_note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['discount_amount'] > (float) $studentFee->amount) {
            abort(422, 'Discount cannot exceed invoice amount.');
        }

        $before = [
            'discount_amount' => $studentFee->discount_amount,
            'discount_type' => $studentFee->discount_type,
        ];

        $studentFee->update([
            'discount_amount' => $data['discount_amount'],
            'discount_type' => $data['discount_type'],
            'discount_note' => $data['discount_note'] ?? null,
        ]);

        $studentFee->refreshPaymentTotals();

        AuditLogger::log(
            $request->user(),
            'fee.discount',
            $studentFee,
            $before,
            $data,
            $request
        );
        $studentFee->load(['student', 'feeType', 'payments']);

        return new StudentFeeResource($studentFee);
    }
}
