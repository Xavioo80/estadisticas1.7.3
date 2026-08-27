<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Throwable;

class PruebaConsultaController extends Controller
{
    private $daemonUrl = 'http://127.0.0.1:9099';

    public function index()
    {
        return view('prueba_consulta');
    }

    public function buscar(Request $request)
    {
        $identidadInput = trim($request->input('identidad', ''));

        if (empty($identidadInput)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingrese un número de identidad válido'
            ], 400);
        }

        // 1. Intentar consultar mediante el Demonio en segundo plano (Velocidad < 1s)
        try {
            $ping = @Http::timeout(1)->get("{$this->daemonUrl}/ping");

            if (!$ping || !$ping->successful()) {
                // El demonio no está corriendo, lanzarlo en segundo plano
                $this->startDaemon();
                // Breve pausa para dar tiempo a que levante la sesión
                usleep(2000000); // 2.0s
            }

            // Consultar al demonio ya inicializado
            $response = Http::timeout(12)->get("{$this->daemonUrl}/buscar", [
                'identidad' => $identidadInput
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

        } catch (Throwable $e) {
            \Log::warning('Daemon call failed, fallback to script: ' . $e->getMessage());
        }

        // 2. Fallback de respaldo (Lanza script ejecutable directamente)
        try {
            $scriptPath = base_path('app/Scripts/fetch_sesal_patient.cjs');
            $nodePath   = 'C:\\Program Files\\nodejs\\node.exe';

            $env = [
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
                'SystemDrive' => getenv('SystemDrive') ?: 'C:',
                'TEMP' => getenv('TEMP') ?: 'C:\\Windows\\Temp',
                'TMP' => getenv('TMP') ?: 'C:\\Windows\\Temp',
                'PATH' => getenv('PATH') ?: 'C:\\Program Files\\nodejs;C:\\Windows\\system32;C:\\Windows',
                'LOCALAPPDATA' => getenv('LOCALAPPDATA') ?: 'C:\\Users\\CISSM\\AppData\\Local',
                'APPDATA' => getenv('APPDATA') ?: 'C:\\Users\\CISSM\\AppData\\Roaming',
                'USERPROFILE' => getenv('USERPROFILE') ?: 'C:\\Users\\CISSM',
            ];

            if (isset($_SERVER) && is_array($_SERVER)) {
                $env = array_merge($_SERVER, $env);
            }

            $process = new Process([$nodePath, $scriptPath, '--identidad=' . $identidadInput], null, $env);
            $process->setTimeout(30);
            $process->run();

            $output = trim($process->getOutput());
            $data   = json_decode($output, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return response()->json($data);
            }

            return response()->json([
                'success' => false,
                'message' => 'Respuesta inesperada del motor de búsqueda',
                'stdout'  => $output,
            ], 500);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    private function startDaemon()
    {
        try {
            $nodePath   = 'C:\\Program Files\\nodejs\\node.exe';
            $daemonPath = base_path('app/Scripts/sesal_daemon.cjs');

            $cmd = "start /B \"\" \"{$nodePath}\" \"{$daemonPath}\"";
            pclose(popen($cmd, "r"));
        } catch (Throwable $e) {
            \Log::error('Error starting daemon: ' . $e->getMessage());
        }
    }
}
