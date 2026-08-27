<div class="h-100 d-flex flex-column justify-content-between overflow-hidden">
    <div class="table-responsive flex-grow-1" style="border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); overflow-y: auto; max-height: calc(100vh - 275px);">
        <table class="table table-hover table-sing mb-0 text-nowrap" style="font-size: 0.82rem; border-collapse: separate; border-spacing: 0;">
            <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <tr>
                    <th class="text-center py-2 px-3" style="color: var(--text-muted); font-weight: 700; width: 45px; border-right: 1px solid var(--border-color);">N°</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">DNI Formateado</th>
                    <th class="py-2 px-3 font-monospace" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">DNI Dígitos</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Nombre Completo</th>
                    <th class="py-2 px-3 text-center" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Fecha Nac.</th>
                    <th class="py-2 px-3 text-center" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Edad</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Teléfono</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Colonia / Dirección</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Departamento</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Municipio</th>
                    <th class="py-2 px-3 text-center" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Cód. Mun.</th>
                    <th class="py-2 px-3 text-center" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Fecha Reg.</th>
                    <th class="py-2 px-3 text-center" style="color: var(--text-muted); font-weight: 700;">Acciones</th>
                </tr>
            </thead>
            <tbody style="color: var(--text-primary);">
                @forelse($pacientes as $idx => $p)
                    @php $resyncUrl = route('pacientes.resync', $p->id); @endphp
                    <tr data-paciente-id="{{ $p->id }}" style="border-bottom: 1px solid var(--border-color);">
                        <td class="py-2 px-3 text-center font-monospace" style="color: var(--text-muted); border-right: 1px solid var(--border-color);">
                            {{ $pacientes->firstItem() + $idx }}
                        </td>
                        <td class="py-2 px-3 font-monospace font-weight-bold" style="color: var(--color-primary); border-right: 1px solid var(--border-color);">
                            {{ $p->dni ?: '-' }}
                        </td>
                        <td class="py-2 px-3 font-monospace" style="color: var(--text-muted); font-size: 0.78rem; border-right: 1px solid var(--border-color);">
                            {{ $p->dni_limpio ?: '-' }}
                        </td>
                        <td class="py-2 px-3 font-weight-semibold" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">
                            {{ $p->nombre_completo }}
                        </td>
                        <td class="py-2 px-3 text-center" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">
                            {{ $p->fecha_nacimiento ?: '-' }}
                        </td>
                        <td class="py-2 px-3 text-center" style="border-right: 1px solid var(--border-color);">
                            @php
                                $edadCalculada = null;
                                if (!empty($p->fecha_nacimiento)) {
                                    try {
                                        $fechaNac = \Carbon\Carbon::createFromFormat('d/m/Y', $p->fecha_nacimiento);
                                        $edadCalculada = $fechaNac->age;
                                    } catch (\Exception $e) {
                                        try {
                                            $fechaNac = \Carbon\Carbon::parse($p->fecha_nacimiento);
                                            $edadCalculada = ($fechaNac->year > 1900 && $fechaNac->year <= now()->year)
                                                ? $fechaNac->age : null;
                                        } catch (\Exception $e2) {}
                                    }
                                }
                                $edadMostrar = $edadCalculada ?? $p->edad;
                            @endphp
                            @if($edadMostrar !== null && $edadMostrar !== '')
                                <span class="badge badge-subtle-info px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">
                                    {{ $edadMostrar }} Años
                                </span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 font-monospace group" style="border-right: 1px solid var(--border-color);">
                            @php
                                $telMostrar = $p->telefono;
                                $telValido = !empty($telMostrar) && $telMostrar !== '-' && $telMostrar !== 'null';
                            @endphp
                            <div class="inline-edit-wrap d-inline-flex align-items-center" data-id="{{ $p->id }}" data-field="telefono" data-value="{{ $telValido ? $telMostrar : '' }}">
                                <span class="inline-edit-display d-inline-flex align-items-center gap-1" role="button" title="Doble clic para editar el teléfono" style="cursor: pointer;">
                                    @if($telValido)
                                        <span style="color: var(--text-primary);">{{ $telMostrar }}</span>
                                    @else
                                        <span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">-- teléfono --</span>
                                    @endif
                                    <i class="bi bi-pencil-square ml-1" style="font-size: 0.75rem; color: var(--text-muted); opacity: 0.7;"></i>
                                </span>
                                <input type="text" class="inline-edit-input d-none form-control form-control-sm px-2 py-0" style="width: 110px; font-size: 0.8rem; height: 26px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--color-primary);" placeholder="9999-9999" maxlength="20">
                                <span class="inline-edit-saving d-none ml-1 text-muted"><i class="spinner-border spinner-border-sm"></i></span>
                            </div>
                        </td>
                        <td class="py-2 px-3" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">
                            {{ $p->colonia ?: '-' }}
                        </td>
                        <td class="py-2 px-3" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">
                            {{ $p->departamento ?: 'FRANCISCO MORAZAN' }}
                        </td>
                        <td class="py-2 px-3" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">
                            {{ $p->municipio ?: 'DISTRITO CENTRAL' }}
                        </td>
                        <td class="py-2 px-3 text-center font-monospace" style="color: var(--text-muted); font-size: 0.78rem; border-right: 1px solid var(--border-color);">
                            {{ $p->cod_municipio ?: '0801' }}
                        </td>
                        <td class="py-2 px-3 text-center font-monospace" style="color: var(--text-muted); font-size: 0.75rem; border-right: 1px solid var(--border-color);">
                            {{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-' }}
                        </td>
                        {{-- Botones de Acción --}}
                        <td class="py-2 px-2 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center" style="gap: 4px;">
                                {{-- 1. Guardar edición --}}
                                <button type="button"
                                    class="btn-guardar-fila btn btn-icon btn-sm btn-subtle-warning"
                                    style="width: 28px; height: 28px; border-radius: var(--radius-sm);"
                                    data-id="{{ $p->id }}"
                                    title="Guardar cambios editados en esta fila">
                                    <i class="bi bi-save" style="font-size: 0.85rem;"></i>
                                </button>

                                {{-- 2. Recalcular edad individual --}}
                                <button type="button"
                                    class="btn-recalc-edad btn btn-icon btn-sm btn-subtle-info"
                                    style="width: 28px; height: 28px; border-radius: var(--radius-sm);"
                                    data-id="{{ $p->id }}"
                                    data-url="{{ route('pacientes.recalcular_edad', $p->id) }}"
                                    title="Recalcular la edad de este paciente">
                                    <i class="bi bi-calculator" style="font-size: 0.85rem;"></i>
                                </button>

                                {{-- 3. Actualizar (resync SESAL) --}}
                                <button type="button"
                                    class="btn-resync btn btn-icon btn-sm btn-subtle-primary"
                                    style="width: 28px; height: 28px; border-radius: var(--radius-sm);"
                                    data-id="{{ $p->id }}"
                                    data-url="{{ route('pacientes.resync', $p->id) }}"
                                    title="Volver a consultar datos en SESAL/SNVS">
                                    <i class="bi bi-arrow-repeat" style="font-size: 0.85rem;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="py-5 text-center" style="color: var(--text-muted);">
                            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                                <i class="bi bi-people" style="font-size: 2.2rem; opacity: 0.4;"></i>
                                <span style="font-size: 0.9rem; font-weight: 500;">No se encontraron pacientes registrados</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($pacientes->hasPages())
        <div class="mt-3 d-flex flex-column flex-md-row align-items-center justify-content-between p-3 rounded" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); gap: 0.75rem;">
            <div style="color: var(--text-secondary); font-size: 0.85rem; font-weight: 500;">
                Mostrando del <span class="font-weight-bold" style="color: var(--color-primary);">{{ number_format($pacientes->firstItem()) }}</span> al <span class="font-weight-bold" style="color: var(--color-primary);">{{ number_format($pacientes->lastItem()) }}</span> de <span class="font-weight-bold" style="color: var(--color-primary);">{{ number_format($pacientes->total()) }}</span> pacientes
            </div>
            <div class="pagination-wrapper">
                {{ $pacientes->links() }}
            </div>
        </div>
    @endif
</div>
