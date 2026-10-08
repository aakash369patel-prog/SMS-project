<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentFileController extends Controller
{
    public function uploadPhoto(Request $request, Student $student): StudentResource
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $path = $request->file('photo')->store('students/photos', 'public');
        $student->update(['photo_path' => $path]);

        return new StudentResource($student->load('schoolClass'));
    }

    public function listDocuments(Student $student)
    {
        return response()->json([
            'data' => $student->documents()->with('uploader')->latest()->get(),
        ]);
    }

    public function uploadDocument(Request $request, Student $student)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'document' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('document');
        $path = $file->store('students/documents', 'public');

        $doc = StudentDocument::query()->create([
            'student_id' => $student->id,
            'title' => $data['title'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $doc->load('uploader')], 201);
    }

    public function deleteDocument(Student $student, StudentDocument $document)
    {
        if ($document->student_id !== $student->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Document deleted.']);
    }
}
