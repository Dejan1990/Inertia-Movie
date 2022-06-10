<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tag;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
