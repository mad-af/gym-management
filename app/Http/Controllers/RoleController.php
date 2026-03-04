<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_roles')->only('index');
    }

    public function index()
    {
        return Inertia::render('Master/Roles');
    }
}
