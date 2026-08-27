<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between py-2 px-6">
            <div>
                <h2 class="font-bold text-lg text-slate-900 leading-none mb-1">
                    {{ __('Auditoría de Discrepancias - AT2r-N') }}
                </h2>
                <p class="text-xs text-slate-500 uppercase tracking-wider m-0">
                    {{ $mes }} {{ $ano }} | Registros que no clasifican en el informe principal
                </p>
            </div>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold uppercase hover:bg-slate-300 transition-all">
                Cerrar Ventana
            </button>
        </div>
    </x-slot>

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-xl border border-slate-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800">
                            Se encontraron <span class="text-rose-600">{{ count($discrepancias) }}</span> registros con discrepancias
                        </h3>
                        <div class="text-xs text-slate-500 italic">
                            * Estos registros se incluyen en el TB9 pero son ignorados o excluidos en el AT2r-N.
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200">
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Médico / Profesión</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Edad / Tipo</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Diagnóstico Principal</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-600 uppercase tracking-wider text-rose-600">Razón de Exclusión</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-slate-600 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($discrepancias as $d)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 text-xs text-slate-600 font-medium">{{ $d['fecha'] }}</td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs font-bold text-slate-800">{{ $d['medico'] }}</div>
                                            <div class="text-[10px] text-slate-500 uppercase">{{ $d['prof'] }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 bg-slate-100 rounded text-xs font-bold text-slate-700">
                                                {{ $d['edad'] }} {{ $d['tipo'] == 'A' ? 'Años' : ($d['tipo'] == 'M' ? 'Meses' : 'Días') }}
                                            </span>
                                            <div class="text-[10px] text-slate-400 mt-1 uppercase">Sexo: {{ $d['sexo'] }} | Cond: {{ $d['cond'] }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-600 italic">
                                            {{ $d['diag'] ?: 'Sin diagnóstico' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs font-semibold text-rose-600">
                                            {{ $d['razon'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('ingresos.detalles-medico', ['fecha' => $d['fecha'], 'medico' => $d['medico']]) }}" target="_blank"
                                               class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[10px] font-bold uppercase hover:bg-blue-100 transition-all">
                                                Corregir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-check-circle text-4xl text-emerald-400 mb-3"></i>
                                                <p class="text-slate-500 font-medium">No se encontraron discrepancias. Los datos deberían cuadrar perfectamente.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                    <p class="text-[10px] text-slate-500 text-center">
                        <b>Nota:</b> El informe AT2r-N clasifica estrictamente por rangos de edad. Si un registro tiene una edad nula o un tipo de edad incorrecto, no aparecerá en los totales del informe pero sí en los listados generales como el TB9.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
