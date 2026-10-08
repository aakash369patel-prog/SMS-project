<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeTypeRequest;
use App\Http\Requests\UpdateFeeTypeRequest;
use App\Http\Resources\FeeTypeResource;
use App\Models\FeeType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeeTypeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return FeeTypeResource::collection(FeeType::query()->latest()->paginate(15));
    }

    public function store(StoreFeeTypeRequest $request): FeeTypeResource
    {
        return new FeeTypeResource(FeeType::query()->create($request->validated()));
    }

    public function show(FeeType $feeType): FeeTypeResource
    {
        return new FeeTypeResource($feeType);
    }

    public function update(UpdateFeeTypeRequest $request, FeeType $feeType): FeeTypeResource
    {
        $feeType->update($request->validated());

        return new FeeTypeResource($feeType);
    }

    public function destroy(FeeType $feeType): \Illuminate\Http\JsonResponse
    {
        $feeType->delete();

        return response()->json(['message' => 'Fee type deleted successfully.']);
    }
}
