<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

class OpdController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_opds')->only('index');
    }

    public function index()
    {
        return Inertia::render('Master/Opd');
    }
}
