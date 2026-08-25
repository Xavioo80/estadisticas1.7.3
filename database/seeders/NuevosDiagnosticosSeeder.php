<?php

namespace Database\Seeders;

use App\Models\Diagnostico;
use Illuminate\Database\Seeder;

class NuevosDiagnosticosSeeder extends Seeder
{
    public function run()
    {
        $diagnosticos = [
            ['codigo' => '1001', 'patologia' => 'ANTICONCEPTIVO ORAL COMBINADO', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1002', 'patologia' => 'ANTICONCEPTIVOS ORALES CON PROGESTINA SOLA', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1003', 'patologia' => 'MUJERES QUE SE LES APLICÓ INYECTABLES TRIMESTRAL', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1004', 'patologia' => 'MUJERES QUE SE LES APLICÓ AUTOINYECTABLES TRIMESTRAL', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1005', 'patologia' => 'DIU CON COBRE INSERTADOS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1006', 'patologia' => 'DIU CON LEVONORGESTREL INSERTADOS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1007', 'patologia' => 'INSERCIÓN DE IMPLANTE CON LEVONORGESTREL 5 AÑOS (JADELLE)', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1008', 'patologia' => 'INSERCIÓN DE IMPLANTE CON ETONOGESTREL 3 AÑOS (NXT)', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1009', 'patologia' => 'RETIRO DE IMPLANTE', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1010', 'patologia' => 'RETIRO DE DIU', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1011', 'patologia' => 'DETECCIÓN DE CÁNCER CÉRVICO UTERINO', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1012', 'patologia' => 'CONSEJERÍAS DE PLANIFICACIÓN FAMILIAR BRINDADAS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1013', 'patologia' => 'AQV AMBULATORIA MUJERES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1014', 'patologia' => 'AQV AMBULATORIA HOMBRES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1015', 'patologia' => 'PAE', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1016', 'patologia' => 'CONDONES ENTREGADOS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1017', 'patologia' => 'ABORTO AMBULATORIO', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1018', 'patologia' => 'ATENCIÓN PRENATAL NUEVA EN LAS EDADES DE 10 A 19 AÑOS (ADOLESCENTES)', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1019', 'patologia' => 'ATENCIÓN PRENATAL NUEVA EN LAS PRIMERAS 12 SEMANAS DE GESTACIÓN', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1020', 'patologia' => 'ATENCIÓN PRENATAL NUEVA DESPUÉS DE LAS 12 SEMANAS DE GESTACIÓN', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1021', 'patologia' => 'TOTAL DE ATENCIONES PRENATALES SUBSIGUIENTES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1022', 'patologia' => 'ATENCIONES PUERPERALES ENTRE LOS 3 A 7 DÍAS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1023', 'patologia' => 'ATENCIONES PUERPERALES DESPUÉS DE LOS 7 DÍAS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1024', 'patologia' => 'CONTROLES PUERPERALES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1025', 'patologia' => 'ATENCIONES POR VIOLENCIA SEXUAL', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1026', 'patologia' => 'ATENCION DE ADOLESCENTES DE 10 A 19 AÑOS MUJERES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1027', 'patologia' => 'ATENCION DE ADOLESCENTES DE 10 A 19 AÑOS VARONES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1028', 'patologia' => 'DETECCIÓN DE CASOS PRESUNTIVOS DE TUBERCULOSIS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1029', 'patologia' => 'ATENCIONES BRINDADAS NUEVAS DE DIABETES MELLITUS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1030', 'patologia' => 'ATENCIONES BRINDADAS SUBSIGUIENTES DE DIABETES MELLITUS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1031', 'patologia' => 'ATENCIONES BRINDADAS NUEVAS DE HIPERTENSIÓN ARTERIAL', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1032', 'patologia' => 'ATENCIONES BRINDADAS SUBSIGUIENTES DE HIPERTENSIÓN ARTERIAL', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1033', 'patologia' => 'ATENCIONES BRINDADAS NUEVAS DE ENFERMEDAD RENAL CRÓNICA', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1034', 'patologia' => 'ATENCIONES BRINDADAS SUBSIGUIENTES DE ENFERMEDAD RENAL CRÓNICA', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1035', 'patologia' => 'ATENCIONES BRINDADAS NUEVAS DE CÁNCER CÉRVICO UTERINO', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1036', 'patologia' => 'ATENCIONES BRINDADAS SUBSIGUIENTES DE CÁNCER CÉRVICO UTERINO', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1037', 'patologia' => 'ATENCIONES BRINDADAS NUEVAS DE CÁNCER PRIORIZADOS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1038', 'patologia' => 'CÁNCER PRIORIZADOS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1039', 'patologia' => 'ATENCIONES POR PSICOLOGÍA-PSIQUIATRÍA', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1040', 'patologia' => 'MIGRANTES IRREGULARES', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '1041', 'patologia' => 'MIGRANTES HONDUREÑOS RETORNADOS', 'secundario' => '', 'categoria' => 'AT2-R'],
            ['codigo' => '2001', 'patologia' => 'GARÍFUNA', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2002', 'patologia' => 'NEGRO INGLÉS', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2003', 'patologia' => 'TOLUPÁN', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2004', 'patologia' => 'PECH(PAYA)', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2005', 'patologia' => 'MISQUITO', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2006', 'patologia' => 'NAHOA', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2007', 'patologia' => 'LENCA', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2008', 'patologia' => 'TAWAKA(SUMO)', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2009', 'patologia' => 'MAYA CHORTÍ', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2010', 'patologia' => 'OTRA ETNIA', 'secundario' => '', 'categoria' => 'ETNIA'],
            ['codigo' => '2011', 'patologia' => 'NO SABE/ NINGUNO ( ETNIA)', 'secundario' => '', 'categoria' => 'ETNIA'],
        ];

        foreach ($diagnosticos as $diag) {
            Diagnostico::updateOrCreate(
                ['codigo' => $diag['codigo']],
                $diag
            );
        }
    }
}
