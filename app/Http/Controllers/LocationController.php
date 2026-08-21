<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LocationController extends Controller
{
    public function index(FilterLocationRequest $request): AnonymousResourceCollection
    {
        $locations = Location::query()
            ->byName($request->name)
            ->byType($request->type)
            ->byDimension($request->dimension)
            ->with('residents')
            ->paginate(20);

        return LocationResource::collection($locations);
    }

    public function show(int $id): LocationResource
    {
        $location = Location::with('residents')
            ->findOrFail($id);

        return new LocationResource($location);
    }
}
