<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Crear Nueva Colonia') }}
        </h2>
    </x-slot>

    @php
        // Consulta directa para obtener el código máximo numérico
        $ultimoCodigo = (int)DB::select('SELECT MAX(CAST(COD_COL AS UNSIGNED)) as max_code FROM colonias')[0]->max_code;
        $siguienteCodigo = $ultimoCodigo + 1;
        
        // Debug (descomentar si es necesario)
        // \Log::info("Código máximo colonia: {$ultimoCodigo}, Siguiente: {$siguienteCodigo}");
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="p-4 rounded-md border-l-4 border-blue-500 bg-blue-50 text-blue-700">
                <p>Último código usado: <strong>{{ $ultimoCodigo ?? 'Ninguno' }}</strong> | Siguiente código sugerido: <strong>{{ $siguienteCodigo }}</strong></p>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <form action="{{ route('colonias.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                        <div class="col-span-1 md:col-span-1">
                            <label for="COD_COL" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Código:') }}</label>
                            <input type="number" name="COD_COL" id="COD_COL" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ $siguienteCodigo }}" required>
                        </div>
                        
                        <div class="col-span-1 md:col-span-5">
                            <label for="COLONIA" class="block text-sm font-medium text-slate-700 mb-1">{{ __('Colonia:') }}</label>
                            <input type="text" name="COLONIA" id="COLONIA" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" maxlength="100" required>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-slate-200">
                        <a href="{{ route('colonias.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('Cancelar') }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-save mr-2"></i> {{ __('Guardar') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
