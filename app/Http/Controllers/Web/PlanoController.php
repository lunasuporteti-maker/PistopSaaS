<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PlanoController extends Controller
{
    public function index(): View
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;

        return view('pitstop.assine', compact('tenant'));
    }
}
