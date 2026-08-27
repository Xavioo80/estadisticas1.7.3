<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display customization and typography guide.
     */
    public function typography()
    {
        return view('admin.typography');
    }

    /**
     * Display UI components catalog.
     */
    public function uiElements()
    {
        return view('admin.ui-elements');
    }
}
