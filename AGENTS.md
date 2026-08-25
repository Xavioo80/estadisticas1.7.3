# Reglas Globales del Proyecto (Estadísticas 1.7 / Sing App)

## 1. Adaptación Obligatoria a Modo Oscuro & Modo Claro
- **Requisito Fundamental**: Cada componente, vista, tabla, modal, formulario, tarjeta, gráfico o plugin nuevo o modificado **DEBE adaptarse automáticamente al Modo Oscuro (`data-theme="dark"`) y al Modo Claro (`data-theme="light"`)**.
- **Variables CSS del Sistema**:
  - No usar colores hexadecimales fijos (#fff, #000, #f8f9fa). Usar variables de `sing-theme.css` (`var(--bg-surface)`, `var(--text-primary)`, `var(--border-color)`, `var(--input-bg)`, etc.).
- **Plugins y Librerías Externas**:
  - Toda librería de terceros (Select2, ApexCharts, Flatpickr, etc.) debe tener sus reglas de estilo en `sing-components.css` adaptadas a ambos temas.
- **Sin Parpadeo (Zero FOUC)**:
  - Todo cambio visual debe respetar la inicialización sincrónica de tema en `<head>`.
