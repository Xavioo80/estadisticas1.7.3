<!-- Footer -->
<footer class="app-footer">
  <div>
    &copy; {{ date('Y') }} <strong>{{ config('app.name', 'Estadísticas 1.7') }}</strong> &mdash; Sistema de Vigilancia y Registros Estadísticos.
  </div>
  <div class="footer-links">
    <a href="#" onclick="SingApp.toast({title: 'Documentación', message: 'Abriendo manual de usuario...', type: 'info'}); return false;">Documentación</a>
    <a href="#" onclick="SingApp.toast({title: 'Soporte', message: 'Contactando soporte técnico...', type: 'primary'}); return false;">Soporte Técnico</a>
    <a href="#" onclick="SingTheme.toggle(); return false;">Modo Oscuro/Claro</a>
  </div>
</footer>
