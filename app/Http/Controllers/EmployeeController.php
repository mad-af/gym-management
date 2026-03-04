<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_employees')->only('index');
    }

    public function index()
    {
        return Inertia::render('Master/Employees');
    }
}
