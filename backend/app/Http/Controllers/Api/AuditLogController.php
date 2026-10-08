<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:40'],
            'auditable_type' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $query = AuditLog::query()->with('user')->latest();

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (! empty($filters['auditable_type'])) {
            $query->where('auditable_type', 'like', '%'.$filters['auditable_type'].'%');
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return response()->json($query->paginate(30));
    }
}
