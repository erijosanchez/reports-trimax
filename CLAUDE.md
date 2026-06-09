# Trimax CRM — Design Refactor Rules

## Paleta de colores
Ver resources/css/trimax-design-system.css — NO usar colores fuera de ese archivo.

## Reglas de redesign (NO ROMPER)
- NUNCA modificar lógica PHP o directivas Blade (@auth, @can, @foreach, @if)
- NUNCA eliminar rutas (route(), href, url())
- NUNCA eliminar IDs o clases usados en JS/Alpine.js
- SIEMPRE mostrar el archivo actual antes de modificar
- SIEMPRE trabajar de a un componente o vista por vez
- Los cambios de CSS van en trimax-design-system.css cuando sea posible
- Si una clase es usada en JS y también necesita estilos nuevos: agrega clase nueva, no reemplaces

## Convención de comentarios
Marcar bloques rediseñados con: <!-- REDESIGN: [nombre] -->
Esto facilita revertir cambios puntuales si algo se rompe.

## Orden de ejecución
Fase 0 → Auditoría
Fase 1 → Design system CSS
Fase 2 → Layout (sidebar + navbar)
Fase 3 → Dashboard
Fase 4 → Módulos (uno por uno)
Fase 5 → Charts
Fase 6 → Modales
Fase 7 → QA final