# Regla de Adaptación Obligatoria a Modo Oscuro

## Objetivo
Todo elemento, componente, interfaz, tabla, formulario, plugin o vista que se cree o modifique en este proyecto **DEBE contar obligatoriamente con adaptación completa y óptima tanto para Modo Oscuro (`data-theme="dark"`) como para Modo Claro (`data-theme="light"`)**.

---

## Directrices Técnicas de Implementación

1. **Uso Exclusivo de Variables del Sistema de Diseño (CSS Tokens)**:
   - Queda estrictamente prohibido el uso de colores fijos 'quemados' (ej. `#ffffff`, `#fff`, `#000000`, `#f8f9fa`) para fondos, bordes o textos.
   - Utilizar siempre las variables CSS globales definidas en `sing-theme.css`:
     - **Fondos**: `var(--bg-body)`, `var(--bg-surface)`, `var(--bg-surface-elevated)`, `var(--bg-surface-hover)`.
     - **Textos**: `var(--text-primary)`, `var(--text-secondary)`, `var(--text-muted)`.
     - **Bordes y Divisores**: `var(--border-color)`, `var(--border-subtle)`.
     - **Formularios e Inputs**: `var(--input-bg)`, `var(--input-border)`, `var(--input-text)`, `var(--input-placeholder)`, `var(--input-focus-ring)`.
     - **Tablas**: `var(--table-header-bg)`, `var(--table-header-text)`, `var(--table-border)`, `var(--table-row-hover)`.
     - **Acentos y Sombras**: `var(--color-primary)`, `var(--color-primary-rgb)`, `var(--shadow-sm)`, `var(--shadow-md)`, `var(--shadow-lg)`.

2. **Adaptación de Plugins y Librerías Externas**:
   - Cualquier plugin integrado (Select2, ApexCharts, SweetAlert2, Flatpickr, DataTables, etc.) debe tener sus estilos personalizados en `assets/css/sing-components.css` asegurando legibilidad, contraste y estilo visual idéntico al tema activo.
   - En el caso de gráficos (ApexCharts), deben responder al evento de cambio de tema (`sing:theme-change`) actualizando `theme.mode = 'dark' | 'light'`.

3. **Prevención de Parpadeo (Anti-FOUC)**:
   - Todas las vistas deben incluir o heredar el script sincrónico en `<head>` que lee `localStorage.getItem('sing_theme')` y la cookie `sing_theme` para establecer el atributo `data-theme` antes del primer renderizado visual del DOM.

4. **Verificación de Contraste y Accesibilidad**:
   - Todo texto o ícono debe tener suficiente contraste (mínimo ratio WCAG AA) sobre su fondo en ambos modos.
