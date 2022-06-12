<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('perPage') ?: 5;
        return Inertia::render('Tags/Index', [
            'tags' => Tag::query()
                ->when($request->input('search'), function($query, $search) {
                    $query->where('tag_name', 'like', "%{$search}%");
                })
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => $request->only(['search', 'perPage'])               
        ]);
    }

    public function create()
    {
        return Inertia::render('Tags/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tagName' => ['required', 'string', 'min:2', 'max:50']
        ]);

        Tag::create([
            'tag_name' => $request->input('tagName'),
            'slug' => Str::slug($request->input('tagName'))
        ]);

        return Redirect::route('admin.tags.index')->with('flash.banner', 'Tag Created.');
    }

    public function edit(Tag $tag)
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag
        ]);
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'tagName' => ['required', 'string', 'min:2', 'max:50']
        ]);

        $tag->update([
            'tag_name' => $request->input('tagName'),
            'slug' => Str::slug($request->input('tagName'))
        ]);

        return Redirect::route('admin.tags.index')->with('flash.banner', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return Redirect::route('admin.tags.index')
            ->with('flash.banner', 'Tag deleted.')
            ->with('flash.bannerStyle', 'danger');
    }
}
