<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_rooms')->only('index');
    }

    public function index()
    {
        return Inertia::render('Master/Rooms');
    }
}
