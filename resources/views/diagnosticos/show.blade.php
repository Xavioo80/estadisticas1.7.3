<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Detalles del Diagnóstico') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-6 flex justify-between items-center bg-indigo-600 rounded-t-lg p-4 -mx-4 sm:-mx-8 -mt-4 sm:-mt-8 mb-8">
                    <h3 class="text-xl font-bold text-white">{{ $diagnostico->patologia }}</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                    <div class="space-y-2">
                        <span class="block text-sm font-medium text-slate-500">{{ __('Código') }}</span>
                        <span class="text-slate-900 font-semibold text-lg">{{ $diagnostico->codigo }}</span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-sm font-medium text-slate-500">{{ __('Auxiliar') }}</span>
                        <span class="text-slate-900">{{ $diagnostico->auxiliar ?? 'N/A' }}</span>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <span class="block text-sm font-medium text-slate-500">{{ __('Patología') }}</span>
                        <span class="text-slate-900">{{ $diagnostico->patologia }}</span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-sm font-medium text-slate-500">{{ __('Secundario') }}</span>
                        <span class="text-slate-900">{{ $diagnostico->secundario ?? 'N/A' }}</span>
                    </div>
                    <div class="space-y-2">
                        <span class="block text-sm font-medium text-slate-500">{{ __('Categoría') }}</span>
                        <span class="text-slate-900">{{ $diagnostico->categoria }}</span>
                    </div>
                </div>

                <div class="mt-8 flex items-center space-x-3 pt-6 border-t border-slate-200">
                    <a href="{{ route('diagnosticos.edit', $diagnostico) }}" class="inline-flex items-center px-4 py-2 bg-yellow-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i> {{ __('Editar') }}
                    </a>
                    <a href="{{ route('diagnosticos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Volver') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
