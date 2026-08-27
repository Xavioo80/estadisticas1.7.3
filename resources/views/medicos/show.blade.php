<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Detalles del Médico') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-6 flex justify-between items-center bg-indigo-600 rounded-t-lg p-4 -mx-4 sm:-mx-8 -mt-4 sm:-mt-8 mb-8">
                    <h3 class="text-xl font-bold text-white">{{ $medico->NOM_MED }}</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Column 1 -->
                    <div class="space-y-4">
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Código') }}</span>
                            <span class="text-slate-900 font-semibold">{{ $medico->COD_MED }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Jornada') }}</span>
                            <span class="text-slate-900">{{ $medico->JORNADA }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Nómina') }}</span>
                            <span class="text-slate-900">{{ $medico->NOMINA }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Especialidad') }}</span>
                            <span class="text-slate-900">{{ $medico->ESPECIALIDAD }}</span>
                        </div>
                    </div>
                    
                    <!-- Column 2 -->
                    <div class="space-y-4">
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Modalidad') }}</span>
                            <span class="text-slate-900">{{ $medico->MODALIDAD }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Fecha Ingreso') }}</span>
                            <span class="text-slate-900">{{ $medico->FECHA_INGRESO ? $medico->FECHA_INGRESO->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Horas Contratadas') }}</span>
                            <span class="text-slate-900">{{ $medico->HORAS_CONTRATADAS ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500 mb-1">{{ __('Estado') }}</span>
                            @if($medico->estado == 'activo')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ ucfirst('activo') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ ucfirst($medico->estado) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Column 3 -->
                    <div class="space-y-4">
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Correo') }}</span>
                            <span class="text-slate-900">{{ $medico->CORREO ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Teléfono') }}</span>
                            <span class="text-slate-900">{{ $medico->TELEFONO ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-500">{{ __('Consultas') }}</span>
                            <span class="text-slate-900">{{ $medico->CONSULTAS ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mt-8">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">
                                    {{ __('Observaciones:') }}
                                </h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>{{ $medico->observaciones ?: 'Sin observaciones registradas.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 flex items-center space-x-3 pt-6 border-t border-slate-200">
                    <a href="{{ route('medicos.edit', $medico) }}" class="inline-flex items-center px-4 py-2 bg-yellow-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i> {{ __('Editar') }}
                    </a>
                    <a href="{{ route('medicos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Volver') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
