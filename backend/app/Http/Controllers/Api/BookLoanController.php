<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookLoanController extends Controller
{
    public function index()
    {
        $loans = BookLoan::query()->with(['book', 'student'])->latest()->paginate(20);

        return response()->json(['data' => $loans->items(), 'meta' => [
            'current_page' => $loans->currentPage(),
            'last_page' => $loans->lastPage(),
        ]]);
    }

    public function issue(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'student_id' => ['required', 'exists:students,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $book = Book::query()->lockForUpdate()->findOrFail($data['book_id']);
            if ($book->available_copies < 1) {
                return response()->json(['message' => 'No copies available.'], 422);
            }

            $book->decrement('available_copies');
            $loan = BookLoan::query()->create([
                ...$data,
                'issued_date' => now()->toDateString(),
                'status' => 'issued',
                'issued_by' => $request->user()->id,
            ]);

            return response()->json(['data' => $loan->load(['book', 'student'])], 201);
        });
    }

    public function returnBook(Request $request, BookLoan $bookLoan)
    {
        if ($bookLoan->status === 'returned') {
            return response()->json(['message' => 'Already returned.'], 422);
        }

        $finePerDay = (float) $request->input('fine_per_day', 5);

        return DB::transaction(function () use ($bookLoan, $finePerDay) {
            $returned = now()->toDateString();
            $fine = 0;
            if ($returned > $bookLoan->due_date->toDateString()) {
                $days = $bookLoan->due_date->diffInDays($returned);
                $fine = round($days * $finePerDay, 2);
            }

            $bookLoan->update([
                'returned_date' => $returned,
                'status' => 'returned',
                'fine_amount' => $fine,
            ]);
            $bookLoan->book?->increment('available_copies');

            return response()->json(['data' => $bookLoan->fresh(['book', 'student'])]);
        });
    }
}
