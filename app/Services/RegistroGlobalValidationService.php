<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class RegistroGlobalValidationService
{
    /**
     * Validar datos de un registro individual
     */
    public function validateRegistroData(array $data, int $rowNumber = null): array
    {
        $rules = $this->getValidationRules();
        $messages = $this->getValidationMessages();
        
        $validator = Validator::make($data, $rules, $messages);
        
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $context = $rowNumber ? " en fila {$rowNumber}" : "";
            
            return [
                'valid' => false,
                'errors' => $errors,
                'message' => 'Errores de validación' . $context . ': ' . implode(', ', $errors)
            ];
        }
        
        return [
            'valid' => true,
            'data' => $this->processData($data),
            'errors' => []
        ];
    }
    
    /**
     * Validar y procesar múltiples registros
     */
    public function validateMultipleRegistros(array $registros): array
    {
        $validated = [];
        $errors = [];
        $warnings = [];
        
        foreach ($registros as $index => $registro) {
            $result = $this->validateRegistroData($registro, $index + 1);
            
            if ($result['valid']) {
                $validated[] = $result['data'];
            } else {
                $errors[] = [
                    'row' => $index + 1,
                    'errors' => $result['errors'],
                    'data' => $registro
                ];
            }
            
            // Verificar advertencias
            $warningsResult = $this->checkWarnings($registro, $index + 1);
            if (!empty($warningsResult)) {
                $warnings = array_merge($warnings, $warningsResult);
            }
        }
        
        return [
            'validated' => $validated,
            'errors' => $errors,
            'warnings' => $warnings,
            'success_rate' => count($registros) > 0 ? round((count($validated) / count($registros)) * 100, 2) : 0
        ];
    }
    
    /**
     * Validar actualización de registro
     */
    public function validateUpdateData(array $data): array
    {
        $rules = $this->getUpdateRules();
        $messages = $this->getValidationMessages();
        
        $validator = Validator::make($data, $rules, $messages);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->all()
            ];
        }
        
        return [
            'valid' => true,
            'data' => $this->processData($data)
        ];
    }
    
    /**
     * Obtener reglas de validación para creación
     */
    private function getValidationRules(): array
    {
        return [
            'ano' => 'nullable|integer|min:1900|max:2100',
            'mes' => 'nullable|string|max:20',
            'numero' => 'nullable|string|max:50',
            'cm' => 'nullable|string|max:50',
            'medico' => 'nullable|string|max:255',
            'prof' => 'nullable|string|max:100',
            'fecha' => 'nullable|date',
            'se' => 'nullable|integer|min:0|max:53',
            'exp' => 'nullable|string|max:50',
            'sexo' => 'nullable|in:M,F,MASCULINO,FEMENINO,H,HOMBRE,MUJER',
            'edad' => 'nullable|integer|min:0|max:150',
            'tipo' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'rango' => 'nullable|string|max:100',
            'rango_2' => 'nullable|string|max:100',
            'rango_3' => 'nullable|string|max:100',
            'rango_4' => 'nullable|string|max:100',
            'rango_5' => 'nullable|string|max:100',
            'cond' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_col' => 'nullable|integer',
            'colonia' => 'nullable|string|max:255',
            'cod_1' => 'nullable|string|max:20',
            'diagnostico_1' => 'nullable|string|max:255',
            'cond_1' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'sg' => 'nullable|string|max:50',
            'cod_2' => 'nullable|string|max:20',
            'diagnostico_2' => 'nullable|string|max:255',
            'cond_2' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_3' => 'nullable|string|max:20',
            'diagnostico_3' => 'nullable|string|max:255',
            'cond_3' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_4' => 'nullable|string|max:20',
            'diagnostico_4' => 'nullable|string|max:255',
            'cond_4' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_5' => 'nullable|string|max:20',
            'diagnostico_5' => 'nullable|string|max:255',
            'cond_5' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_6' => 'nullable|string|max:20',
            'diagnostico_6' => 'nullable|string|max:255',
            'cond_6' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_7' => 'nullable|string|max:20',
            'diagnostico_7' => 'nullable|string|max:255',
            'cond_7' => 'nullable|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'referido_a' => 'nullable|string|max:255',
            'referido_de' => 'nullable|string|max:255',
            'pg_emb' => 'nullable|string|max:50',
            'jornada' => 'nullable|in:MATUTINA,VESPERTINA,FIN DE SEMANA',
            'sm' => 'nullable|string|max:50',
            'sg2' => 'nullable|string|max:50',
        ];
    }
    
    /**
     * Obtener reglas de validación para actualización
     */
    private function getUpdateRules(): array
    {
        return [
            'ano' => 'sometimes|integer|min:1900|max:2100',
            'mes' => 'sometimes|string|max:20',
            'numero' => 'sometimes|string|max:50',
            'cm' => 'sometimes|string|max:50',
            'medico' => 'sometimes|string|max:255',
            'prof' => 'sometimes|string|max:100',
            'fecha' => 'sometimes|date',
            'se' => 'sometimes|integer|min:0|max:53',
            'exp' => 'sometimes|string|max:50',
            'sexo' => 'sometimes|in:M,F,MASCULINO,FEMENINO,H,HOMBRE,MUJER',
            'edad' => 'sometimes|integer|min:0|max:150',
            'tipo' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'rango' => 'sometimes|string|max:100',
            'rango_2' => 'sometimes|string|max:100',
            'rango_3' => 'sometimes|string|max:100',
            'rango_4' => 'sometimes|string|max:100',
            'rango_5' => 'sometimes|string|max:100',
            'cond' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_col' => 'sometimes|integer',
            'colonia' => 'sometimes|string|max:255',
            'cod_1' => 'sometimes|string|max:20',
            'diagnostico_1' => 'sometimes|string|max:255',
            'cond_1' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'sg' => 'sometimes|string|max:50',
            'cod_2' => 'sometimes|string|max:20',
            'diagnostico_2' => 'sometimes|string|max:255',
            'cond_2' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_3' => 'sometimes|string|max:20',
            'diagnostico_3' => 'sometimes|string|max:255',
            'cond_3' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_4' => 'sometimes|string|max:20',
            'diagnostico_4' => 'sometimes|string|max:255',
            'cond_4' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_5' => 'sometimes|string|max:20',
            'diagnostico_5' => 'sometimes|string|max:255',
            'cond_5' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_6' => 'sometimes|string|max:20',
            'diagnostico_6' => 'sometimes|string|max:255',
            'cond_6' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'cod_7' => 'sometimes|string|max:20',
            'diagnostico_7' => 'sometimes|string|max:255',
            'cond_7' => 'sometimes|in:A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z',
            'referido_a' => 'sometimes|string|max:255',
            'referido_de' => 'sometimes|string|max:255',
            'pg_emb' => 'sometimes|string|max:50',
            'jornada' => 'sometimes|in:MATUTINA,VESPERTINA,FIN DE SEMANA',
            'sm' => 'sometimes|string|max:50',
            'sg2' => 'sometimes|string|max:50',
        ];
    }
    
    /**
     * Obtener mensajes de validación personalizados
     */
    private function getValidationMessages(): array
    {
        return [
            'ano.integer' => 'El año debe ser un número válido',
            'ano.min' => 'El año no puede ser menor a 1900',
            'ano.max' => 'El año no puede ser mayor a 2100',
            'mes.string' => 'El mes debe ser un texto válido',
            'mes.max' => 'El mes no puede tener más de 20 caracteres',
            'edad.integer' => 'La edad debe ser un número válido',
            'edad.min' => 'La edad no puede ser negativa',
            'edad.max' => 'La edad no puede ser mayor a 150 años',
            'sexo.in' => 'El sexo debe ser M, F o valores válidos',
            'jornada.in' => 'La jornada debe ser MATUTINA, VESPERTINA o FIN DE SEMANA',
            'fecha.date' => 'La fecha debe tener un formato válido',
        ];
    }
    
    /**
     * Procesar datos según tipo de campo
     */
    private function processData(array $data): array
    {
        $processed = [];
        
        foreach ($data as $key => $value) {
            if ($value === '' || $value === null) {
                $processed[$key] = null;
                continue;
            }
            
            switch ($key) {
                case 'fecha':
                    $processed[$key] = Carbon::parse($value)->format('Y-m-d');
                    break;
                    
                case 'ano':
                case 'edad':
                case 'se':
                case 'cod_col':
                    $processed[$key] = (int)$value;
                    break;
                    
                case 'cod_1':
                case 'cod_2':
                case 'cod_3':
                case 'cod_4':
                case 'cod_5':
                case 'cod_6':
                case 'cod_7':
                    $processed[$key] = $this->getNumericValue($value);
                    break;
                    
                default:
                    // Convertir a mayúsculas para campos de texto
                    $processed[$key] = mb_strtoupper((string)$value, 'UTF-8');
                    break;
            }
        }
        
        return $processed;
    }
    
    /**
     * Convertir a valor numérico
     */
    private function getNumericValue($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        if (preg_match('/\d+/', (string)$value, $matches)) {
            return (int)$matches[0];
        }
        
        return null;
    }
    
    /**
     * Verificar advertencias en los datos
     */
    private function checkWarnings(array $data, int $rowNumber): array
    {
        $warnings = [];
        
        // Campos importantes vacíos
        if (empty($data['medico'])) {
            $warnings[] = [
                'row' => $rowNumber,
                'warning' => 'Campo médico vacío'
            ];
        }
        
        if (empty($data['sexo'])) {
            $warnings[] = [
                'row' => $rowNumber,
                'warning' => 'Campo sexo vacío'
            ];
        }
        
        if (empty($data['edad'])) {
            $warnings[] = [
                'row' => $rowNumber,
                'warning' => 'Campo edad vacío'
            ];
        }
        
        // Validar rango de edad
        if (!empty($data['edad']) && $data['edad'] > 100) {
            $warnings[] = [
                'row' => $rowNumber,
                'warning' => 'Edad mayor a 100 años detectada'
            ];
        }
        
        return $warnings;
    }
}
