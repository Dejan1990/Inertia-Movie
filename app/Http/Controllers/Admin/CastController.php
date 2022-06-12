<?php

namespace App\Http\Controllers\Admin;

use App\Models\Cast;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
