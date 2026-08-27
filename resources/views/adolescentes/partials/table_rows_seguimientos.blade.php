@foreach($registros as $index => $seg)
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
@endforeach
