<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TvShowController extends Controller
{
    public function index()
    {
        return Inertia::render('TvShows/Index');
    }
}
