<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Agregar Nueva Referencia') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="mb-4">
                <a href="{{ route('referencias.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i> {{ __('Volver al Listado') }}
                </a>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-6 border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fas fa-hospital mr-2 text-indigo-600"></i> {{ __('Información de la Referencia') }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Complete la información del hospital o centro de salud') }}</p>
                </div>

                <form action="{{ route('referencias.store') }}" method="POST" id="form-referencia" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-hospital-alt text-indigo-500 mr-1"></i> {{ __('Nombre de la Referencia *') }}
                            </label>
                            <input type="text" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('nombre') border-red-500 @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}" 
                                   placeholder="Ej: HOSPITAL GENERAL SAN FELIPE"
                                   required>
                            @error('nombre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-tag text-blue-500 mr-1"></i> {{ __('Tipo *') }}
                            </label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('tipo') border-red-500 @enderror" 
                                    id="tipo" 
                                    name="tipo" 
                                    required>
                                <option value="">{{ __('Seleccione el tipo') }}</option>
                                <option value="HOSPITAL GENERAL" {{ old('tipo') == 'HOSPITAL GENERAL' ? 'selected' : '' }}>Hospital General</option>
                                <option value="HOSPITAL ESPECIALIZADO" {{ old('tipo') == 'HOSPITAL ESPECIALIZADO' ? 'selected' : '' }}>Hospital Especializado</option>
                                <option value="HOSPITAL PSIQUIATRICO" {{ old('tipo') == 'HOSPITAL PSIQUIATRICO' ? 'selected' : '' }}>Hospital Psiquiátrico</option>
                                <option value="INSTITUTO" {{ old('tipo') == 'INSTITUTO' ? 'selected' : '' }}>Instituto</option>
                                <option value="CENTRO DE SALUD" {{ old('tipo') == 'CENTRO DE SALUD' ? 'selected' : '' }}>Centro de Salud</option>
                                <option value="CLINICA" {{ old('tipo') == 'CLINICA' ? 'selected' : '' }}>Clínica</option>
                                <option value="OTRO" {{ old('tipo') == 'OTRO' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('tipo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700 mb-3">
                                <i class="fas fa-toggle-on text-green-500 mr-1"></i> {{ __('Estado') }}
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="estado" name="estado" value="1" class="sr-only peer" {{ old('estado', true) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                <span class="ms-3 text-sm font-medium text-gray-700">{{ __('Activo') }}</span>
                            </label>
                        </div>

                        <!-- Dirección -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-map-marker-alt text-yellow-500 mr-1"></i> {{ __('Dirección') }}
                            </label>
                            <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('direccion') border-red-500 @enderror" 
                                      id="direccion" 
                                      name="direccion" 
                                      rows="3" 
                                      placeholder="Dirección completa del hospital o centro de salud">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-phone text-indigo-500 mr-1"></i> {{ __('Teléfono') }}
                            </label>
                            <input type="text" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('telefono') border-red-500 @enderror" 
                                   id="telefono" 
                                   name="telefono" 
                                   value="{{ old('telefono') }}" 
                                   placeholder="Ej: 2222-1234">
                            @error('telefono')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-envelope text-blue-500 mr-1"></i> {{ __('Email') }}
                            </label>
                            <input type="email" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('email') border-red-500 @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="Ej: contacto@hospital.hn">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Contacto -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="contacto" class="block text-sm font-medium text-gray-700 mb-1">
                                <i class="fas fa-user-tie text-gray-500 mr-1"></i> {{ __('Persona de Contacto') }}
                            </label>
                            <input type="text" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('contacto') border-red-500 @enderror" 
                                   id="contacto" 
                                   name="contacto" 
                                   value="{{ old('contacto') }}" 
                                   placeholder="Ej: Dr. Juan Pérez - Director Médico">
                            @error('contacto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 mt-6">
                        <a href="{{ route('referencias.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-times mr-2"></i> {{ __('Cancelar') }}
                        </a>
                        <div class="flex space-x-3">
                            <button type="button" onclick="limpiarFormulario()" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-broom mr-2"></i> {{ __('Limpiar') }}
                            </button>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-save mr-2"></i> {{ __('Guardar Referencia') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function limpiarFormulario() {
        Swal.fire({
            title: '¿Limpiar formulario?',
            text: 'Se perderán todos los datos ingresados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#eab308', // yellow-500
            cancelButtonColor: '#94a3b8', // slate-400
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-referencia').reset();
                document.getElementById('estado').checked = true; // Mantener estado activo por defecto
                
                Swal.fire({
                    title: 'Formulario limpiado',
                    text: 'Todos los campos han sido restaurados',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }

    // Convertir nombre a mayúsculas automáticamente
    document.getElementById('nombre').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });

    // Validación del formulario
    document.getElementById('form-referencia').addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        const tipo = document.getElementById('tipo').value;
        
        if (!nombre) {
            e.preventDefault();
            Swal.fire({
                title: 'Campo requerido',
                text: 'El nombre de la referencia es obligatorio',
                icon: 'error'
            });
            return;
        }
        
        if (!tipo) {
            e.preventDefault();
            Swal.fire({
                title: 'Campo requerido',
                text: 'Debe seleccionar un tipo de referencia',
                icon: 'error'
            });
            return;
        }
    });
    </script>
</x-app-layout>
