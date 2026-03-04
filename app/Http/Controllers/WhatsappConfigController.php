<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use Inertia\Inertia;

class WhatsappConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:'.Permission::VIEW_WHATSAPP_CONFIG->value)->only(['index']);
    }

    public function index()
    {
        return Inertia::render('Master/WhatsappConfig');
    }
}
