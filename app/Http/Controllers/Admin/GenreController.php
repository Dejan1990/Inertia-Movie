<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class GenreController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ?: 5;

        return Inertia::render('Genres/Index', [
            'genres' => Genre::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('title', 'like', "%{$search}%");
                })
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => $request->only(['search', 'perPage'])
        ]);
    }

    public function store()
    {
        $tmdb_genres = Http::get(config('services.tmdb.endpoint') . 'genre/movie/list?api_key=' . config('services.tmdb.secret') . '&language=en-US');

        if ($tmdb_genres->successful()) {
            $tmdb_genres_json = $tmdb_genres->json();
            foreach($tmdb_genres_json as $single_tmdb_genre){
                foreach ($single_tmdb_genre as $tgenre){
                    $genre = Genre::where('tmdb_id', $tgenre['id'])->first();
                    if (!$genre) {
                        Genre::create([
                            'tmdb_id' => $tgenre['id'],
                            'title' => $tgenre['name']
                        ]);
                    }
                }
            }
            return Redirect::back()->with('flash.banner', 'genre created.');
        }
        return Redirect::back()->with('flash.banner', 'Api Error.');
    }

    public function edit(Genre $genre)
    {
        return Inertia::render('Genres/Edit', ['genre' => $genre]);
    }

    public function update(Request $request, Genre $genre)
    {
        $genre->update($request->validate([
            'title' => 'required'
        ]));

        return Redirect::route('admin.genres.index')->with('flash.banner', 'Genre updated.');
    }

    public function destroy(Genre $genre)
    {
        $genre->delete();

        return Redirect::route('admin.genres.index')
            ->with('flash.banner', 'Genre deleted.')
            ->with('flash.bannerStyle', 'danger');
    }
}
