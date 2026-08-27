<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Ver Referencia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-6 flex justify-between items-center">
                    <a href="{{ route('referencias.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-arrow-left mr-2"></i> {{ __('Volver al Listado') }}
                    </a>
                    <a href="{{ route('referencias.edit', $referencia) }}" class="inline-flex items-center px-4 py-2 bg-yellow-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i> {{ __('Editar Referencia') }}
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2 space-y-6">
                        <div class="bg-slate-50 rounded-lg p-6 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-3 mb-4">
                                <i class="fas fa-hospital mr-2 text-indigo-600"></i> {{ __('Información General') }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Nombre') }}</label>
                                    <div class="text-slate-900 text-lg font-medium">{{ $referencia->nombre }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Tipo') }}</label>
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $referencia->tipo }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Estado') }}</label>
                                    <div>
                                        @if($referencia->estado)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1.5"></i> {{ __('Activo') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-times mr-1.5"></i> {{ __('Inactivo') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($referencia->direccion)
                                <div class="col-span-1 md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Dirección') }}</label>
                                    <div class="text-slate-900">{{ $referencia->direccion }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-slate-50 rounded-lg p-6 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-3 mb-4">
                                <i class="fas fa-address-book mr-2 text-indigo-600"></i> {{ __('Contacto') }}
                            </h3>
                            <div class="space-y-4">
                                @if($referencia->telefono)
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Teléfono') }}</label>
                                    <a href="tel:{{ $referencia->telefono }}" class="text-indigo-600 hover:text-indigo-900">{{ $referencia->telefono }}</a>
                                </div>
                                @endif

                                @if($referencia->email)
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Email') }}</label>
                                    <a href="mailto:{{ $referencia->email }}" class="text-indigo-600 hover:text-indigo-900">{{ $referencia->email }}</a>
                                </div>
                                @endif

                                @if($referencia->contacto)
                                <div>
                                    <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Persona de Contacto') }}</label>
                                    <div class="text-slate-900">{{ $referencia->contacto }}</div>
                                </div>
                                @endif

                                @if(!$referencia->telefono && !$referencia->email && !$referencia->contacto)
                                <div class="text-center text-slate-500 py-4">
                                    <i class="fas fa-info-circle fa-2x mb-2 text-slate-400"></i>
                                    <p class="text-sm">{{ __('No hay información de contacto disponible') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-6 border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-800 border-b border-slate-200 pb-3 mb-4">
                                <i class="fas fa-info-circle mr-2 text-indigo-600"></i> {{ __('Información del Sistema') }}
                            </h3>
                            <div class="space-y-2 text-sm text-slate-600">
                                <p><strong class="text-slate-700">{{ __('ID:') }}</strong> {{ $referencia->id }}</p>
                                <p><strong class="text-slate-700">{{ __('Creado:') }}</strong> {{ $referencia->created_at->format('d/m/Y H:i') }}</p>
                                <p><strong class="text-slate-700">{{ __('Actualizado:') }}</strong> {{ $referencia->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                             <form id="delete-form" action="{{ route('referencias.destroy', $referencia) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmarEliminacion({{ $referencia->id }}, '{{ addslashes($referencia->nombre) }}')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-trash mr-2"></i> {{ __('Eliminar Referencia') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 for Delete Confirmation -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Está seguro?',
            text: `¿Desea eliminar la referencia "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626', // Tailwind red-600
            cancelButtonColor: '#94a3b8', // Tailwind slate-400
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    }
    </script>
</x-app-layout>
