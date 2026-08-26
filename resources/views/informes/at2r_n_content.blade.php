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

        <!-- Navigation Tabs -->
        <div class="audit-tabs-bar">
            <button type="button" class="audit-tab-btn active" id="tabDiscrepanciasBtn" onclick="cambiarAuditTab('discrepancias')">
                <i class="bi bi-exclamation-triangle-fill"></i> Discrepancias &amp; Hallazgos (<span id="badgeCountDiscrepancias">0</span>)
            </button>
            <button type="button" class="audit-tab-btn" id="tabPacientesBtn" onclick="cambiarAuditTab('pacientes')">
                <i class="bi bi-people-fill"></i> Pacientes en AT2-r N (<span id="badgeCountPacientes">0</span>)
            </button>
        </div>

        <!-- Search input for Pacientes tab -->
        <div id="morbAuditSearchBox" class="modal-search-bar hidden">
            <div style="position: relative;">
                <i class="bi bi-search" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="text" id="morbAuditSearchInput" placeholder="Buscar por paciente, ID, médico, fecha..." oninput="filtrarPacientesAuditModal()" class="modal-search-input">
            </div>
        </div>

        <!-- Scrollable Body -->
        <div class="modal-scroll-body">
            <!-- Loading -->
            <div id="morbAuditLoading" style="padding: 3rem 0; text-align: center; color: var(--text-muted);">
                <div class="spinner-border text-warning mb-3" role="status"></div>
                <p style="font-size: 0.88rem; font-weight: 600;">Comparando registros de AT2-r N con la base de Morbilidad...</p>
            </div>

            <!-- Content -->
            <div id="morbAuditContent" class="hidden">
                <div id="viewDiscrepancias"></div>
                <div id="viewPacientes" class="hidden"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="audit-modal-footer">
            <span class="audit-footer-note">
                <i class="bi bi-info-circle"></i> AT2-r N cuenta personas únicas. Morbilidad cuenta líneas de diagnósticos.
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
        // Badges de totales con nuevas clases CSS
        const statsEl = document.getElementById('morbAuditStatsBadge');
        statsEl.innerHTML = `
            <span class="audit-badge-at2"><i class="bi bi-clipboard2-check me-1"></i>AT2-r N: ${data.at2_total}</span>
            <span class="audit-badge-morb"><i class="bi bi-bar-chart-fill me-1"></i>Morbilidad: ${data.morb_total}</span>
        `;

        // Banner con clases CSS en lugar de clases inline hardcodeadas
        const banner = document.getElementById('morbAuditBanner');
        if (data.cuadra) {
            banner.className = 'banner-ok';
            banner.innerHTML = `<i class="bi bi-check-circle-fill"></i> <strong>Cuadre Exacto (100%):</strong> Todas las atenciones de AT2-r N coinciden con los registros de Morbilidad.`;
        } else {
            banner.className = 'banner-warn';
            banner.innerHTML = `<i class="bi bi-exclamation-triangle-fill"></i> <strong>Diferencia de ${Math.abs(data.diferencia)} registros:</strong> AT2-r N tiene ${data.at2_total} pacientes atendidos y Morbilidad tiene ${data.morb_total} líneas de diagnóstico registradas.`;
        }

        const totalDiscrepancias = (data.duplicados?.length || 0) + (data.huerfanos?.length || 0) + (data.excluidos_profesion?.length || 0);
        document.getElementById('badgeCountDiscrepancias').innerText = totalDiscrepancias;
        document.getElementById('badgeCountPacientes').innerText = data.pacientes?.length || 0;

        // Render Discrepancias View
        const discView = document.getElementById('viewDiscrepancias');
        let htmlDisc = '';

        if (totalDiscrepancias === 0) {
            htmlDisc = `
                <div class="audit-empty-state">
                    <i class="bi bi-check-circle text-success"></i>
                    <strong>¡No hay discrepancias!</strong>
                    <span>Los registros en AT2-r N y Morbilidad coinciden en su totalidad.</span>
                </div>
            `;
        } else {
            // 1. Duplicados
            if (data.duplicados && data.duplicados.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title dup">
                        <i class="bi bi-arrow-repeat"></i> Diagnósticos Duplicados en la Misma Consulta (${data.duplicados.length})
                    </h4>`;
                data.duplicados.forEach(d => {
                    let diagsList = d.diagnosticos.map(x => `<span class="diag-slot-badge"><i class="bi bi-hash" style="font-size:0.65rem;"></i>Casilla ${x.pos}: ${x.diag} (${x.cond})</span>`).join('');
                    htmlDisc += `
                        <div class="audit-disc-card dup">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.45rem;">
                                <div>
                                    <p class="disc-card-title dup">ID #${d.registro_id} — ${d.paciente}</p>
                                    <span class="disc-card-meta"><i class="bi bi-calendar3 me-1"></i>${d.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${d.medico} (${d.prof})</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(245,158,11,0.2);color:#fbbf24;border:1px solid rgba(245,158,11,0.35);">${d.count}x en la consulta</span>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:0.3rem;margin:0.4rem 0;">${diagsList}</div>
                            <div class="disc-card-reason dup">
                                <i class="bi bi-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                                <span><strong>Explicación:</strong> ${d.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }

            // 2. Huérfanos
            if (data.huerfanos && data.huerfanos.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title huer">
                        <i class="bi bi-ghost"></i> Registros Huérfanos en Morbilidad (${data.huerfanos.length})
                    </h4>`;
                data.huerfanos.forEach(h => {
                    htmlDisc += `
                        <div class="audit-disc-card huer">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; margin-bottom:0.45rem;">
                                <div>
                                    <p class="disc-card-title huer">ID #${h.registro_id} — ${h.paciente}</p>
                                    <span class="disc-card-meta"><i class="bi bi-calendar3 me-1"></i>${h.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${h.medico} (${h.prof})</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(239,68,68,0.2);color:#f87171;border:1px solid rgba(239,68,68,0.35);">Huérfano</span>
                            </div>
                            <div style="font-size:0.8rem;margin:0.3rem 0;color:var(--text-primary);">
                                Diagnóstico en Morbilidad: <strong>${h.diagnostico}</strong>
                            </div>
                            <div class="disc-card-reason huer">
                                <i class="bi bi-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                                <span><strong>Causa:</strong> ${h.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }

            // 3. Excluidos por profesión
            if (data.excluidos_profesion && data.excluidos_profesion.length > 0) {
                htmlDisc += `<div style="margin-bottom: 1.35rem;">
                    <h4 class="audit-section-title excl">
                        <i class="bi bi-person-x-fill"></i> Profesiones fuera de las 4 columnas AT2-r N (${data.excluidos_profesion.length})
                    </h4>`;
                data.excluidos_profesion.forEach(ex => {
                    htmlDisc += `
                        <div class="audit-disc-card excl">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                                <div>
                                    <p class="disc-card-title excl">ID #${ex.registro_id} — ${ex.paciente}</p>
                                    <span class="disc-card-meta"><i class="bi bi-calendar3 me-1"></i>${ex.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${ex.medico}</span>
                                </div>
                                <span class="disc-card-badge" style="background:rgba(59,130,246,0.2);color:#60a5fa;border:1px solid rgba(59,130,246,0.35);">${ex.prof}</span>
                            </div>
                            <div class="disc-card-reason excl" style="margin-top:0.5rem;">
                                <i class="bi bi-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                                <span>${ex.motivo}</span>
                            </div>
                        </div>`;
                });
                htmlDisc += `</div>`;
            }
        }
        discView.innerHTML = htmlDisc;

        // Render Pacientes View
        renderPacientesAuditView(data.pacientes || []);

        // Default to discrepancias if any, else pacientes
        if (totalDiscrepancias > 0) {
            cambiarAuditTab('discrepancias');
        } else {
            cambiarAuditTab('pacientes');
        }
    }

    function renderPacientesAuditView(pacientes) {
        const pacView = document.getElementById('viewPacientes');
        if (!pacientes || pacientes.length === 0) {
            pacView.innerHTML = `
                <div class="audit-empty-state">
                    <i class="bi bi-people"></i>
                    <strong>Sin pacientes</strong>
                    <span>No hay pacientes en AT2-r N para este concepto.</span>
                </div>`;
            return;
        }

        let html = `<div style="display:flex;flex-direction:column;gap:0;">`;
        pacientes.forEach((p, i) => {
            let diagsHtml = p.diagnosticos.map(d =>
                `<span class="diag-slot-badge">${d.diag} (${d.cond})</span>`
            ).join('');
            html += `
                <div class="paciente-audit-card">
                    <div style="display:flex;align-items:center;gap:0.65rem;min-width:0;flex:1;">
                        <span class="pac-num">#${i + 1}</span>
                        <div style="min-width:0;">
                            <span class="pac-name">${p.paciente} <span style="font-weight:400;font-size:0.73rem;color:var(--text-muted);">(ID: ${p.registro_id} — ${p.edad}, ${p.sexo})</span></span>
                            <span class="pac-meta"><i class="bi bi-calendar3 me-1"></i>${p.fecha} &nbsp;|&nbsp; <i class="bi bi-person-badge me-1"></i>${p.medico} (${p.prof})</span>
                        </div>
                    </div>
                    <div class="pac-diags">${diagsHtml}</div>
                </div>`;
        });
        html += `</div>`;
        pacView.innerHTML = html;
    }

    function cambiarAuditTab(tab) {
        const btnDisc = document.getElementById('tabDiscrepanciasBtn');
        const btnPac = document.getElementById('tabPacientesBtn');
        const viewDisc = document.getElementById('viewDiscrepancias');
        const viewPac = document.getElementById('viewPacientes');
        const searchBox = document.getElementById('morbAuditSearchBox');

        if (tab === 'discrepancias') {
            btnDisc.classList.add('active');
            btnPac.classList.remove('active');
            viewDisc.classList.remove('hidden');
            viewPac.classList.add('hidden');
            searchBox.classList.add('hidden');
        } else {
            btnPac.classList.add('active');
            btnDisc.classList.remove('active');
            viewPac.classList.remove('hidden');
            viewDisc.classList.add('hidden');
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
            String(p.registro_id).includes(query) ||
            p.fecha.includes(query)
        );
        renderPacientesAuditView(filtered);
    }

    function cerrarModalMorbAudit() {
        document.getElementById('morbilidadAuditModal').classList.add('hidden');
    }
</script>