<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterEpisodeRequest;
use App\Http\Resources\EpisodeResource;
use App\Models\Episode;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EpisodeController extends Controller
{
    public function index(FilterEpisodeRequest $request): AnonymousResourceCollection
    {
        $episodes = Episode::query()
            ->byName($request->name)
            ->byEpisodeCode($request->episode)
            ->with('characters')
            ->paginate(20);

        return EpisodeResource::collection($episodes);
    }

    public function show(int $id): EpisodeResource
    {
        $episode = Episode::with('characters')
            ->findOrFail($id);

        return new EpisodeResource($episode);
    }
}
