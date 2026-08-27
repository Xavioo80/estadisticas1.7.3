<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display AT1 registration data tables.
     */
    public function tables()
    {
        return view('reports.registrosat1');
    }
}
