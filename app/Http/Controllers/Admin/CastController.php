<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cast;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class CastController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ?: 5;

        return Inertia::render('Casts/Index', [
            'casts' => Cast::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => $request->only(['search', 'perPage'])
        ]);
    }

    public function store(Request $request)
    {
        $cast = Cast::where('tmdb_id', $request->input('castTMDBId'))->first();
        if ($cast) {
            return Redirect::back()->with('flash.banner', 'Cast Exists.');
        }

        $tmdb_cast = Http::asJson()->get(config('services.tmdb.endpoint').'person/' . $request->input('castTMDBId') . '?api_key='. config('services.tmdb.secret') .'&language=en-US');

        if ($tmdb_cast->successful()) {
            Cast::create([
                'tmdb_id' => $tmdb_cast['id'],
                'name'    => $tmdb_cast['name'],
                //'slug'    => Str::slug($tmdb_cast['name']),
                'poster_path' => $tmdb_cast['profile_path']
            ]); 
            return Redirect::back()->with('flash.banner', 'Cast created.');
        }
        
        return Redirect::back()->with('flash.banner', 'Api error.');
    }

    public function edit(Cast $cast)
    {
        return Inertia::render('Casts/Edit', ['cast' => $cast]);
    }

    public function update(Request $request, Cast $cast)
    {
        $cast->update($request->validate([
            'name' => 'required',
            'poster_path' => 'required'
        ]));

        return Redirect::route('admin.casts.index')->with('flash.banner', 'Cast Updated.');
    }

    public function destroy(Cast $cast)
    {
        $cast->delete();
        return Redirect::back()->with('flash.banner', 'Cast Deleted.');
    }
}
