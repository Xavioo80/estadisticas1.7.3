/**
 * ============================================================================
 * SING APP - EXCEL TABLE ENGINE (JAVASCRIPT MODULE)
 * Reusable class for high-performance in-memory tabular data rendering.
 *
 * Features:
 * - Progressive Chunk Rendering (Infinite Scroll on Passive Listener)
 * - In-Memory Fast Global Search (Debounced)
 * - Cascading Excel-Style Column Filters with Value Frequencies
 * - Date Hierarchy Tree Drilldown (Year -> Month -> Day)
 * - Additive Filtering ("Agregar selección al filtro actual")
 * - Column Sorting (Asc/Desc with Natural String, Numeric & Date sorting)
 * - Native XLSX Export via SheetJS with Auto-Column Sizing
 * - LocalStorage Filter Persistence
 * - Fullscreen Compatibility (HTML5 Fullscreen API + CSS Top-Layer)
 * ============================================================================
 */

(function (window, document) {
  'use strict';

  const MONTH_NAMES = {
    '01': 'Enero', '02': 'Febrero', '03': 'Marzo', '04': 'Abril',
    '05': 'Mayo', '06': 'Junio', '07': 'Julio', '08': 'Agosto',
    '09': 'Septiembre', '10': 'Octubre', '11': 'Noviembre', '12': 'Diciembre'
  };

  const SafeStorage = {
    get(key, fallback = null) {
      try {
        const v = window.localStorage ? localStorage.getItem(key) : null;
        return v ? JSON.parse(v) : fallback;
      } catch (e) {
        return fallback;
      }
    },
    set(key, value) {
      try {
        if (window.localStorage) localStorage.setItem(key, JSON.stringify(value));
      } catch (e) {}
    },
    remove(key) {
      try {
        if (window.localStorage) localStorage.removeItem(key);
      } catch (e) {}
    }
  };

  function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  class SingExcelTable {
    constructor(tableId, options = {}) {
      this.table = document.getElementById(tableId);
      if (!this.table) return;

      this.storagePrefix = options.storagePrefix || ('sing_excel_' + tableId);
      this.exportSheetName = options.exportSheetName || 'Datos';
      this.exportFileName = options.exportFileName || 'export_excel';
      this.dataScriptId = options.dataScriptId || 'registrosDataJson';
      this.CHUNK_SIZE = options.chunkSize || 200;
      this.actionsRenderer = options.actionsRenderer || null;

      this.tbody = this.table.querySelector('tbody');
      this.scrollContainer = this.table.closest('.excel-table-scroll');
      this.allRecords = [];
      this.filteredRecords = [];
      this.renderedCount = 0;
      this.activeFilters = {};
      this.globalSearchQuery = '';
      this.currentOpenCol = null;
      this.currentBackdrop = null;
      this.currentPopover = null;

      this.initData();
      this.restoreFiltersFromStorage();
      this.initEvents();
      this.applyFilters();
    }

    initData() {
      const dataEl = document.getElementById(this.dataScriptId);
      if (!dataEl) return;
      try {
        const raw = JSON.parse(dataEl.textContent);
        if (Array.isArray(raw)) {
          this.allRecords = raw.map((item, index) => {
            const cells = item.map(c => (c !== null && c !== undefined) ? String(c) : '');
            return {
              originalIndex: index + 1,
              cells: cells,
              searchText: cells.join(' ').toLowerCase()
            };
          });
        }
      } catch (e) {
        console.error('Error parsing dataset JSON:', e);
      }
      this.filteredRecords = [...this.allRecords];
    }

    restoreFiltersFromStorage() {
      const savedColFilters = SafeStorage.get(this.storagePrefix + '_col_filters');
      if (savedColFilters && typeof savedColFilters === 'object') {
        Object.entries(savedColFilters).forEach(([colIdx, arrVals]) => {
          if (Array.isArray(arrVals) && arrVals.length > 0) {
            this.activeFilters[parseInt(colIdx, 10)] = new Set(arrVals);
            const th = this.table.querySelector(`th[data-col="${colIdx}"]`);
            if (th) th.querySelector('.excel-filter-btn')?.classList.add('has-filter');
          }
        });
      }

      const savedSearch = SafeStorage.get(this.storagePrefix + '_search');
      if (savedSearch && typeof savedSearch === 'string') {
        this.globalSearchQuery = savedSearch;
        const sInput = document.getElementById('excelGlobalSearch');
        if (sInput) sInput.value = savedSearch;
      }
    }

    saveFiltersToStorage() {
      const colFiltersForStorage = {};
      Object.entries(this.activeFilters).forEach(([colIdx, setVals]) => {
        colFiltersForStorage[colIdx] = Array.from(setVals);
      });
      SafeStorage.set(this.storagePrefix + '_col_filters', colFiltersForStorage);
      SafeStorage.set(this.storagePrefix + '_search', this.globalSearchQuery);
    }

    initEvents() {
      const self = this;

      if (this.scrollContainer) {
        this.scrollContainer.addEventListener('scroll', () => {
          if (self.renderedCount >= self.filteredRecords.length) return;
          const distanceToBottom = self.scrollContainer.scrollHeight - self.scrollContainer.scrollTop - self.scrollContainer.clientHeight;
          if (distanceToBottom < 350) {
            self.renderNextChunk();
          }
        }, { passive: true });
      }

      this.tbody.addEventListener('click', (e) => {
        const td = e.target.closest('td');
        if (!td) return;
        if (e.target.tagName === 'BUTTON' || e.target.tagName === 'I') return;

        self.table.querySelectorAll('td.excel-cell-active').forEach(cell => {
          cell.classList.remove('excel-cell-active');
        });
        td.classList.add('excel-cell-active');
      });

      this.table.querySelectorAll('th[data-col]').forEach(th => {
        const colIndex = parseInt(th.getAttribute('data-col'), 10);
        const filterBtn = th.querySelector('.excel-filter-btn');
        if (filterBtn) {
          filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            self.openFilterPopover(th, colIndex);
          });
        }
      });

      const globalSearch = document.getElementById('excelGlobalSearch');
      if (globalSearch) {
        let searchTimer = null;
        globalSearch.addEventListener('input', (e) => {
          clearTimeout(searchTimer);
          searchTimer = setTimeout(() => {
            self.globalSearchQuery = e.target.value.toLowerCase().trim();
            self.applyFilters();
          }, 100);
        });
      }

      const resetAllBtn = document.getElementById('btnResetAllExcelFilters');
      if (resetAllBtn) {
        resetAllBtn.addEventListener('click', () => {
          self.activeFilters = {};
          self.globalSearchQuery = '';
          if (globalSearch) globalSearch.value = '';
          self.table.querySelectorAll('.excel-filter-btn.has-filter').forEach(btn => {
            btn.classList.remove('has-filter');
          });
          SafeStorage.remove(self.storagePrefix + '_col_filters');
          SafeStorage.remove(self.storagePrefix + '_search');
          self.applyFilters();
          if (window.SingApp && SingApp.toast) {
            SingApp.toast({ title: 'Filtros Limpiados', message: 'Se eliminaron todos los filtros de columna y búsquedas.', type: 'info' });
          }
        });
      }

      const exportXlsxBtn = document.getElementById('btnExportExcelXLSX');
      if (exportXlsxBtn) {
        exportXlsxBtn.addEventListener('click', () => self.exportXLSX());
      }
    }

    getDistinctValuesForColumn(colIndex) {
      const counts = {};
      const query = this.globalSearchQuery;
      const activeColEntries = Object.entries(this.activeFilters).filter(([cStr]) => parseInt(cStr, 10) !== colIndex);

      this.allRecords.forEach(item => {
        if (query && !item.searchText.includes(query)) return;

        for (const [cStr, allowedSet] of activeColEntries) {
          const c = parseInt(cStr, 10);
          const val = item.cells[c] || '';
          if (!allowedSet.has(val)) return;
        }

        const val = (item.cells[colIndex] || '').trim() || '(Vacío)';
        counts[val] = (counts[val] || 0) + 1;
      });

      return Object.entries(counts).sort((a, b) => {
        const numA = parseFloat(a[0]);
        const numB = parseFloat(b[0]);
        if (!isNaN(numA) && !isNaN(numB)) return numA - numB;
        return a[0].localeCompare(b[0], undefined, { numeric: true, sensitivity: 'base' });
      });
    }

    buildDateTreeHtml(distinctValues, currentActiveSet) {
      const tree = {};

      distinctValues.forEach(([dateStr, count]) => {
        const match = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})/) || dateStr.match(/^(\d{2})-(\d{2})-(\d{4})/);
        if (match) {
          let year, month, day;
          if (dateStr.indexOf('-') === 4) {
            [, year, month, day] = match;
          } else {
            [, day, month, year] = match;
          }
          if (!tree[year]) tree[year] = { count: 0, months: {} };
          if (!tree[year].months[month]) tree[year].months[month] = { count: 0, days: {} };

          tree[year].count += count;
          tree[year].months[month].count += count;
          tree[year].months[month].days[day] = { fullDate: dateStr, count: count };
        } else {
          const year = 'Otros';
          if (!tree[year]) tree[year] = { count: 0, months: { '00': { count: 0, days: {} } } };
          tree[year].count += count;
          tree[year].months['00'].count += count;
          tree[year].months['00'].days[dateStr] = { fullDate: dateStr, count: count };
        }
      });

      let treeHtml = '<div class="excel-date-tree">';
      Object.keys(tree).sort().reverse().forEach(year => {
        const yearData = tree[year];
        let monthsHtml = '';

        Object.keys(yearData.months).sort().forEach(month => {
          const monthData = yearData.months[month];
          const monthLabel = MONTH_NAMES[month] || month;
          let daysHtml = '';

          Object.keys(monthData.days).sort().forEach(day => {
            const dayData = monthData.days[day];
            const isChecked = !currentActiveSet || currentActiveSet.has(dayData.fullDate);

            daysHtml += `
              <label class="excel-tree-row excel-popover-list-item">
                <input type="checkbox" class="excel-val-cb excel-cb-day" data-date="${dayData.fullDate}" value="${dayData.fullDate}" ${isChecked ? 'checked' : ''}>
                <span>${parseInt(day, 10) || day}</span>
                <span class="item-count">${dayData.count}</span>
              </label>
            `;
          });

          monthsHtml += `
            <div class="excel-tree-node excel-node-month" data-month="${month}">
              <div class="excel-tree-row excel-popover-list-item">
                <button type="button" class="excel-tree-toggle expanded"><i class="bi bi-chevron-right"></i></button>
                <input type="checkbox" class="excel-cb-month" data-year="${year}" data-month="${month}">
                <span style="font-weight:600;">${monthLabel}</span>
                <span class="item-count">${monthData.count}</span>
              </div>
              <div class="excel-tree-children">
                ${daysHtml}
              </div>
            </div>
          `;
        });

        treeHtml += `
          <div class="excel-tree-node excel-node-year" data-year="${year}">
            <div class="excel-tree-row excel-popover-list-item">
              <button type="button" class="excel-tree-toggle expanded"><i class="bi bi-chevron-right"></i></button>
              <input type="checkbox" class="excel-cb-year" data-year="${year}">
              <span style="font-weight:700;">${year}</span>
              <span class="item-count">${yearData.count}</span>
            </div>
            <div class="excel-tree-children">
              ${monthsHtml}
            </div>
          </div>
        `;
      });
      treeHtml += '</div>';

      return treeHtml;
    }

    openFilterPopover(th, colIndex) {
      this.closeFilterPopover();
      this.currentOpenCol = colIndex;

      const colTitle = th.getAttribute('data-title') || 'Columna';
      const isDateCol = th.getAttribute('data-type') === 'date';
      const distinctValues = this.getDistinctValuesForColumn(colIndex);
      const currentActiveSet = this.activeFilters[colIndex];
      const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';

      const backdrop = document.createElement('div');
      backdrop.className = 'excel-popover-backdrop';
      backdrop.addEventListener('click', () => this.closeFilterPopover());

      const popover = document.createElement('div');
      popover.className = 'excel-filter-popover';
      popover.setAttribute('data-theme', activeTheme);

      const rect = th.getBoundingClientRect();
      popover.style.position = 'fixed';
      popover.style.top = (rect.bottom + 2) + 'px';
      const leftPos = Math.min(rect.left, window.innerWidth - 300);
      popover.style.left = Math.max(10, leftPos) + 'px';

      let listItemsHtml = '';
      if (isDateCol) {
        listItemsHtml = this.buildDateTreeHtml(distinctValues, currentActiveSet);
      } else {
        distinctValues.forEach(([val, count]) => {
          const isChecked = !currentActiveSet || currentActiveSet.has(val);
          listItemsHtml += `
            <label class="excel-popover-list-item">
              <input type="checkbox" class="excel-val-cb" value="${val.replace(/"/g, '&quot;')}" ${isChecked ? 'checked' : ''}>
              <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escHtml(val)}</span>
              <span class="item-count">${count}</span>
            </label>
          `;
        });
      }

      popover.innerHTML = `
        <div class="excel-popover-header">
          <span><i class="bi bi-funnel text-primary"></i> Filtrar: ${escHtml(colTitle)}</span>
          <button type="button" class="btn-close-popover" style="background:transparent; border:none; color:var(--text-muted); cursor:pointer; font-size:1.1rem; line-height:1;">&times;</button>
        </div>
        <div class="excel-popover-actions">
          <button type="button" class="excel-popover-action-item" id="popoverSortAsc">
            <i class="bi bi-sort-alpha-down text-primary"></i> ${isDateCol ? 'Ordenar Antiguo a Reciente' : 'Ordenar A a Z / Menor a Mayor'}
          </button>
          <button type="button" class="excel-popover-action-item" id="popoverSortDesc">
            <i class="bi bi-sort-alpha-down-alt text-primary"></i> ${isDateCol ? 'Ordenar Reciente a Antiguo' : 'Ordenar Z a A / Mayor a Menor'}
          </button>
          ${currentActiveSet ? `
          <button type="button" class="excel-popover-action-item text-danger" id="popoverClearColFilter">
            <i class="bi bi-x-circle text-danger"></i> Borrar filtro de ${escHtml(colTitle)}
          </button>` : ''}
        </div>
        <div class="excel-popover-search">
          <input type="text" id="popoverSearchInput" placeholder="Buscar en ${escHtml(colTitle)}...">
          <label id="popoverAddToFilterWrap" class="excel-popover-add-wrap" style="display: ${currentActiveSet ? 'flex' : 'none'};">
            <input type="checkbox" id="popoverAddToFilterCb" ${currentActiveSet ? 'checked' : ''}>
            <span>Agregar selección al filtro actual</span>
          </label>
        </div>
        <div class="excel-popover-list">
          <label class="excel-popover-list-item" style="font-weight: 700; border-bottom: 1px dashed var(--border-color); padding-bottom: 0.3rem;">
            <input type="checkbox" id="popoverSelectAllVals" ${!currentActiveSet || currentActiveSet.size === distinctValues.length ? 'checked' : ''}>
            <span>(Seleccionar Todo)</span>
          </label>
          <div id="popoverItemsContainer">
            ${listItemsHtml}
          </div>
        </div>
        <div class="excel-popover-footer">
          <button type="button" class="btn btn-subtle btn-sm" id="popoverBtnCancel" style="padding: 0.2rem 0.6rem; font-size: 0.78rem;">Cancelar</button>
          <button type="button" class="btn btn-primary btn-sm" id="popoverBtnApply" style="padding: 0.2rem 0.7rem; font-size: 0.78rem;">Aplicar</button>
        </div>
      `;

      const activeContainer = this.table.closest('.sing-card-excel-fullscreen') || document.body;
      activeContainer.appendChild(backdrop);
      activeContainer.appendChild(popover);

      this.currentBackdrop = backdrop;
      this.currentPopover = popover;

      popover.querySelector('.btn-close-popover').addEventListener('click', () => this.closeFilterPopover());
      popover.querySelector('#popoverBtnCancel').addEventListener('click', () => this.closeFilterPopover());

      popover.querySelector('#popoverSortAsc').addEventListener('click', () => {
        this.sortTable(colIndex, 'asc');
        this.closeFilterPopover();
      });
      popover.querySelector('#popoverSortDesc').addEventListener('click', () => {
        this.sortTable(colIndex, 'desc');
        this.closeFilterPopover();
      });

      const clearBtn = popover.querySelector('#popoverClearColFilter');
      if (clearBtn) {
        clearBtn.addEventListener('click', () => {
          delete this.activeFilters[colIndex];
          th.querySelector('.excel-filter-btn')?.classList.remove('has-filter');
          this.applyFilters();
          this.closeFilterPopover();
        });
      }

      const searchInput = popover.querySelector('#popoverSearchInput');
      const selectAllCb = popover.querySelector('#popoverSelectAllVals');
      const addToFilterWrap = popover.querySelector('#popoverAddToFilterWrap');

      setTimeout(() => {
        if (searchInput) {
          searchInput.focus();
          searchInput.select();
        }
      }, 50);

      searchInput.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        let matchingCount = 0;

        if (addToFilterWrap) {
          addToFilterWrap.style.display = currentActiveSet ? 'flex' : 'none';
        }

        popover.querySelectorAll('.excel-popover-list-item').forEach(item => {
          if (item.querySelector('#popoverSelectAllVals')) return;
          const text = item.innerText.toLowerCase();
          const matches = text.includes(q);
          item.style.display = matches ? 'flex' : 'none';
          if (matches) {
            matchingCount++;
            if (q.length > 0) {
              const cb = item.querySelector('.excel-val-cb');
              if (cb) cb.checked = true;
            }
          }
        });

        if (selectAllCb) {
          selectAllCb.checked = matchingCount > 0;
          const span = selectAllCb.nextElementSibling;
          if (span) {
            span.textContent = q.length > 0 ? `(Seleccionar ${matchingCount} coincidencias)` : '(Seleccionar Todo)';
          }
        }
      });

      selectAllCb.addEventListener('change', (e) => {
        const isChecked = e.target.checked;
        popover.querySelectorAll('.excel-val-cb').forEach(cb => {
          const item = cb.closest('.excel-popover-list-item');
          if (item && item.style.display !== 'none') {
            cb.checked = isChecked;
          }
        });
      });

      popover.querySelectorAll('.excel-tree-toggle').forEach(toggleBtn => {
        toggleBtn.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          toggleBtn.classList.toggle('expanded');
          const node = toggleBtn.closest('.excel-tree-node');
          const children = node.querySelector('.excel-tree-children');
          if (children) children.classList.toggle('collapsed');
        });
      });

      popover.querySelectorAll('.excel-cb-year').forEach(yearCb => {
        yearCb.addEventListener('change', (e) => {
          const node = yearCb.closest('.excel-tree-node');
          node.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = e.target.checked);
        });
      });

      popover.querySelectorAll('.excel-cb-month').forEach(monthCb => {
        monthCb.addEventListener('change', (e) => {
          const node = monthCb.closest('.excel-tree-node');
          node.querySelectorAll('.excel-cb-day').forEach(cb => cb.checked = e.target.checked);
        });
      });

      const applyFilterAction = () => {
        const isSearching = searchInput && searchInput.value.trim().length > 0;
        const addToFilterCb = popover.querySelector('#popoverAddToFilterCb');
        const isAddMode = addToFilterCb && addToFilterCb.checked && currentActiveSet;

        let selectedVals = new Set();

        if (isSearching) {
          popover.querySelectorAll('.excel-popover-list-item').forEach(item => {
            if (item.style.display !== 'none') {
              const cb = item.querySelector('.excel-val-cb:checked');
              if (cb) selectedVals.add(cb.value);
            }
          });

          if (isAddMode) {
            selectedVals = new Set([...currentActiveSet, ...selectedVals]);
          }
        } else {
          popover.querySelectorAll('.excel-popover-list-item').forEach(item => {
            const cb = item.querySelector('.excel-val-cb:checked');
            if (cb) selectedVals.add(cb.value);
          });
        }

        if (selectedVals.size >= distinctValues.length || selectedVals.size === 0) {
          delete this.activeFilters[colIndex];
          th.querySelector('.excel-filter-btn')?.classList.remove('has-filter');
        } else {
          this.activeFilters[colIndex] = selectedVals;
          th.querySelector('.excel-filter-btn')?.classList.add('has-filter');
        }

        this.saveFiltersToStorage();
        this.applyFilters();
        this.closeFilterPopover();
      };

      popover.querySelector('#popoverBtnApply').addEventListener('click', applyFilterAction);

      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          applyFilterAction();
        } else if (e.key === 'Escape') {
          e.preventDefault();
          this.closeFilterPopover();
        }
      });

      popover.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          e.preventDefault();
          this.closeFilterPopover();
        }
      });
    }

    closeFilterPopover() {
      if (this.currentPopover) {
        this.currentPopover.remove();
        this.currentPopover = null;
      }
      if (this.currentBackdrop) {
        this.currentBackdrop.remove();
        this.currentBackdrop = null;
      }
      this.currentOpenCol = null;
    }

    sortTable(colIndex, direction = 'asc') {
      this.filteredRecords.sort((a, b) => {
        const valA = (a.cells[colIndex] || '').trim();
        const valB = (b.cells[colIndex] || '').trim();

        const numA = parseFloat(valA);
        const numB = parseFloat(valB);
        if (!isNaN(numA) && !isNaN(numB)) {
          return direction === 'asc' ? numA - numB : numB - numA;
        }

        const cmp = valA.localeCompare(valB, undefined, { numeric: true, sensitivity: 'base' });
        return direction === 'asc' ? cmp : -cmp;
      });

      this.tbody.innerHTML = '';
      this.renderedCount = 0;
      if (this.scrollContainer) this.scrollContainer.scrollTop = 0;
      this.renderNextChunk();
    }

    applyFilters() {
      const activeColFilters = Object.entries(this.activeFilters);
      const hasColFilters = activeColFilters.length > 0;
      const query = this.globalSearchQuery;

      if (!hasColFilters && !query) {
        this.filteredRecords = [...this.allRecords];
      } else {
        this.filteredRecords = this.allRecords.filter(item => {
          if (query && !item.searchText.includes(query)) return false;

          for (const [colIndexStr, allowedSet] of activeColFilters) {
            const c = parseInt(colIndexStr, 10);
            const val = item.cells[c] || '';
            if (!allowedSet.has(val)) return false;
          }
          return true;
        });
      }

      this.tbody.innerHTML = '';
      this.renderedCount = 0;
      if (this.scrollContainer) this.scrollContainer.scrollTop = 0;

      if (this.filteredRecords.length === 0) {
        this.tbody.innerHTML = `
          <tr class="excel-no-records-row">
            <td colspan="55" style="text-align: center; padding: 2.5rem 1rem; color: var(--text-muted);">
              <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 0.45rem; opacity: 0.6;"></i>
              <strong>No se encontraron registros que coincidan con los filtros</strong>
              <div style="font-size: 0.78rem; margin-top: 0.25rem;">Prueba ajustando los filtros de columna o limpiando la búsqueda.</div>
            </td>
          </tr>
        `;
        this.updateFooter();
      } else {
        this.renderNextChunk();
      }

      this.saveFiltersToStorage();
    }

    renderNextChunk() {
      if (this.renderedCount >= this.filteredRecords.length) return;

      const nextChunk = this.filteredRecords.slice(this.renderedCount, this.renderedCount + this.CHUNK_SIZE);
      const fragment = document.createDocumentFragment();

      nextChunk.forEach((item, idx) => {
        const tr = document.createElement('tr');
        const visualRowIndex = this.renderedCount + idx + 1;

        let rowHtml = `<td class="col-row-num" tabindex="0">${visualRowIndex}</td>`;
        for (let i = 0; i < item.cells.length; i++) {
          const val = item.cells[i];
          const isNumeric = val !== '' && !isNaN(val) && !val.startsWith('0');
          const alignStyle = isNumeric ? 'text-align: right;' : '';
          rowHtml += `<td tabindex="0" style="${alignStyle}">${escHtml(val)}</td>`;
        }
        if (typeof this.actionsRenderer === 'function') {
          rowHtml += `<td class="col-actions text-center" style="white-space: nowrap; width: 80px;">${this.actionsRenderer(item, visualRowIndex)}</td>`;
        }
        tr.innerHTML = rowHtml;
        fragment.appendChild(tr);
      });

      this.tbody.appendChild(fragment);
      this.renderedCount += nextChunk.length;
      this.updateFooter();
    }

    updateRecord(matchFn, updateFn) {
      let updated = false;
      this.allRecords.forEach(item => {
        if (matchFn(item)) {
          updateFn(item);
          item.searchText = item.cells.join(' ').toLowerCase();
          updated = true;
        }
      });
      if (updated) {
        this.applyFilters();
      }
      return updated;
    }

    deleteRecord(matchFn) {
      const beforeCount = this.allRecords.length;
      this.allRecords = this.allRecords.filter(item => !matchFn(item));
      if (this.allRecords.length !== beforeCount) {
        this.applyFilters();
        return true;
      }
      return false;
    }

    updateFooter() {
      const totalCount = this.allRecords.length;
      const filteredCount = this.filteredRecords.length;
      const rendered = this.renderedCount;

      const totalStatEl = document.getElementById('excelStatTotal');
      const filteredStatEl = document.getElementById('excelStatFiltered');
      const progressiveBadge = document.getElementById('excelProgressiveBadge');
      const progressiveText = document.getElementById('excelProgressiveText');
      const activeFiltersBadge = document.getElementById('excelActiveFiltersBadge');
      const activeFiltersCount = document.getElementById('excelActiveFiltersCount');
      const resetAllBtn = document.getElementById('btnResetAllExcelFilters');

      if (totalStatEl) totalStatEl.textContent = totalCount;

      if (filteredStatEl) {
        if (rendered < filteredCount) {
          filteredStatEl.textContent = `Mostrando ${rendered} de ${filteredCount}`;
          if (progressiveBadge) progressiveBadge.style.display = 'inline-flex';
          if (progressiveText) progressiveText.textContent = `Scroll para cargar +${Math.min(this.CHUNK_SIZE, filteredCount - rendered)}`;
        } else {
          filteredStatEl.textContent = `Mostrando todos (${filteredCount} de ${totalCount})`;
          if (progressiveBadge) progressiveBadge.style.display = 'none';
        }
      }

      const filterCount = Object.keys(this.activeFilters).length + (this.globalSearchQuery ? 1 : 0);
      if (filterCount > 0) {
        if (activeFiltersBadge) activeFiltersBadge.style.display = 'inline-flex';
        if (activeFiltersCount) activeFiltersCount.textContent = filterCount;
        if (resetAllBtn) resetAllBtn.style.display = 'inline-flex';
      } else {
        if (activeFiltersBadge) activeFiltersBadge.style.display = 'none';
        if (resetAllBtn) resetAllBtn.style.display = 'none';
      }
    }

    exportXLSX() {
      if (this.filteredRecords.length === 0) {
        if (window.SingApp && SingApp.toast) {
          SingApp.toast({ title: 'Exportación', message: 'No hay filas coincidentes para exportar.', type: 'warning' });
        }
        return;
      }

      if (typeof window.XLSX === 'undefined') {
        if (window.SingApp && SingApp.toast) {
          SingApp.toast({ title: 'Cargando', message: 'Cargando módulo de exportación Excel...', type: 'info' });
        }
        return;
      }

      const headers = Array.from(this.table.querySelectorAll('thead th[data-title]')).map(th => th.getAttribute('data-title') || '');
      const aoa = [headers];

      this.filteredRecords.forEach(item => {
        const rowData = item.cells.map(val => {
          const trimmed = String(val).trim();
          const num = Number(trimmed);
          if (trimmed !== '' && !isNaN(num) && !trimmed.startsWith('0') && trimmed.length < 10) {
            return num;
          }
          return trimmed;
        });
        aoa.push(rowData);
      });

      const ws = XLSX.utils.aoa_to_sheet(aoa);

      const colWidths = headers.map((h, colIdx) => {
        let maxLen = h.length;
        const sampleLimit = Math.min(aoa.length, 120);
        for (let r = 0; r < sampleLimit; r++) {
          const cellVal = aoa[r][colIdx] !== undefined ? String(aoa[r][colIdx]) : '';
          if (cellVal.length > maxLen) {
            maxLen = Math.min(cellVal.length, 45);
          }
        }
        return { wch: Math.max(maxLen + 3, 7) };
      });
      ws['!cols'] = colWidths;

      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, this.exportSheetName);

      const fileName = `${this.exportFileName}_${new Date().toISOString().slice(0, 10)}.xlsx`;
      XLSX.writeFile(wb, fileName);

      if (window.SingApp && SingApp.toast) {
        SingApp.toast({
          title: 'Exportación Exitosa',
          message: `Se descargó la tabla con ${this.filteredRecords.length} registros (${fileName}).`,
          type: 'success'
        });
      }
    }
  }

  window.SingExcelTable = SingExcelTable;
})(window, document);
