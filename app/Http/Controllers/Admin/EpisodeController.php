<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Season;
use App\Models\TvShow;
use App\Models\Episode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class EpisodeController extends Controller
{
    public function index(Request $request, TvShow $tvShow, Season $season)
    {
        $perPage = $request->input('perPage') ?: 5;

        return Inertia::render('TvShows/Seasons/Episodes/Index', [
            'episodes' => Episode::query()
                ->where('season_id', $season->id)
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => $request->only(['search', 'perPage']),
            'tvShow' => $tvShow,
            'season' => $season
        ]);
    }

    public function store(Request $request, TvShow $tvShow, Season $season)
    {
        $episode = $season->episodes()->where('episode_number', $request->input('episodeNumber'))->exists();

        if ($episode) {
            return Redirect::back()->with('flash.banner', 'Episode Exists.');
        }

        $tmdb_episode = Http::asJson()->get(config('services.tmdb.endpoint') . 'tv/' . $tvShow->tmdb_id . '/season/' . $season->season_number . '/episode/'. $request->input('episodeNumber') . '?api_key=' . config('services.tmdb.secret') . '&language=en-US');

        if ($tmdb_episode->successful()) {
            Episode::create([
                'season_id' => $season->id,
                'tmdb_id' => $tmdb_episode['id'],
                'name'    => $tmdb_episode['name'],
                'episode_number' => $tmdb_episode['episode_number'],
                'overview'  => $tmdb_episode['overview'],
            ]);

            return Redirect::back()->with('flash.banner', 'Episode created.');
        } 
        
        return Redirect::back()->with('flash.banner', 'Api error.');
    }

    public function edit(TvShow $tvShow, Season $season, Episode $episode)
    {
        return Inertia::render('TvShows/Seasons/Episodes/Edit', [
            'tvShow' => $tvShow,
            'season' => $season,
            'episode' => $episode
        ]);
    }

    public function update(Request $request, TvShow $tvShow, Season $season, Episode $episode)
    {
        $episode->update($request->validate([
            'name'    => 'required',
            'overview' => 'required',
            'is_public' => 'required'
        ]));

        return Redirect::route('admin.episodes.index', [$tvShow->id, $season->id])->with('flash.banner', 'Episode updated.');

    }

    public function destroy(TvShow $tvShow, Season $season, Episode $episode)
    {
        $episode->delete();

        return Redirect::route('admin.episodes.index', [$tvShow->id, $season->id])
            ->with('flash.banner', 'Episode deleted.')
            ->with('flash.bannerStyle', 'danger');
    }
}
