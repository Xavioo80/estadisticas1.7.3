<?php

namespace App\Http\Controllers;

use App\Models\Colonia;
use Illuminate\Http\Request;

class ColoniaController extends Controller
{
    public function index(Request $request)
    {
        $colonias = Colonia::orderBy('COLONIA')->get();
        
        if ($request->input('format') === 'json') {
            return response()->json($colonias);
        }

        return view('colonias.index', compact('colonias'));
    }

    public function create()
    {
        return view('colonias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'COD_COL' => 'required|integer|unique:colonias',
            'COLONIA' => 'required|string|max:100'
        ]);

        Colonia::create($request->all());

        return redirect()->route('colonias.index')
            ->with('success', 'Colonia creada exitosamente.');
    }

    public function show(Colonia $colonia)
    {
        return view('colonias.show', compact('colonia'));
    }

    public function edit(Colonia $colonia)
    {
        return view('colonias.edit', compact('colonia'));
    }

    public function update(Request $request, Colonia $colonia)
    {
        $request->validate([
            'COD_COL' => 'required|integer|unique:colonias,COD_COL,'.$colonia->id,
            'COLONIA' => 'required|string|max:100'
        ]);

        $colonia->update($request->all());

        return redirect()->route('colonias.index')
            ->with('success', 'Colonia actualizada exitosamente.');
    }

    public function destroy(Colonia $colonia)
    {
        $colonia->delete();

        return redirect()->route('colonias.index')
            ->with('success', 'Colonia eliminada exitosamente.');
    }
}
