<div class="ata-card" id="ata-card-container">
    <div class="ata-table-wrapper">
        <table class="table-ata">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 100px;">EXPEDIENTE</th>
                    <th style="width: 100px;">FECHA</th>
                    <th>NOMBRE COMPLETO</th>
                    <th style="width: 50px;">SEXO</th>
                    <th style="width: 50px;">EDAD</th>
                    <th>DIAGNÓSTICO / MOTIVO DE CONSULTA</th>
                    <th>TUTOR</th>
                    <th style="width: 100px;">TELÉFONO</th>
                    <th style="width: 100px;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $index => $seg)
                    <tr>
                        <td class="text-center text-muted">{{ $registros->firstItem() + $index }}</td>
                        <td class="text-danger font-weight-bold text-center">{{ $seg->no_expediente }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($seg->fecha_consulta)->format('d/m/Y') }}</td>
                        <td class="text-uppercase">{{ $seg->nombre_completo }}</td>
                        <td class="text-center">
                            <span class="font-weight-bold {{ $seg->sexo == 'M' ? 'text-primary' : 'text-danger' }}">
                                {{ $seg->sexo }}
                            </span>
                        </td>
                        <td class="text-center">{{ $seg->edad }}</td>
                        <td class="small text-wrap" style="white-space: normal; line-height: 1.2;">{{ $seg->diagnostico_seguimiento }}</td>
                        <td class="small">{{ $seg->nombre_tutor }}</td>
                        <td class="text-center">{{ $seg->numero_telefono }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center align-items-center h-100 gap-1 px-1">
                                <button type="button" 
                                    class="btn btn-info btn-sm p-0 d-flex align-items-center justify-content-center btn-editar-seguimiento" 
                                    data-id="{{ $seg->id }}"
                                    style="width: 20px; height: 18px; border-radius: 2px; border: none;"
                                    title="Editar">
                                    <i class="fas fa-edit text-white" style="font-size: 9px;"></i>
                                </button>
                                <form action="{{ route('adolescentes.seguimiento.destroy', $seg->id) }}" method="POST" onsubmit="return confirm('¿Eliminar seguimiento?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center"
                                        style="width: 20px; height: 18px; border-radius: 2px;"
                                        title="Eliminar">
                                        <i class="fas fa-trash-alt" style="font-size: 9px;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">No se encontraron seguimientos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="ata-footer">
        <div class="d-flex align-items-center">
            <span>SEGUIMIENTOS: <span class="badge-ata">{{ $registros->total() }}</span></span>
            <span class="mx-3">|</span>
            <span>PÁGINA: <span class="badge-ata bg-light text-dark border">{{ $registros->currentPage() }}</span> DE {{ $registros->lastPage() }}</span>
        </div>
        <div class="pagination-ata shadow-none border-0">
            {{ $registros->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
