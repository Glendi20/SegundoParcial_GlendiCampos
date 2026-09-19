<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CalendarioController extends Controller
{
    public function index(): View
    {
        return view('calendario.index');
    }
}
