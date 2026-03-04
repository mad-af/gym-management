<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;

class AssetCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_asset_categories')->only('index');
    }

    public function index()
    {
        return Inertia::render('Master/AssetCategories');
    }
}
