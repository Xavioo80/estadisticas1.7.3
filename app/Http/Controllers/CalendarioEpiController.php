<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Throwable;

class CalendarioEpiController extends Controller
{
    private function getDirectoryPath()
    {
        $dir = public_path('img/Calendario_Epi');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    private function getCalendarioFile()
    {
        $dir = $this->getDirectoryPath();
        $files = File::files($dir);

        if (!empty($files)) {
            $file = $files[0];
            $ext = strtolower($file->getExtension());
            $filename = $file->getFilename();
            $url = asset('img/Calendario_Epi/' . $filename);
            $type = ($ext === 'pdf') ? 'pdf' : 'image';

            return [
                'exists' => true,
                'path' => $file->getPathname(),
                'filename' => $filename,
                'url' => $url,
                'type' => $type,
                'extension' => $ext,
                'size' => number_format($file->getSize() / 1024, 1) . ' KB',
                'updated_at' => date('d/m/Y h:i A', $file->getMTime()),
            ];
        }

        return [
            'exists' => false,
            'url' => null,
            'type' => null,
        ];
    }

    public function index()
    {
        $calendario = $this->getCalendarioFile();
        return view('calendario-epi', compact('calendario'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:jpeg,jpg,png,webp,gif,pdf|max:20480',
        ], [
            'archivo.required' => 'Debe seleccionar un archivo para subir.',
            'archivo.mimes' => 'Formato no permitido. Solo se aceptan imágenes (PNG, JPG, WEBP) o PDF.',
            'archivo.max' => 'El archivo no debe pesar más de 20MB.',
        ]);

        try {
            $dir = $this->getDirectoryPath();

            // Eliminar archivos anteriores para que solo quede el nuevo sobreescrito
            $existingFiles = File::files($dir);
            foreach ($existingFiles as $file) {
                File::delete($file->getPathname());
            }

            $uploadedFile = $request->file('archivo');
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            $filename = 'calendario_epi.' . $extension;

            $uploadedFile->move($dir, $filename);

            return redirect()->route('calendario_epi')
                ->with('success', '¡Calendario Epidemiológico actualizado con éxito!');

        } catch (Throwable $e) {
            return redirect()->route('calendario_epi')
                ->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
    }

    public function download()
    {
        $calendario = $this->getCalendarioFile();

        if ($calendario['exists'] && File::exists($calendario['path'])) {
            return response()->download($calendario['path'], 'Calendario_Epidemiologico.' . $calendario['extension']);
        }

        return redirect()->route('calendario_epi')->with('error', 'No existe ningún calendario cargado para descargar.');
    }
}
