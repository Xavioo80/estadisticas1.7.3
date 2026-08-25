<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the main analytics dashboard.
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * Display the epidemiologic surveillance dashboard (Visitas).
     */
    public function visits()
    {
        return view('dashboard.visits');
    }

    /**
     * Display diagnostic statistics & KPIs (Gráficos).
     */
    public function charts()
    {
        return view('dashboard.charts');
    }
}
