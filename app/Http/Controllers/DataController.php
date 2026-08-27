<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Display data entry forms.
     */
    public function forms()
    {
        return view('data.forms');
    }
}
