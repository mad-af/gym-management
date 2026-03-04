<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_users')->only('index');
    }

    public function index()
    {
        return Inertia::render('Master/Users');
    }
}
