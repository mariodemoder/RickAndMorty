<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\CharacterResource;
use App\Models\CharacterFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $favorites = CharacterFavorite::where('user_id', $request->user()->id)
            ->with('character')
            ->paginate(20);

        return CharacterResource::collection(
            $favorites->pluck('character')
        );
    }

    public function store(FavoriteRequest $request): JsonResponse
    {
        $favorite = CharacterFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'character_id' => $request->character_id,
        ]);

        return response()->json([
            'message' => 'Favorite added successfully.',
            'data' => new CharacterResource($favorite->character),
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = CharacterFavorite::where('user_id', $request->user()->id)
            ->where('character_id', $id)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'error' => [
                    'message' => 'Favorite not found.',
                    'status' => 404,
                ],
            ], 404);
        }

        return response()->json([
            'message' => 'Favorite removed successfully.',
        ]);
    }
}
