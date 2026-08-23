<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterCharacterRequest;
use App\Http\Resources\CharacterResource;
use App\Models\Character;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CharacterController extends Controller
{
    public function index(FilterCharacterRequest $request): AnonymousResourceCollection
    {
        $characters = Character::query()
            ->byName($request->name)
            ->byStatus($request->status)
            ->bySpecies($request->species)
            ->byGender($request->gender)
            ->with(['originLocation', 'currentLocation'])
            ->paginate(20);

        return CharacterResource::collection($characters);
    }

    public function show(int $id): CharacterResource
    {
        $character = Character::with(['originLocation', 'currentLocation', 'episodes'])
            ->findOrFail($id);

        return new CharacterResource($character);
    }
}
