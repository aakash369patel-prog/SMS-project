<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParentGuardianRequest;
use App\Http\Requests\UpdateParentGuardianRequest;
use App\Http\Resources\ParentGuardianResource;
use App\Models\ParentGuardian;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ParentGuardianController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $parents = ParentGuardian::query()
            ->with('students')
            ->latest()
            ->paginate(15);

        return ParentGuardianResource::collection($parents);
    }

    public function store(StoreParentGuardianRequest $request): ParentGuardianResource
    {
        $data = $request->validated();

        $parent = DB::transaction(function () use ($data) {
            $userId = null;
            if (! empty($data['password'])) {
                $user = User::query()->create([
                    'name' => trim("{$data['first_name']} {$data['last_name']}"),
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => 'parent',
                ]);
                $userId = $user->id;
            }

            $parent = ParentGuardian::query()->create([
                'user_id' => $userId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            $this->syncStudents($parent, $data['students'] ?? []);

            return $parent;
        });

        $parent->load('students');

        return new ParentGuardianResource($parent);
    }

    public function show(ParentGuardian $parentGuardian): ParentGuardianResource
    {
        $parentGuardian->load('students');

        return new ParentGuardianResource($parentGuardian);
    }

    public function update(UpdateParentGuardianRequest $request, ParentGuardian $parentGuardian): ParentGuardianResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $parentGuardian): void {
            $parentGuardian->update(collect($data)->only([
                'first_name', 'last_name', 'email', 'phone', 'address',
            ])->filter(fn ($v) => $v !== null)->all());

            if (! empty($data['password'])) {
                if ($parentGuardian->user_id) {
                    $parentGuardian->user?->update(['password' => Hash::make($data['password'])]);
                } else {
                    $user = User::query()->create([
                        'name' => $parentGuardian->full_name,
                        'email' => $parentGuardian->email,
                        'password' => Hash::make($data['password']),
                        'role' => 'parent',
                    ]);
                    $parentGuardian->update(['user_id' => $user->id]);
                }
            }

            if (array_key_exists('students', $data)) {
                $this->syncStudents($parentGuardian, $data['students'] ?? []);
            }
        });

        $parentGuardian->load('students');

        return new ParentGuardianResource($parentGuardian);
    }

    public function destroy(ParentGuardian $parentGuardian): \Illuminate\Http\JsonResponse
    {
        $userId = $parentGuardian->user_id;
        $parentGuardian->delete();
        if ($userId) {
            User::query()->whereKey($userId)->delete();
        }

        return response()->json(['message' => 'Parent deleted successfully.']);
    }

    /** @param array<int, array{student_id:int, relation:string}> $students */
    private function syncStudents(ParentGuardian $parent, array $students): void
    {
        $sync = [];
        foreach ($students as $row) {
            $sync[$row['student_id']] = ['relation' => $row['relation']];
        }
        $parent->students()->sync($sync);
    }
}
