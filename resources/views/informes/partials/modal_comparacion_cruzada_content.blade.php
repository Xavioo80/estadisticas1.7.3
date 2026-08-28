<div class="comparacion-cruzada-wrapper" style="padding: 0.25rem 0.5rem;">
    <!-- Barra Superior de Filtros del Modal (Una Sola Fila Horizontal Estricta) -->
    <div style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: center !important; justify-content: space-between !important; padding: 10px 14px !important; margin-bottom: 14px !important; background: var(--bg-subtle) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md) !important; overflow-x: auto !important; width: 100% !important;">
        
        <!-- Controles de Filtro en una Sola Línea -->
        <div style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: center !important; flex-shrink: 0 !important;">
            <span style="font-weight: 700 !important; font-size: 0.78rem !important; color: var(--text-secondary) !important; text-transform: uppercase !important; margin-right: 10px !important; white-space: nowrap !important; display: inline-flex !important; align-items: center !important;">
                <i class="bi bi-funnel-fill text-primary" style="margin-right: 4px !important;"></i> Filtros:
            </span>
            
            <div style="width: 85px !important; margin-right: 8px !important; flex-shrink: 0 !important;">
                <select id="modal-cmp-ano" class="form-control form-control-sm font-weight-bold" 
                        style="background: var(--input-bg) !important; color: var(--text-primary) !important; border-color: var(--border-color) !important; height: 32px !important; font-size: 0.8rem !important; padding: 2px 6px !important; display: inline-block !important; width: 100% !important;">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="width: 120px !important; margin-right: 8px !important; flex-shrink: 0 !important;">
                <select id="modal-cmp-mes" class="form-control form-control-sm font-weight-bold text-uppercase" 
                        style="background: var(--input-bg) !important; color: var(--text-primary) !important; border-color: var(--border-color) !important; height: 32px !important; font-size: 0.8rem !important; padding: 2px 6px !important; display: inline-block !important; width: 100% !important;">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mes) ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="width: 145px !important; margin-right: 8px !important; flex-shrink: 0 !important;">
                <select id="modal-cmp-jornada" class="form-control form-control-sm font-weight-bold" 
                        style="background: var(--input-bg) !important; color: var(--text-primary) !important; border-color: var(--border-color) !important; height: 32px !important; font-size: 0.8rem !important; padding: 2px 6px !important; display: inline-block !important; width: 100% !important;">
                    <option value="TODAS" {{ $jornada == 'TODAS' ? 'selected' : '' }}>TODAS LAS JORNADAS</option>
                    @foreach($jornadas as $j)
                        <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="width: 160px !important; margin-right: 10px !important; flex-shrink: 0 !important;">
                <select id="modal-cmp-condicion-filter" class="form-control form-control-sm font-weight-bold" onchange="filtrarFilasPorCondicion(this.value)"
                        style="background: var(--input-bg) !important; color: var(--color-primary) !important; border-color: var(--border-color) !important; height: 32px !important; font-size: 0.8rem !important; padding: 2px 6px !important; display: inline-block !important; width: 100% !important;">
                    <option value="ALL">TODAS LAS COND.</option>
                    <option value="N">SOLO NUEVOS (N)</option>
                    <option value="S">SOLO SUBSIGUIENTES (S)</option>
                    <option value="TOTAL">TOTALES (N+S)</option>
                </select>
            </div>
            
            <button type="button" class="btn btn-sm btn-primary" onclick="recargarModalComparacion()" 
                    style="height: 32px !important; font-size: 0.78rem !important; font-weight: 700 !important; padding: 0 14px !important; white-space: nowrap !important; flex-shrink: 0 !important; display: inline-flex !important; align-items: center !important;">
                <i class="bi bi-arrow-clockwise" style="margin-right: 5px !important;"></i> Actualizar
            </button>
        </div>

        <!-- Badges Informativos a la derecha -->
        <div style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: center !important; flex-shrink: 0 !important;">
            <span class="badge badge-subtle-primary font-weight-bold" style="font-size: 0.72rem !important; border-radius: var(--radius-xs) !important; border: 1px solid var(--border-color) !important; padding: 6px 10px !important; margin-right: 6px !important; white-space: nowrap !important;">
                <i class="bi bi-database-check" style="margin-right: 4px !important;"></i> {{ $resultado['resumen']['total_informes_raw'] }} Diagnósticos
            </span>
            <span class="badge badge-subtle-secondary font-weight-bold" style="font-size: 0.72rem !important; border-radius: var(--radius-xs) !important; border: 1px solid var(--border-color) !important; padding: 6px 10px !important; white-space: nowrap !important;">
                <i class="bi bi-people" style="margin-right: 4px !important;"></i> {{ $resultado['resumen']['total_registros_globales'] }} Consultas Raw
            </span>
        </div>
    </div>

    <!-- Tarjetas KPI de Consistencia en Grid de 4 Columnas -->
    <div style="display: grid !important; grid-template-columns: repeat(4, 1fr) !important; gap: 0.75rem !important; margin-bottom: 1rem !important;">
        <div class="card p-2.5 text-center" style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md) !important; box-shadow: var(--shadow-sm) !important;">
            <div class="text-muted font-weight-bold mb-1" style="font-size: 0.7rem !important; text-transform: uppercase !important;">Consistencia General</div>
            <div class="font-weight-bold" style="font-size: 1.4rem !important; line-height: 1.2 !important; color: {{ $resultado['resumen']['porcentaje_consistencia'] == 100 ? 'var(--color-success, #10b981)' : ($resultado['resumen']['porcentaje_consistencia'] >= 80 ? 'var(--color-warning, #f59e0b)' : 'var(--color-danger, #ef4444)') }} !important;">
                {{ $resultado['resumen']['porcentaje_consistencia'] }}%
            </div>
            <small class="text-muted" style="font-size: 0.68rem !important;">{{ $resultado['resumen']['total_comparables'] }} cruces auditados</small>
        </div>

        <div class="card p-2.5 text-center" style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md) !important; box-shadow: var(--shadow-sm) !important;">
            <div class="text-muted font-weight-bold mb-1" style="font-size: 0.7rem !important; text-transform: uppercase !important;">Coincidencias Exactas</div>
            <div class="font-weight-bold text-success" style="font-size: 1.4rem !important; line-height: 1.2 !important;">
                <i class="bi bi-check-circle-fill" style="margin-right: 4px !important;"></i> {{ $resultado['resumen']['coincidencias'] }}
            </div>
            <small class="text-muted" style="font-size: 0.68rem !important;">Cifras 100% idénticas</small>
        </div>

        <div class="card p-2.5 text-center" style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md) !important; box-shadow: var(--shadow-sm) !important;">
            <div class="text-muted font-weight-bold mb-1" style="font-size: 0.7rem !important; text-transform: uppercase !important;">Discrepancias</div>
            <div class="font-weight-bold {{ $resultado['resumen']['discrepancias'] > 0 ? 'text-warning' : 'text-muted' }}" style="font-size: 1.4rem !important; line-height: 1.2 !important;">
                <i class="bi bi-exclamation-triangle-fill" style="margin-right: 4px !important;"></i> {{ $resultado['resumen']['discrepancias'] }}
            </div>
            <small class="text-muted" style="font-size: 0.68rem !important;">Requieren revisión</small>
        </div>

        <div class="card p-2.5 text-center" style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md) !important; box-shadow: var(--shadow-sm) !important;">
            <div class="text-muted font-weight-bold mb-1" style="font-size: 0.7rem !important; text-transform: uppercase !important;">Informes Cruzados</div>
            <div class="font-weight-bold text-primary" style="font-size: 1.4rem !important; line-height: 1.2 !important;">
                4 Fuentes
            </div>
            <small class="text-muted" style="font-size: 0.68rem !important;">AT2RN / Morb / Trans2 / RG</small>
        </div>
    </div>

    <!-- Tabla Comparativa -->
    <div class="table-responsive custom-scrollbar" style="max-height: 480px !important; overflow-y: auto !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md) !important;">
        <table class="table table-sm table-hover mb-0 text-center align-middle" style="font-size: 0.8rem !important; background: var(--bg-surface) !important; border-collapse: separate !important; border-spacing: 0 !important; width: 100% !important;">
            <thead class="sticky-top" style="background: var(--bg-subtle) !important; z-index: 10 !important; border-bottom: 2px solid var(--border-color) !important;">
                <tr>
                    <th class="text-left py-2 px-3" style="width: 33% !important; color: var(--text-primary) !important;">ENFERMEDAD / CONDICIÓN COMPATIBLE</th>
                    <th class="py-2" style="width: 13% !important; color: var(--text-primary) !important;" title="Informe Mensual AT2-R (N)">AT2-R (N)</th>
                    <th class="py-2" style="width: 13% !important; color: var(--text-primary) !important;" title="Informe Mensual de Morbilidad">MORBILIDAD</th>
                    <th class="py-2" style="width: 13% !important; color: var(--text-primary) !important;" title="Informe Semanal TRANS-2 (Únicamente Casos Nuevos)">TRANS-2 (Nuevas)</th>
                    <th class="py-2" style="width: 14% !important; color: var(--text-primary) !important;" title="Base de Datos Registros Globales / AT1">REG. GLOBALES (AT1)</th>
                    <th class="py-2 px-3" style="width: 14% !important; color: var(--text-primary) !important;">ESTADO DE CONSISTENCIA</th>
                </tr>
            </thead>
            <tbody>
                @php $curCat = null; @endphp
                @foreach($resultado['comparaciones'] as $cmp)
                    @if($curCat !== $cmp['categoria'])
                        @php $curCat = $cmp['categoria']; @endphp
                        <tr class="cat-header-row" data-cat="{{ $curCat }}" style="background: rgba(77, 124, 254, 0.08) !important;">
                            <td colspan="6" class="text-left font-weight-bold py-1.5 px-3 text-primary" style="font-size: 0.74rem !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; border-top: 1px solid var(--border-color) !important; border-bottom: 1px solid var(--border-color) !important;">
                                <i class="bi bi-folder2-open" style="margin-right: 4px !important;"></i> {{ $curCat }}
                            </td>
                        </tr>
                    @endif
                    <tr class="cmp-row" data-cond="{{ $cmp['condicion'] ?? 'ALL' }}" style="border-bottom: 1px solid var(--border-color) !important;">
                        <td class="text-left px-3 py-2 font-weight-bold" style="color: var(--text-primary) !important;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                <div>
                                    <span>{{ $cmp['label'] }}</span>
                                    @if(($cmp['condicion'] ?? '') === 'N')
                                        <span class="badge badge-subtle-primary ml-1" style="font-size: 0.65rem !important;">NUEVOS</span>
                                    @elseif(($cmp['condicion'] ?? '') === 'S')
                                        <span class="badge badge-subtle-secondary ml-1" style="font-size: 0.65rem !important;">SUBSIG.</span>
                                    @elseif(($cmp['condicion'] ?? '') === 'TOTAL')
                                        <span class="badge badge-subtle-info ml-1" style="font-size: 0.65rem !important;">TOTAL N+S</span>
                                    @endif
                                </div>
                                <span class="badge badge-secondary ml-1" style="font-size: 0.65rem !important; background: var(--bg-subtle) !important; color: var(--text-secondary) !important; border: 1px solid var(--border-color) !important;">{{ $cmp['codigo_cie'] }}</span>
                            </div>
                            <div class="text-muted font-weight-normal" style="font-size: 0.68rem !important; margin-top: 2px !important;">{{ $cmp['descripcion'] }}</div>
                        </td>

                        <!-- AT2-R (N) -->
                        <td class="py-2 font-weight-bold" style="font-size: 0.95rem !important;">
                            @if($cmp['at2rn'] !== null)
                                <span class="badge {{ $cmp['at2rn'] > 0 ? 'badge-primary' : 'badge-subtle' }}" style="font-size: 0.85rem !important; padding: 4px 8px !important;">
                                    {{ $cmp['at2rn'] }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem !important;">N/A</span>
                            @endif
                        </td>

                        <!-- Morbilidad -->
                        <td class="py-2 font-weight-bold" style="font-size: 0.95rem !important;">
                            @if($cmp['morbilidad'] !== null)
                                <span class="badge {{ $cmp['morbilidad'] > 0 ? 'badge-info' : 'badge-subtle' }}" style="font-size: 0.85rem !important; padding: 4px 8px !important;">
                                    {{ $cmp['morbilidad'] }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem !important;">N/A</span>
                            @endif
                        </td>

                        <!-- TRANS-2 (Únicamente Casos Nuevos) -->
                        <td class="py-2 font-weight-bold" style="font-size: 0.95rem !important;">
                            @if(($cmp['condicion'] ?? '') === 'S')
                                <span class="badge badge-subtle-secondary" style="font-size: 0.68rem !important; font-weight: 500 !important;" title="TRANS-2 solo contabiliza casos nuevos">Solo Nuevas</span>
                            @elseif(($cmp['condicion'] ?? '') === 'TOTAL')
                                <span class="badge badge-subtle-secondary" style="font-size: 0.68rem !important; font-weight: 500 !important;" title="TRANS-2 no totaliza N+S, solo contabiliza casos nuevos">Solo Nuevas</span>
                            @elseif($cmp['trans2'] !== null)
                                <span class="badge {{ $cmp['trans2'] > 0 ? 'badge-warning' : 'badge-subtle' }}" style="font-size: 0.85rem !important; padding: 4px 8px !important;">
                                    {{ $cmp['trans2'] }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem !important;">N/A</span>
                            @endif
                        </td>

                        <!-- Registros Globales -->
                        <td class="py-2 font-weight-bold" style="font-size: 0.95rem !important;">
                            @if($cmp['globales'] !== null)
                                <span class="badge {{ $cmp['globales'] > 0 ? 'badge-secondary' : 'badge-subtle' }}" style="font-size: 0.85rem !important; padding: 4px 8px !important; background: var(--bg-subtle) !important; color: var(--text-primary) !important; border: 1px solid var(--border-color) !important;">
                                    {{ $cmp['globales'] }}
                                </span>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem !important;">N/A</span>
                            @endif
                        </td>

                        <!-- Estado de Consistencia -->
                        <td class="py-2 px-3">
                            @if($cmp['estado'] === 'match')
                                <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 0.72rem !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;">
                                    <i class="bi bi-check-circle-fill"></i> COINCIDE 100%
                                </span>
                            @elseif($cmp['estado'] === 'discrepancia')
                                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 0.72rem !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;" title="Diferencia máxima: ±{{ $cmp['diferencia_max'] }}">
                                    <i class="bi bi-exclamation-triangle-fill"></i> DIF: ±{{ $cmp['diferencia_max'] }}
                                </span>
                            @else
                                <span class="badge badge-secondary px-2 py-1" style="font-size: 0.72rem !important; background: var(--bg-subtle) !important; color: var(--text-muted) !important; border: 1px solid var(--border-color) !important;">
                                    INFORMATIVO
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pie del Modal con Nota Explicativa -->
    <div class="d-flex align-items-center justify-content-between mt-3 text-muted" style="font-size: 0.72rem !important;">
        <div>
            <i class="bi bi-info-circle mr-1"></i>
            <span><strong>Nota Técnica:</strong> TRANS-2 contabiliza únicamente casos <em>Nuevos (N)</em> por semana epidemiológica. En totalizaciones (N + S), TRANS-2 indica "Solo Nuevas" y no penaliza la consistencia.</span>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">
                <i class="bi bi-x-lg mr-1"></i> Cerrar Auditoría
            </button>
        </div>
    </div>
</div>

<script>
    function filtrarFilasPorCondicion(val) {
        const rows = document.querySelectorAll('.cmp-row');
        rows.forEach(r => {
            const cond = r.getAttribute('data-cond');
            if (val === 'ALL' || cond === val || (val === 'TOTAL' && cond === 'TOTAL')) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
    }
</script>
