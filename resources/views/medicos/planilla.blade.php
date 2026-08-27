<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center print:hidden">
            <h2 class="font-black text-2xl text-slate-900 leading-tight">
                {{ __('Planilla Médica') }}
            </h2>
            <div class="mt-2 sm:mt-0 px-4 py-2 bg-indigo-50 rounded-lg border border-indigo-100 shadow-sm flex items-center">
                <i class="fas fa-calendar-alt text-indigo-500 mr-2"></i>
                <span class="font-semibold text-indigo-800">{{ __('Fecha:') }} {{ $fecha }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8 max-w-[100vw] sm:max-w-7xl mx-auto print:py-0 print:px-0 print:max-w-none">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:shadow-none print:rounded-none">
            
            <!-- Print Header (Only visible when printing) -->
            <div class="hidden print:block text-center mb-6">
                <h1 class="text-2xl font-bold uppercase">{{ __('Planilla Médica') }}</h1>
                <h4 class="text-lg">{{ __('Fecha:') }} {{ $fecha }}</h4>
            </div>

            <div class="p-4 sm:p-6 bg-white border-b border-gray-200 print:p-0 print:border-none">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 w-full mb-4 tabular-nums">
                        <thead class="bg-slate-800 print:bg-slate-200">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('No.') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('Código') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('Nombre') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('Jornada') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('Especialidad') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('Modalidad') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800 select-none">{{ __('Estado') }}</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-white print:text-slate-800 uppercase tracking-wider print:border print:border-slate-800">{{ __('Fecha Ingreso') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($medicos as $index => $medico)
                            <tr class="hover:bg-slate-50 transition-colors duration-150">
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-500 print:text-black print:border print:border-slate-800">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-slate-900 print:text-black print:border print:border-slate-800">{{ $medico->COD_MED }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-700 print:text-black print:border print:border-slate-800">{{ $medico->NOM_MED }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-500 print:text-black print:border print:border-slate-800">{{ $medico->JORNADA }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-500 print:text-black print:border print:border-slate-800">{{ $medico->ESPECIALIDAD }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-500 print:text-black print:border print:border-slate-800">{{ $medico->MODALIDAD }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm print:text-black print:border print:border-slate-800">
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $medico->estado == 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} print:bg-transparent print:p-0">
                                        {{ ucfirst($medico->estado) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap text-sm text-slate-500 print:text-black print:border print:border-slate-800">{{ $medico->FECHA_INGRESO->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-6 flex justify-end print:hidden">
                    <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-800 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                        <i class="fas fa-print mr-2"></i> {{ __('Imprimir Planilla') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Inject Print Styles directly into layout -->
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .py-12, .py-12 * {
                visibility: visible;
            }
            .py-12 {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none !important;
            }
            .print\:hidden {
                display: none !important;
            }
            .print\:block {
                display: block !important;
            }
            /* Add table borders for print */
            table {
                border-collapse: collapse !important;
                width: 100% !important;
            }
            th, td {
                border: 1px solid #000 !important;
                padding: 4px 8px !important;
                color: #000 !important;
            }
            th {
                background-color: #f1f5f9 !important; /* light gray header */
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</x-app-layout>
