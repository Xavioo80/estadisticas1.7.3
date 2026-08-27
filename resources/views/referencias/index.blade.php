<x-app-layout>
    @section('title', 'Referencias')
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            <i class="fas fa-hospital-alt mr-2"></i>{{ __('Referencias - Hospitales y Centros de Salud') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('referencias.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Agregar Referencia
                </a>
            </div>

            <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Tabla de Referencias -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-700">
                            <i class="fas fa-list mr-2"></i>Lista de Referencias
                        </h3>
                        <span class="badge badge-primary badge-pill">{{ $referencias->total() }} registros</span>
                    </div>

                    @if($referencias->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 font-weight-bold">#</th>
                                        <th class="border-0 font-weight-bold">Nombre</th>
                                        <th class="border-0 font-weight-bold">Tipo</th>
                                        <th class="border-0 font-weight-bold">Teléfono</th>
                                        <th class="border-0 font-weight-bold">Contacto</th>
                                        <th class="border-0 font-weight-bold">Estado</th>
                                        <th class="border-0 font-weight-bold text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($referencias as $index => $referencia)
                                        <tr>
                                            <td class="align-middle">
                                                <span class="text-muted">{{ $referencias->firstItem() + $index }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle p-2 mr-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-hospital text-white"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold">{{ $referencia->nombre }}</h6>
                                                        @if($referencia->direccion)
                                                            <small class="text-muted">{{ Str::limit($referencia->direccion, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <span class="badge badge-info">{{ $referencia->tipo }}</span>
                                            </td>
                                            <td class="align-middle">
                                                @if($referencia->telefono)
                                                    <i class="fas fa-phone mr-1 text-muted"></i>{{ $referencia->telefono }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($referencia->contacto)
                                                    {{ Str::limit($referencia->contacto, 30) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if($referencia->estado)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check mr-1"></i>Activo
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-times mr-1"></i>Inactivo
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('referencias.show', $referencia) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('referencias.edit', $referencia) }}" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            onclick="confirmarEliminacion({{ $referencia->id }}, '{{ $referencia->nombre }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-hospital-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay referencias registradas</h5>
                            <p class="text-muted">Comience agregando la primera referencia al sistema.</p>
                            <a href="{{ route('referencias.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Agregar Primera Referencia
                            </a>
                        </div>
                    @endif
                </div>
                
                @if($referencias->hasPages())
                    <div class="p-4 bg-gray-50 border-t border-gray-200">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Mostrando {{ $referencias->firstItem() }} a {{ $referencias->lastItem() }} 
                                de {{ $referencias->total() }} resultados
                            </div>
                            {{ $referencias->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Form oculto para eliminación -->
    <form id="delete-form" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarEliminacion(id, nombre) {
        Swal.fire({
            title: '¿Está seguro?',
            text: `¿Desea eliminar la referencia "${nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form');
                form.action = `/referencias/${id}`;
                form.submit();
            }
        });
    }
    </script>
</x-app-layout>
