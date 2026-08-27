<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'nombre_completo',
        'dni',
        'dni_limpio',
        'expediente',
        'fecha_nacimiento',
        'colonia',
        'telefono',
        'edad',
        'sexo',
        'departamento',
        'municipio',
        'cod_municipio',
    ];

    protected $casts = [
        'edad' => 'integer',
    ];

    /**
     * Asegura que la tabla de pacientes exista e incluya los campos exactos requeridos
     */
    public static function ensureTableExists()
    {
        if (!Schema::hasTable('pacientes')) {
            Schema::create('pacientes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_completo', 255)->nullable();
                $table->string('dni', 50)->nullable()->index('idx_pacientes_dni');
                $table->string('dni_limpio', 50)->nullable()->index('idx_pacientes_dni_limpio');
                $table->string('expediente', 50)->nullable();
                $table->string('fecha_nacimiento', 30)->nullable();
                $table->string('colonia', 150)->nullable();
                $table->string('telefono', 80)->nullable();
                $table->integer('edad')->nullable();
                $table->string('sexo', 20)->nullable();
                $table->string('departamento', 100)->nullable()->default('FRANCISCO MORAZAN');
                $table->string('municipio', 100)->nullable()->default('DISTRITO CENTRAL');
                $table->string('cod_municipio', 50)->nullable()->default('0801');
                $table->timestamps();
            });
        } else {
            // Añadir columnas nuevas en tiempo de ejecución si la tabla ya existe
            if (!Schema::hasColumn('pacientes', 'expediente')) {
                Schema::table('pacientes', function (Blueprint $table) {
                    $table->string('expediente', 50)->nullable()->after('dni_limpio');
                });
            }
            if (!Schema::hasColumn('pacientes', 'telefono')) {
                Schema::table('pacientes', function (Blueprint $table) {
                    $table->string('telefono', 80)->nullable()->after('colonia');
                });
            }
            if (!Schema::hasColumn('pacientes', 'sexo')) {
                Schema::table('pacientes', function (Blueprint $table) {
                    $table->string('sexo', 20)->nullable()->after('edad');
                });
            }
        }
    }
}
