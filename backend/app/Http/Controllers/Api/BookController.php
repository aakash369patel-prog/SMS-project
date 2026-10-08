<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return response()->json(Book::query()->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'isbn' => ['nullable', 'string', 'max:30', 'unique:books,isbn'],
            'author' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:80'],
            'total_copies' => ['required', 'integer', 'min:1'],
        ]);
        $data['available_copies'] = $data['total_copies'];

        return response()->json(['data' => Book::query()->create($data)], 201);
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'isbn' => ['nullable', 'string', 'max:30', 'unique:books,isbn,'.$book->id],
            'author' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:80'],
            'total_copies' => ['sometimes', 'integer', 'min:1'],
        ]);
        if (isset($data['total_copies'])) {
            $issued = $book->total_copies - $book->available_copies;
            $data['available_copies'] = max(0, $data['total_copies'] - $issued);
        }
        $book->update($data);

        return response()->json(['data' => $book]);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(['message' => 'Book deleted.']);
    }
}
