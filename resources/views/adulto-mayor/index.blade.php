<x-app-layout>
    @section('title', 'Adulto Mayor')

    <div class="py-6 px-4">

        {{-- Header compacto con estadísticas --}}
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-black text-white flex items-center">
                        <i class="fas fa-user-friends mr-3"></i>
                        Registro Adulto Mayor
                    </h1>
                    <div class="flex items-center gap-6 mt-3">
                        <div class="flex items-center gap-2">
                            <span class="text-indigo-200 text-sm">Total:</span>
                            <span class="bg-white/20 backdrop-blur-sm text-white font-black px-3 py-1 rounded-lg text-lg">
                                {{ number_format($total) }}
                            </span>
                        </div>
                        @if($search)
                            <div class="flex items-center gap-2">
                                <span class="text-indigo-200 text-sm">Resultados:</span>
                                <span class="bg-white/20 backdrop-blur-sm text-white font-bold px-3 py-1 rounded-lg">
                                    {{ number_format($registros->total()) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('adulto-mayor.create') }}"
                   class="inline-flex items-center bg-white text-indigo-700 hover:bg-indigo-50 font-bold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-plus mr-2"></i> Nuevo Registro
                </a>
            </div>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-medium">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Búsqueda --}}
        <form method="GET" class="mb-5 flex gap-3">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Buscar por nombre, DNI, expediente o colonia..."
                   class="flex-1 border border-slate-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
            <button type="submit"
                    class="bg-slate-700 hover:bg-slate-800 text-white px-5 py-2 rounded-xl text-sm font-bold transition-all">
                <i class="fas fa-search mr-1"></i> Buscar
            </button>
            @if($search)
                <a href="{{ route('adulto-mayor.index') }}"
                   class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl text-sm font-bold transition-all">
                    Limpiar
                </a>
            @endif
        </form>

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 text-xs uppercase">Expediente</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 text-xs uppercase">Nombre Completo</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 text-xs uppercase">DNI</th>
                            <th class="text-center px-4 py-3 font-bold text-slate-600 text-xs uppercase">Edad</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 text-xs uppercase">Colonia</th>
                            <th class="text-left px-4 py-3 font-bold text-slate-600 text-xs uppercase">Teléfono</th>
                            <th class="text-center px-4 py-3 font-bold text-slate-600 text-xs uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($registros as $reg)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3 text-slate-500 font-mono text-xs">
                                    {{ $reg->expediente ?: '-' }}
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    {{ $reg->nombre_completo }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                                    {{ $reg->dni ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($reg->edad)
                                        <span class="bg-indigo-100 text-indigo-700 font-bold px-2 py-0.5 rounded-full text-xs">
                                            {{ $reg->edad }} años
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ $reg->colonia ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ $reg->telefono ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('adulto-mayor.edit', $reg) }}"
                                           class="inline-flex items-center bg-amber-100 hover:bg-amber-200 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                        <form method="POST" action="{{ route('adulto-mayor.destroy', $reg) }}"
                                              onsubmit="return confirm('¿Eliminar este registro?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold px-3 py-1.5 rounded-lg transition-all">
                                                <i class="fas fa-trash mr-1"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                    <i class="fas fa-search fa-2x mb-3 block opacity-30"></i>
                                    No se encontraron registros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($registros->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                    {{ $registros->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
