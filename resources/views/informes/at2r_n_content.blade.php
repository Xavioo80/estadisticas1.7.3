<div style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;">
    <div class="table-responsive" style="flex: 1 1 0%; min-height: 0; overflow: auto;">
        <table class="table table-bordered table-sm text-center mb-0 w-full" id="at2rTable">
            <thead class="thead-premium sticky-top">
                <tr class="bg-light">
                    <th rowspan="2" class="sticky-col-first align-middle"
                        style="width: 240px; min-width: 220px;">CONCEPTO</th>
                    <th colspan="2" class="text-primary">ENFERMERA</th>
                    <th colspan="2" class="text-danger">MÉDICO</th>
                    <th rowspan="2" class="align-middle bg-primary-soft" style="width: 85px;">TOTAL</th>
                    <th rowspan="2" class="align-middle text-emerald-400 no-print col-morbilidad-head" style="width: 110px; border-left: 2px solid var(--border-color); font-size: 11px; font-weight: 800; background: rgba(16, 185, 129, 0.12);" title="Total en el informe de Morbilidad (< 5 años)">MORBILIDAD</th>
                </tr>
                <tr>
                    <th style="width: 100px; min-width: 100px;">AUXILIARES</th>
                    <th style="width: 100px; min-width: 100px;" title="PROFESIONAL / NUTRICIÓN / PSICOLOGÍA">PROF. / NUTRI. / PSICO.</th>
                    <th style="width: 100px; min-width: 100px;">GENERAL</th>
                    <th style="width: 100px; min-width: 100px;">ESPECIALISTA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finalData as $row)
                    @if(!($row['hidden'] ?? false))
                        @if($row['header'] ?? false)
                            <tr class="row-section-header">
                                <td colspan="7" class="text-center py-1" style="letter-spacing: 2px; font-size: 0.85rem;">
                                    {{ $row['label'] }}</td>
                            </tr>
                        @else
                            @php
                                $label      = strtolower(trim($row['label']));
                                $isTotal    = str_starts_with($label, 'total');
                                $isMenores5 = str_contains($label, 'menores de 5') || str_contains($label, 'menores 5');
                                $isEmbarazo = str_contains($label, 'embaraz') || str_contains($label, 'prenatal');
                                $isPuerperio= str_contains($label, 'puerperio') || str_contains($label, 'puérpera') || str_contains($label, 'puerpera');
                                $isNino     = str_contains($label, 'niño') || str_contains($label, 'niña') || str_contains($label, 'nino') || str_contains($label, 'nina');

                                $rowClass = '';
                                if ($isTotal)     $rowClass = 'row-total-highlight';
                                elseif ($isMenores5) $rowClass = 'row-menores5';
                                elseif ($isEmbarazo) $rowClass = 'row-embarazo';
                                elseif ($isPuerperio) $rowClass = 'row-puerperio';
                                elseif ($isNino) $rowClass = 'row-nino';

                                $morbVal = $row['morbilidad_val'] ?? null;
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="sticky-col-first {{ $rowClass ? $rowClass.'-sticky' : '' }}">
                                    {{ $row['label'] }}
                                </td>
                                @if($row['is_manual'] ?? false)
                                    <td><input type="number" min="0" class="manual-input text-center" data-col="1" data-key="{{ $row['manual_key'] ?? 'rehidratados' }}" value="{{ $row['cols'][1] ?: '0' }}"></td>
                                    <td><input type="number" min="0" class="manual-input text-center" data-col="2" data-key="{{ $row['manual_key'] ?? 'rehidratados' }}" value="{{ $row['cols'][2] ?: '0' }}"></td>
                                    <td><input type="number" min="0" class="manual-input text-center" data-col="3" data-key="{{ $row['manual_key'] ?? 'rehidratados' }}" value="{{ $row['cols'][3] ?: '0' }}"></td>
                                    <td><input type="number" min="0" class="manual-input text-center" data-col="4" data-key="{{ $row['manual_key'] ?? 'rehidratados' }}" value="{{ $row['cols'][4] ?: '0' }}"></td>
                                @else
                                    <td class="cell-clickable" data-row-idx="{{ $loop->index }}" data-col-idx="1" title="Ver desglose">{{ $row['cols'][1] ?: '0' }}</td>
                                    <td class="cell-clickable" data-row-idx="{{ $loop->index }}" data-col-idx="2" title="Ver desglose">{{ $row['cols'][2] ?: '0' }}</td>
                                    <td class="cell-clickable" data-row-idx="{{ $loop->index }}" data-col-idx="3" title="Ver desglose">{{ $row['cols'][3] ?: '0' }}</td>
                                    <td class="cell-clickable" data-row-idx="{{ $loop->index }}" data-col-idx="4" title="Ver desglose">{{ $row['cols'][4] ?: '0' }}</td>
                                @endif
                                <td class="col-total manual-total">{{ $row['total'] ?: '0' }}</td>
                                @if($morbVal !== null)
                                    @php
                                        $isMatch = ($morbVal == $row['total']);
                                        $morbKey = $row['morbilidad_key'] ?? '';
                                    @endphp
                                    <td class="col-morbilidad no-print" style="border-left: 2px solid #374151; vertical-align: middle; text-align: center;">
                                        <div class="morbilidad-badge {{ $isMatch ? 'morbilidad-match' : 'morbilidad-mismatch' }} morbilidad-clickable" 
                                             data-concept-key="{{ $morbKey }}"
                                             data-concept-label="{{ $row['label'] }}"
                                             data-at2-total="{{ $row['total'] }}"
                                             data-morb-total="{{ $morbVal }}"
                                             title="Click para ver auditoría detallada vs Morbilidad">
                                            <span class="m-val">{{ $morbVal }}</span>
                                            @if($isMatch)
                                                <i class="bi bi-check-circle-fill text-success" style="font-size: 11px;"></i>
                                            @else
                                                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 11px;"></i>
                                            @endif
                                        </div>
                                    </td>
                                @else
                                    <td class="col-morbilidad-empty no-print" style="border: none !important; background: transparent !important;"></td>
                                @endif
                            </tr>
                        @endif
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL DE DESGLOSE DE CELDA (MÉDICO Y FECHA) -->
<div id="cellDetailModal" class="fixed inset-0 hidden backdrop-blur-sm bg-slate-900/70 items-center justify-center p-4" style="z-index: 99999;">
    <div class="modal-container">
        <!-- 1. Header (Fixed top) -->
        <div class="modal-header">
            <div>
                <h3 style="font-size: 1rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; margin: 0; color: #ffffff;" id="modalConceptoTitle">
                    <i class="bi bi-stethoscope text-primary"></i> Desglose por Médico y Fecha
                </h3>
                <p style="font-size: 0.75rem; color: #94a3b8; margin: 0.25rem 0 0 0;" id="modalColumnaSubTitle">Cargando...</p>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span class="badge" style="background: rgba(77, 124, 254, 0.25); color: #93c5fd; border: 1px solid rgba(77, 124, 254, 0.4); padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;" id="modalTotalBadge">
                    0 atenciones
                </span>
                <button type="button" onclick="cerrarModalDetalles()" class="btn-filter-action" style="color: #94a3b8; background: transparent; border: none; font-size: 1.25rem; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- 2. Search Bar (Fixed sub-header) -->
        <div class="modal-search-bar">
            <div style="position: relative;">
                <i class="bi bi-search" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="text" id="modalDoctorSearch" placeholder="Filtrar médico o profesión..." oninput="filtrarMedicosModal()" class="modal-search-input">
            </div>
        </div>

        <!-- 3. Internal Scrollable Body -->
        <div class="modal-scroll-body">
            <!-- Loading State -->
            <div id="modalLoadingState" style="padding: 3rem 0; text-align: center; color: var(--text-muted);">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <p style="font-size: 0.88rem; font-weight: 600;">Consultando registros y desglosando por médico y fecha...</p>
            </div>

            <!-- Content Area -->
            <div id="modalContentArea" class="hidden">
                <!-- Doctors List Container (Scrollable) -->
                <div id="modalMedicosList">
                    <!-- Dynamic doctor cards -->
                </div>
            </div>
        </div>

        <!-- 4. Footer (Fixed bottom) -->
        <div style="padding: 0.75rem 1.5rem; background: var(--bg-surface); border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            <button type="button" onclick="cerrarModalDetalles()" class="btn btn-primary btn-sm" style="font-weight: 600; padding: 0.4rem 1.25rem;">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- MODAL DE AUDITORÍA Y COMPARACIÓN CON MORBILIDAD -->
<div id="morbilidadAuditModal" class="fixed inset-0 hidden backdrop-blur-sm bg-slate-900/70" style="z-index: 99999;">
    <div class="modal-container">
        <!-- Header -->
        <div class="modal-header">
            <div style="min-width: 0; flex: 1;">
                <h3 id="morbAuditTitle">
                    <i class="bi bi-clipboard2-pulse text-warning"></i> Auditoría: AT2-r N vs Morbilidad
                </h3>
                <p id="morbAuditSubtitle">Comparación y análisis de discrepancias</p>
            </div>
            <div style="display: flex; align-items: center; gap: 0.6rem; flex-shrink: 0;">
                <div id="morbAuditStatsBadge" style="display: flex; gap: 0.4rem; flex-wrap: wrap; justify-content: flex-end;"></div>
                <button type="button" onclick="cerrarModalMorbAudit()" class="btn-audit-close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Subheader Status Banner -->
        <div id="morbAuditBanner"></div>

        <!-- Navigation Tabs (Desglose por Edad, Discrepancias, Pacientes) -->
        <div class="audit-tabs-bar">
            <button type="button" class="audit-tab-btn active" id="tabDesgloseBtn" onclick="cambiarAuditTab('desglose')">
                <i class="bi bi-bar-chart-steps"></i> Desglose por Edad
            </button>
            <button type="button" class="audit-tab-btn" id="tabDiscrepanciasBtn" onclick="cambiarAuditTab('discrepancias')">
                <i class="bi bi-exclamation-triangle-fill"></i> Discrepancias &amp; Hallazgos (<span id="badgeCountDiscrepancias">0</span>)
            </button>
            <button type="button" class="audit-tab-btn" id="tabPacientesBtn" onclick="cambiarAuditTab('pacientes')">
                <i class="bi bi-people-fill"></i> Pacientes Detallados (<span id="badgeCountPacientes">0</span>)
            </button>
        </div>

        <!-- Search input for Pacientes tab -->
        <div id="morbAuditSearchBox" class="modal-search-bar hidden">
            <div style="position: relative;">
                <i class="bi bi-search" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="text" id="morbAuditSearchInput" placeholder="Buscar por paciente, ID, médico, rango de edad, fecha..." oninput="filtrarPacientesAuditModal()" class="modal-search-input">
            </div>
        </div>

        <!-- Scrollable Body -->
        <div class="modal-scroll-body">
            <!-- Loading -->
            <div id="morbAuditLoading" style="padding: 3rem 0; text-align: center; color: var(--text-muted);">
                <div class="spinner-border text-warning mb-3" role="status"></div>
                <p style="font-size: 0.88rem; font-weight: 600;">Comparando registros de AT2-r N con Registros Globales y Morbilidad...</p>
            </div>

            <!-- Content -->
            <div id="morbAuditContent" class="hidden">
                <div id="viewDesglose"></div>
                <div id="viewDiscrepancias" class="hidden"></div>
                <div id="viewPacientes" class="hidden"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="audit-modal-footer">
            <span class="audit-footer-note">
                <i class="bi bi-info-circle"></i> Registros Globales (AT1) contiene la base cruda. AT2-r N cuenta personas únicas en 4 columnas. Morbilidad cuenta líneas de diagnósticos.
            </span>
            <button type="button" onclick="cerrarModalMorbAudit()" class="btn btn-secondary btn-sm" style="font-weight: 600; padding: 0.4rem 1.25rem;">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
    window.globalMedicosData = window.globalMedicosData || [];
    window.globalAuditData = null;

    $(document).off('click', '.cell-clickable').on('click', '.cell-clickable', function() {
        const rowIdx = $(this).data('row-idx');
        const colIdx = $(this).data('col-idx');

        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.set('row_idx', rowIdx);
        params.set('col_idx', colIdx);

        const modalEl = document.getElementById('cellDetailModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        modalEl.classList.remove('hidden');
        document.getElementById('modalLoadingState').classList.remove('hidden');
        document.getElementById('modalContentArea').classList.add('hidden');

        fetch(`{{ route('informes.at2r-n.cell-details') }}?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('modalLoadingState').classList.add('hidden');
                document.getElementById('modalContentArea').classList.remove('hidden');

                document.getElementById('modalConceptoTitle').innerHTML = `<i class="bi bi-stethoscope text-primary"></i> ${data.concepto}`;
                document.getElementById('modalColumnaSubTitle').innerText = `${data.columna_nombre}`;
                document.getElementById('modalTotalBadge').innerText = `${data.total_registros} Atenciones Totales`;

                window.globalMedicosData = data.medicos || [];
                renderMedicosModal(window.globalMedicosData);
            })
            .catch(err => {
                console.error('Error al cargar detalles:', err);
                document.getElementById('modalLoadingState').innerHTML = `<p class="text-danger font-weight-bold text-center">Error al obtener los detalles de la casilla.</p>`;
            });
    });

    function renderMedicosModal(medicos) {
        const container = document.getElementById('modalMedicosList');
        if (!medicos || medicos.length === 0) {
            container.innerHTML = `<div class="p-4 text-center text-muted" style="background: var(--bg-surface); border-radius: var(--radius-md); border: 1px solid var(--border-color);">No se encontraron registros para esta casilla.</div>`;
            return;
        }

        let html = '';
        medicos.forEach((m, i) => {
            let fechasHtml = m.fechas.map(f => `
                <span class="fecha-tag">
                    <i class="bi bi-calendar3 text-muted"></i>
                    ${f.fecha}: <strong>${f.count}</strong>
                </span>
            `).join('');

            html += `
                <div class="medico-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="medico-avatar">
                                ${i + 1}
                            </div>
                            <div>
                                <h4 class="medico-name">${m.medico}</h4>
                                <span class="medico-prof">${m.profesion}</span>
                            </div>
                        </div>
                        <span class="medico-badge-count">
                            ${m.count} ${m.count === 1 ? 'atención' : 'atenciones'}
                        </span>
                    </div>
                    <div style="margin-top: 0.75rem; padding-top: 0.6rem; border-top: 1px solid var(--border-color);">
                        <span style="font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.35rem;">
                            Desglose por Fecha (${m.fechas.length} fechas):
                        </span>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                            ${fechasHtml}
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function filtrarMedicosModal() {
        const query = document.getElementById('modalDoctorSearch').value.toLowerCase().trim();
        const filtered = (window.globalMedicosData || []).filter(m => 
            m.medico.toLowerCase().includes(query) || 
            m.profesion.toLowerCase().includes(query)
        );
        renderMedicosModal(filtered);
    }

    function cerrarModalDetalles() {
        document.getElementById('cellDetailModal').classList.add('hidden');
    }

    // ── AUDITORÍA DE MORBILIDAD AL CLICK EN BADGE ──
    $(document).off('click', '.morbilidad-clickable').on('click', '.morbilidad-clickable', function(e) {
        e.stopPropagation();
        const conceptKey = $(this).data('concept-key');
        const conceptLabel = $(this).data('concept-label');

        if (!conceptKey) return;

        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        params.set('concept_key', conceptKey);

        const modalEl = document.getElementById('morbilidadAuditModal');
        if (modalEl && modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        modalEl.classList.remove('hidden');
        document.getElementById('morbAuditLoading').classList.remove('hidden');
        document.getElementById('morbAuditContent').classList.add('hidden');

        document.getElementById('morbAuditTitle').innerHTML = `<i class="bi bi-clipboard2-pulse text-warning"></i> Auditoría: ${conceptLabel}`;

        fetch(`{{ route('informes.at2r-n.morbilidad-audit') }}?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                window.globalAuditData = data;
                document.getElementById('morbAuditLoading').classList.add('hidden');
                document.getElementById('morbAuditContent').classList.remove('hidden');

                renderMorbilidadAudit(data);
            })
            .catch(err => {
                console.error('Error al auditar Morbilidad:', err);
                document.getElementById('morbAuditLoading').innerHTML = `<p class="text-danger font-weight-bold text-center">Error al conectar con la base de datos de Morbilidad.</p>`;
            });
    });

    function renderMorbilidadAudit(data) {
        // Badges de totales con 4 fuentes: RG (AT1), AT2-r N, Morbilidad y TRANS-2
        const statsEl = document.getElementById('morbAuditStatsBadge');
        let transBadge = '';
        if (data.has_trans2) {
            transBadge = `<span class="audit-badge-at2" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:700;"><i class="bi bi-clock-history me-1"></i>TRANS-2 (Nuevas): ${data.trans2_total}</span>`;
        } else {
            transBadge = `<span class="audit-badge-at2" style="background:rgba(107,114,128,0.12);color:#9ca3af;border:1px solid rgba(107,114,128,0.25);font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:600;" title="TRANS-2 únicamente registra patologías nuevas"><i class="bi bi-dash-circle me-1"></i>TRANS-2: N/A (Solo Nuevas)</span>`;
        }

        statsEl.innerHTML = `
            <span class="audit-badge-at2" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:700;"><i class="bi bi-database me-1"></i>Reg. Globales: ${data.rg_total}</span>
            <span class="audit-badge-at2" style="font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:700;"><i class="bi bi-clipboard2-check me-1"></i>AT2-r N: ${data.at2_total}</span>
            <span class="audit-badge-morb" style="font-size:0.75rem;padding:3px 8px;border-radius:6px;font-weight:700;"><i class="bi bi-bar-chart-fill me-1"></i>Morbilidad: ${data.morb_total}</span>
            ${transBadge}
        `;

        // Banner con resumen
        const banner = document.getElementById('morbAuditBanner');
        if (data.cuadra && (data.rg_total === data.at2_total) && (!data.has_trans2 || data.trans2_total === data.at2_total)) {
            banner.className = 'banner-ok';
            banner.innerHTML = `<i class="bi bi-check-circle-fill"></i> <strong>Cuadre Exacto (100%):</strong> Los informes (Registros Globales: ${data.rg_total}, AT2-r N: ${data.at2_total}, Morbilidad: ${data.morb_total}${data.has_trans2 ? ', TRANS-2: ' + data.trans2_total : ''}) coinciden exactamente.`;
        } else {
            banner.className = 'banner-warn';
            let msg = `<i class="bi bi-exclamation-triangle-fill"></i> <strong>Discrepancia detectada:</strong> AT2-r N tiene <strong>${data.at2_total}</strong> pacientes, Morbilidad tiene <strong>${data.morb_total}</strong> diagnósticos${data.has_trans2 ? ', TRANS-2 (Nuevas) tiene <strong>' + data.trans2_total + '</strong>' : ''} y Registros Globales tiene <strong>${data.rg_total}</strong>. `;
            if (data.diferencia !== 0) {
                msg += `Diferencia neta de <strong>${Math.abs(data.diferencia)}</strong> registros entre AT2 y Morbilidad.`;
            }
            banner.innerHTML = msg;
        }

        const totalDiscrepancias = (data.faltantes_morb?.length || 0) + (data.duplicados?.length || 0) + (data.huerfanos?.length || 0) + (data.excluidos_profesion?.length || 0);
        document.getElementById('badgeCountDiscrepancias').innerText = totalDiscrepancias;
        document.getElementById('badgeCountPacientes').innerText = data.pacientes?.length || 0;

        // 1. Render Desglose por Edad View
        renderDesgloseEdadView(data.desglose_edad || [], data);

        // 2. Render Discrepancias View
        renderDiscrepanciasAuditView(data);

        // 3. Render Pacientes View
        renderPacientesAuditView(data.pacientes || []);

        // Default to desglose tab
        cambiarAuditTab('desglose');
    }

    function renderDesgloseEdadView(desglose, data) {
        const viewEl = document.getElementById('viewDesglose');
        if (!desglose || desglose.length === 0) {
            viewEl.innerHTML = `<div class="audit-empty-state"><i class="bi bi-info-circle"></i><span>No hay desglose por edad disponible.</span></div>`;
            return;
        }

        let html = `
            <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:var(--radius-md, 8px);overflow:hidden;margin-bottom:1rem;">
                <div style="padding:0.75rem 1rem;background:var(--bg-subtle);border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.82rem;font-weight:700;color:var(--text-primary);"><i class="bi bi-table me-1.5 text-primary"></i> Matriz Comparativa por Rango de Edad</span>
                    <span style="font-size:0.72rem;color:var(--text-muted);">Comparación cruzada de 4 fuentes en tiempo real</span>
                </div>
                <div class="table-responsive mb-0">
                    <table class="table table-sm table-bordered mb-0" style="font-size:0.78rem;">
                        <thead>
                            <tr style="background:var(--bg-subtle);text-align:center;font-size:0.72rem;text-transform:uppercase;">
                                <th style="text-align:left;padding:7px 10px;">Rango de Edad</th>
                                <th style="width:105px;color:#10b981;"><i class="bi bi-database me-1"></i>Reg. Globales</th>
                                <th style="width:95px;color:#3b82f6;"><i class="bi bi-clipboard2-check me-1"></i>AT2-r N</th>
                                <th style="width:95px;color:#8b5cf6;"><i class="bi bi-bar-chart-fill me-1"></i>Morbilidad</th>
                                <th style="width:115px;color:#f59e0b;"><i class="bi bi-clock-history me-1"></i>TRANS-2 (N)</th>
                                <th style="width:85px;">Diferencia</th>
                                <th style="width:160px;">Diagnóstico / Estado</th>
                            </tr>
                        </thead>
                        <tbody>`;

        let sumRG = 0, sumAT2 = 0, sumMorb = 0, sumTrans2 = 0, sumDif = 0;

        desglose.forEach(row => {
            sumRG += row.rg_total;
            sumAT2 += row.at2_total;
            sumMorb += row.morb_total;
            if (row.trans2_total !== null) sumTrans2 += row.trans2_total;
            sumDif += row.diferencia;

            const isCuadra = row.cuadra;
            const bgRow = isCuadra ? '' : 'background:rgba(245,158,11,0.06);';
            const difBadge = isCuadra 
                ? `<span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);font-size:0.72rem;">0</span>`
                : `<span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3);font-size:0.72rem;">${row.diferencia > 0 ? '+' : ''}${row.diferencia}</span>`;

            const estadoBadge = isCuadra
                ? `<span class="text-success font-weight-bold"><i class="bi bi-check-circle-fill me-1"></i> Coincide</span>`
                : `<span class="text-warning font-weight-bold" style="font-size:0.72rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i> ${row.motivo_rango}</span>`;

            const trans2Cell = (row.trans2_total !== null)
                ? `<span class="font-weight-bold" style="color:#f59e0b;">${row.trans2_total}</span>`
                : `<span class="text-muted" style="font-size:0.70rem;">N/A</span>`;

            html += `
                <tr style="${bgRow}">
                    <td style="padding:6px 10px;font-weight:700;color:var(--text-primary);"><i class="bi bi-person-fill me-1 text-muted"></i> ${row.rango}</td>
                    <td style="text-align:center;font-weight:700;color:#10b981;">${row.rg_total}</td>
                    <td style="text-align:center;font-weight:700;color:#3b82f6;">${row.at2_total}</td>
                    <td style="text-align:center;font-weight:700;color:#8b5cf6;">${row.morb_total}</td>
                    <td style="text-align:center;">${trans2Cell}</td>
                    <td style="text-align:center;">${difBadge}</td>
                    <td style="padding:6px 10px;">${estadoBadge}</td>
                </tr>`;
        });

        const transFooter = data.has_trans2 
            ? `<span style="color:#f59e0b;">${sumTrans2}</span>` 
            : `<span class="text-muted" style="font-size:0.70rem;">N/A</span>`;

        html += `
                        </tbody>
                        <tfoot>
                            <tr style="background:var(--bg-subtle);font-weight:800;border-top:2px solid var(--border-color);">
                                <td style="padding:7px 10px;text-transform:uppercase;">TOTAL CONCEPTO</td>
                                <td style="text-align:center;color:#10b981;">${sumRG}</td>
                                <td style="text-align:center;color:#3b82f6;">${sumAT2}</td>
                                <td style="text-align:center;color:#8b5cf6;">${sumMorb}</td>
                                <td style="text-align:center;">${transFooter}</td>
                                <td style="text-align:center;">
                                    <span class="badge" style="background:${sumDif === 0 ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)'};color:${sumDif === 0 ? '#10b981' : '#ef4444'};border:1px solid ${sumDif === 0 ? 'rgba(16,185,129,0.4)' : 'rgba(239,68,68,0.4)'};font-size:0.75rem;">
                                        ${sumDif > 0 ? '+' : ''}${sumDif}
                                    </span>
                                </td>
                                <td style="padding:7px 10px;">
                                    ${sumDif === 0 
                                        ? '<span class="text-success font-weight-bold"><i class="bi bi-check-circle-fill me-1"></i> Cuadre 100%</span>' 
                                        : `<span class="text-danger font-weight-bold"><i class="bi bi-exclamation-octagon-fill me-1"></i> Diferencia neta: ${Math.abs(sumDif)}</span>`}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>`;

        viewEl.innerHTML = html;
    }

    function renderDiscrepanciasAuditView(data) {
        const discView = document.getElementById('viewDiscrepancias');
        let htmlDisc = '';

        const totalDiscrepancias = (data.faltantes_morb?.length || 0) + (data.duplicados?.length || 0) + (data.huerfanos?.length || 0) + (data.excluidos_profesion?.length || 0);

        if (totalDiscrepancias === 0) {
            htmlDisc = `
                <div class="audit-empty-state">
                    <i class="bi bi-check-circle text-success" style="font-size:2rem;"></i>
                    <strong>¡No hay discrepancias registradas!</strong>
                    <span>Todos los pacientes de AT2-r N y Morbilidad coinciden en su totalidad sin duplicados ni faltantes.</span>
                </div>`;
        } else {
            // 1. Faltantes en Morbilidad
            if (data.faltantes_morb && data.faltantes_morb.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title" style="color:#ef4444;border-color:rgba(239,68,68,0.3);background:rgba(239,68,68,0.08);padding:6px 12px;border-radius:6px;font-size:0.85rem;font-weight:700;">
                        <i class="bi bi-file-earmark-x-fill me-1"></i> Faltantes en Tabla de Morbilidad (${data.faltantes_morb.length})
                    </h4>`;
                data.faltantes_morb.forEach(f => {
                    let diagsList = (f.diagnosticos || []).map(x => `<span class="diag-slot-badge"><i class="bi bi-hash"></i>Casilla ${x.pos}: ${x.diag} (${x.cond})</span>`).join('');
                    htmlDisc += `
                        <div class="audit-disc-card huer" style="border-left:4px solid #ef4444;background:var(--bg-surface);padding:0.75rem;border-radius:6px;border:1px solid var(--border-color);margin-bottom:0.5rem;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.35rem;">
                                <div>
                                    <p class="disc-card-title huer" style="font-weight:700;color:var(--text-primary);margin:0;">
                                        ID #${f.registro_id} — ${f.paciente} <span style="font-weight:400;font-size:0.73rem;color:var(--text-muted);">(${f.edad}, ${f.sexo} | Rango: <strong>${f.rango_edad}</strong>)</span>
                                    </p>
                                    <span class="disc-card-meta" style="font-size:0.72rem;color:var(--text-muted);"><i class="bi bi-calendar3 me-1"></i>${f.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${f.medico} (${f.prof})</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(239,68,68,0.2);color:#f87171;border:1px solid rgba(239,68,68,0.35);font-size:0.72rem;padding:2px 8px;border-radius:4px;font-weight:700;">Faltante en Morbilidad</span>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:0.3rem;margin:0.35rem 0;">${diagsList}</div>
                            <div class="disc-card-reason huer" style="font-size:0.72rem;background:rgba(239,68,68,0.06);padding:4px 8px;border-radius:4px;color:var(--text-secondary);">
                                <i class="bi bi-info-circle text-danger me-1"></i>
                                <span><strong>Causa:</strong> ${f.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }

            // 2. Duplicados
            if (data.duplicados && data.duplicados.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title dup" style="color:#f59e0b;border-color:rgba(245,158,11,0.3);background:rgba(245,158,11,0.08);padding:6px 12px;border-radius:6px;font-size:0.85rem;font-weight:700;">
                        <i class="bi bi-arrow-repeat me-1"></i> Diagnósticos Duplicados en la Misma Consulta (${data.duplicados.length})
                    </h4>`;
                data.duplicados.forEach(d => {
                    let diagsList = d.diagnosticos.map(x => `<span class="diag-slot-badge"><i class="bi bi-hash"></i>Casilla ${x.pos}: ${x.diag} (${x.cond})</span>`).join('');
                    htmlDisc += `
                        <div class="audit-disc-card dup" style="border-left:4px solid #f59e0b;background:var(--bg-surface);padding:0.75rem;border-radius:6px;border:1px solid var(--border-color);margin-bottom:0.5rem;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.35rem;">
                                <div>
                                    <p class="disc-card-title dup" style="font-weight:700;color:var(--text-primary);margin:0;">
                                        ID #${d.registro_id} — ${d.paciente} <span style="font-weight:400;font-size:0.73rem;color:var(--text-muted);">(Rango: <strong>${d.rango_edad}</strong>)</span>
                                    </p>
                                    <span class="disc-card-meta" style="font-size:0.72rem;color:var(--text-muted);"><i class="bi bi-calendar3 me-1"></i>${d.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${d.medico} (${d.prof})</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.35);font-size:0.72rem;padding:2px 8px;border-radius:4px;font-weight:700;">${d.count}x en la consulta</span>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:0.3rem;margin:0.35rem 0;">${diagsList}</div>
                            <div class="disc-card-reason dup" style="font-size:0.72rem;background:rgba(245,158,11,0.06);padding:4px 8px;border-radius:4px;color:var(--text-secondary);">
                                <i class="bi bi-info-circle text-warning me-1"></i>
                                <span><strong>Explicación:</strong> ${d.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }

            // 3. Huérfanos
            if (data.huerfanos && data.huerfanos.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title huer" style="color:#ef4444;border-color:rgba(239,68,68,0.3);background:rgba(239,68,68,0.08);padding:6px 12px;border-radius:6px;font-size:0.85rem;font-weight:700;">
                        <i class="bi bi-ghost me-1"></i> Registros Huérfanos en Morbilidad (${data.huerfanos.length})
                    </h4>`;
                data.huerfanos.forEach(h => {
                    htmlDisc += `
                        <div class="audit-disc-card huer" style="border-left:4px solid #ef4444;background:var(--bg-surface);padding:0.75rem;border-radius:6px;border:1px solid var(--border-color);margin-bottom:0.5rem;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.35rem;">
                                <div>
                                    <p class="disc-card-title huer" style="font-weight:700;color:var(--text-primary);margin:0;">
                                        ID #${h.registro_id} — ${h.paciente} <span style="font-weight:400;font-size:0.73rem;color:var(--text-muted);">(Rango: <strong>${h.rango_edad}</strong>)</span>
                                    </p>
                                    <span class="disc-card-meta" style="font-size:0.72rem;color:var(--text-muted);"><i class="bi bi-calendar3 me-1"></i>${h.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${h.medico} (${h.prof})</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(239,68,68,0.2);color:#f87171;border:1px solid rgba(239,68,68,0.35);font-size:0.72rem;padding:2px 8px;border-radius:4px;font-weight:700;">Huérfano</span>
                            </div>
                            <div style="font-size:0.75rem;margin:0.25rem 0;color:var(--text-primary);">
                                Diagnóstico en Morbilidad: <strong>${h.diagnostico}</strong>
                            </div>
                            <div class="disc-card-reason huer" style="font-size:0.72rem;background:rgba(239,68,68,0.06);padding:4px 8px;border-radius:4px;color:var(--text-secondary);">
                                <i class="bi bi-info-circle text-danger me-1"></i>
                                <span><strong>Causa:</strong> ${h.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }

            // 4. Excluidos por profesión
            if (data.excluidos_profesion && data.excluidos_profesion.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title excl" style="color:#3b82f6;border-color:rgba(59,130,246,0.3);background:rgba(59,130,246,0.08);padding:6px 12px;border-radius:6px;font-size:0.85rem;font-weight:700;">
                        <i class="bi bi-person-x-fill me-1"></i> Profesiones fuera de las 4 columnas AT2-r N (${data.excluidos_profesion.length})
                    </h4>`;
                data.excluidos_profesion.forEach(ex => {
                    htmlDisc += `
                        <div class="audit-disc-card excl" style="border-left:4px solid #3b82f6;background:var(--bg-surface);padding:0.75rem;border-radius:6px;border:1px solid var(--border-color);margin-bottom:0.5rem;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div>
                                    <p class="disc-card-title excl" style="font-weight:700;color:var(--text-primary);margin:0;">
                                        ID #${ex.registro_id} — ${ex.paciente} <span style="font-weight:400;font-size:0.73rem;color:var(--text-muted);">(Rango: <strong>${ex.rango_edad}</strong>)</span>
                                    </p>
                                    <span class="disc-card-meta" style="font-size:0.72rem;color:var(--text-muted);"><i class="bi bi-calendar3 me-1"></i>${ex.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${ex.medico}</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(59,130,246,0.2);color:#60a5fa;border:1px solid rgba(59,130,246,0.35);font-size:0.72rem;padding:2px 8px;border-radius:4px;font-weight:700;">${ex.prof}</span>
                            </div>
                            <div class="disc-card-reason excl" style="font-size:0.72rem;background:rgba(59,130,246,0.06);padding:4px 8px;border-radius:4px;color:var(--text-secondary);margin-top:0.35rem;">
                                <i class="bi bi-info-circle text-primary me-1"></i>
                                <span>${ex.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }
        }
        discView.innerHTML = htmlDisc;
    }

    function renderPacientesAuditView(pacientes) {
        const pacView = document.getElementById('viewPacientes');
        if (!pacientes || pacientes.length === 0) {
            pacView.innerHTML = `
                <div class="audit-empty-state">
                    <i class="bi bi-people" style="font-size:2rem;"></i>
                    <strong>Sin pacientes</strong>
                    <span>No hay pacientes en AT2-r N para este concepto.</span>
                </div>`;
            return;
        }

        let html = `<div style="display:flex;flex-direction:column;gap:0.4rem;">`;
        pacientes.forEach((p, i) => {
            let diagsHtml = p.diagnosticos.map(d =>
                `<span class="diag-slot-badge">${d.diag} (${d.cond})</span>`
            ).join('');

            const morbStatusBadge = p.en_morb 
                ? `<span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3);font-size:0.68rem;"><i class="bi bi-check-circle me-1"></i>En Morbilidad</span>`
                : `<span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3);font-size:0.68rem;"><i class="bi bi-x-circle me-1"></i>Falta en Morbilidad</span>`;

            html += `
                <div class="paciente-audit-card" style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:6px;padding:0.6rem 0.85rem;display:flex;align-items:center;justify-content:space-between;gap:0.75rem;">
                    <div style="display:flex;align-items:center;gap:0.65rem;min-width:0;flex:1;">
                        <span class="pac-num" style="font-size:0.75rem;font-weight:800;color:var(--color-primary);width:26px;text-align:center;">#${i + 1}</span>
                        <div style="min-width:0;">
                            <div style="display:flex;align-items:center;gap:0.4rem;flex-wrap:wrap;">
                                <span class="pac-name" style="font-weight:700;font-size:0.8rem;color:var(--text-primary);">${p.paciente}</span>
                                <span style="font-weight:500;font-size:0.72rem;color:var(--text-muted);">(ID: ${p.registro_id} — ${p.edad}, ${p.sexo})</span>
                                <span class="badge" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-color);font-size:0.68rem;"><i class="bi bi-tag me-1"></i>${p.rango_edad}</span>
                                ${morbStatusBadge}
                            </div>
                            <span class="pac-meta" style="font-size:0.72rem;color:var(--text-muted);display:block;margin-top:2px;">
                                <i class="bi bi-calendar3 me-1"></i>${p.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${p.medico} (${p.prof})
                            </span>
                        </div>
                    </div>
                    <div class="pac-diags" style="display:flex;flex-wrap:wrap;gap:0.25rem;">${diagsHtml}</div>
                </div>`;
        });
        html += `</div>`;
        pacView.innerHTML = html;
    }

    function cambiarAuditTab(tab) {
        const btnDesglose = document.getElementById('tabDesgloseBtn');
        const btnDisc = document.getElementById('tabDiscrepanciasBtn');
        const btnPac = document.getElementById('tabPacientesBtn');
        const viewDesglose = document.getElementById('viewDesglose');
        const viewDisc = document.getElementById('viewDiscrepancias');
        const viewPac = document.getElementById('viewPacientes');
        const searchBox = document.getElementById('morbAuditSearchBox');

        btnDesglose.classList.remove('active');
        btnDisc.classList.remove('active');
        btnPac.classList.remove('active');
        viewDesglose.classList.add('hidden');
        viewDisc.classList.add('hidden');
        viewPac.classList.add('hidden');
        searchBox.classList.add('hidden');

        if (tab === 'desglose') {
            btnDesglose.classList.add('active');
            viewDesglose.classList.remove('hidden');
        } else if (tab === 'discrepancias') {
            btnDisc.classList.add('active');
            viewDisc.classList.remove('hidden');
        } else {
            btnPac.classList.add('active');
            viewPac.classList.remove('hidden');
            searchBox.classList.remove('hidden');
        }
    }

    function filtrarPacientesAuditModal() {
        const query = document.getElementById('morbAuditSearchInput').value.toLowerCase().trim();
        if (!window.globalAuditData || !window.globalAuditData.pacientes) return;

        const filtered = window.globalAuditData.pacientes.filter(p => 
            p.paciente.toLowerCase().includes(query) ||
            p.medico.toLowerCase().includes(query) ||
            p.prof.toLowerCase().includes(query) ||
            (p.rango_edad && p.rango_edad.toLowerCase().includes(query)) ||
            String(p.registro_id).includes(query) ||
            p.fecha.includes(query)
        );
        renderPacientesAuditView(filtered);
    }

    function cerrarModalMorbAudit() {
        document.getElementById('morbilidadAuditModal').classList.add('hidden');
    }
</script>