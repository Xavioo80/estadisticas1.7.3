<div class="btn-group">
    <a href="{{ route('medicos.show', $medico) }}" class="btn btn-info btn-sm" title="Ver">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('medicos.edit', $medico) }}" class="btn btn-warning btn-sm" title="Editar">
        <i class="fas fa-edit"></i>
    </a>
    <form action="{{ route('medicos.destroy', $medico) }}" method="POST" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Eliminar médico?')">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
