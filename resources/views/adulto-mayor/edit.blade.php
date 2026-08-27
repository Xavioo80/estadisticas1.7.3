<x-app-layout>
    @section('title', 'Editar Registro - Adulto Mayor')

    <div class="py-6 px-4 max-w-2xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('adulto-mayor.index') }}" class="text-slate-400 hover:text-indigo-600 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-2xl font-black text-slate-800">
                <i class="fas fa-user-edit mr-2 text-amber-500"></i>Editar Registro
            </h1>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
            <form method="POST" action="{{ route('adulto-mayor.update', $registro) }}" class="space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Expediente</label>
                        <input type="text" name="expediente" value="{{ old('expediente', $registro->expediente) }}"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Edad</label>
                        <input type="number" name="edad" value="{{ old('edad', $registro->edad) }}" min="0" max="150"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $registro->nombre_completo) }}"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none @error('nombre_completo') border-red-400 @enderror">
                    @error('nombre_completo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">DNI</label>
                    <input type="text" 
                           id="dni" 
                           name="dni" 
                           value="{{ old('dni', $registro->dni) }}"
                           placeholder="xxxx-xxxx-xxxxx"
                           maxlength="15"
                           data-original-dni="{{ $registro->dni }}"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    <div id="dni-alert" class="hidden mt-2 p-3 rounded-lg text-sm"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Colonia / Barrio</label>
                        <select name="colonia" class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                            <option value="">-- Seleccione una colonia --</option>
                            @foreach($colonias as $col)
                                <option value="{{ $col->COLONIA }}" {{ old('colonia', $registro->colonia) == $col->COLONIA ? 'selected' : '' }}>
                                    {{ $col->COD_COL }} - {{ $col->COLONIA }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $registro->telefono) }}"
                               class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $registro->direccion) }}"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                </div>

                <div class="flex justify-between items-center pt-2">
                    <form method="POST" action="{{ route('adulto-mayor.destroy', $registro) }}"
                          onsubmit="return confirm('¿Eliminar este registro permanentemente?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="text-red-500 hover:text-red-700 text-sm font-bold transition-colors">
                            <i class="fas fa-trash mr-1"></i> Eliminar registro
                        </button>
                    </form>
                    <div class="flex gap-3">
                        <a href="{{ route('adulto-mayor.index') }}"
                           class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                                class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-6 py-2.5 rounded-xl shadow transition-all">
                            <i class="fas fa-save mr-2"></i>Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dniInput = document.getElementById('dni');
            const dniAlert = document.getElementById('dni-alert');
            const originalDni = dniInput.dataset.originalDni;
            let checkTimeout = null;

            // Máscara para DNI formato: xxxx-xxxx-xxxxx (4-4-5 dígitos)
            dniInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Solo números
                let formatted = '';

                // Aplicar formato: xxxx-xxxx-xxxxx
                if (value.length > 0) {
                    formatted = value.substring(0, 4);
                }
                if (value.length >= 5) {
                    formatted += '-' + value.substring(4, 8);
                }
                if (value.length >= 9) {
                    formatted += '-' + value.substring(8, 13);
                }

                e.target.value = formatted;

                // Verificar DNI cuando tenga formato completo (13 dígitos) y sea diferente del original
                clearTimeout(checkTimeout);
                const cleanDni = value;
                
                if (cleanDni.length >= 13 && formatted !== originalDni) {
                    checkTimeout = setTimeout(() => {
                        checkDniExists(formatted);
                    }, 500);
                } else {
                    hideDniAlert();
                }
            });

            function checkDniExists(dni) {
                fetch(`{{ route('adulto-mayor.check-dni') }}?dni=${encodeURIComponent(dni)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            showDniAlert(
                                `Este DNI ya existe registrado: <strong>${data.nombre}</strong> - Expediente: <strong>${data.expediente || 'Sin expediente'}</strong>`,
                                'warning'
                            );
                        } else {
                            showDniAlert('DNI disponible', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Error al verificar DNI:', error);
                    });
            }

            function showDniAlert(message, type) {
                dniAlert.classList.remove('hidden', 'bg-yellow-50', 'border-yellow-200', 'text-yellow-800', 'bg-green-50', 'border-green-200', 'text-green-700');
                
                if (type === 'warning') {
                    dniAlert.classList.add('bg-yellow-50', 'border-yellow-200', 'text-yellow-800', 'border');
                    dniAlert.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
                } else if (type === 'success') {
                    dniAlert.classList.add('bg-green-50', 'border-green-200', 'text-green-700', 'border');
                    dniAlert.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
                }
            }

            function hideDniAlert() {
                dniAlert.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
