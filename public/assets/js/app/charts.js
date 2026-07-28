/**
 * Fábrica de Chart.js con las opciones comunes de la app (FRONTEND.md, F3).
 * No reemplaza los datasets de cada vista — solo las opciones de
 * presentación (leyenda, tooltip) que hoy se copian en cada uno de los 33
 * `new Chart(...)` del inventario original. Requiere que la vista ya
 * cargue Chart.js (el layout lo hace para todas).
 */
window.ChartFactory = (function () {
    var baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { usePointStyle: true } },
            tooltip: { backgroundColor: '#0a1f4d', cornerRadius: 8, padding: 10 },
        },
    };

    function deepMerge(base, override) {
        var out = Object.assign({}, base);
        for (var key in override) {
            if (!Object.prototype.hasOwnProperty.call(override, key)) continue;
            var overrideVal = override[key];
            var baseVal = base[key];
            var isPlainObject = overrideVal !== null && typeof overrideVal === 'object' && !Array.isArray(overrideVal);
            out[key] = (isPlainObject && baseVal && typeof baseVal === 'object')
                ? deepMerge(baseVal, overrideVal)
                : overrideVal;
        }
        return out;
    }

    function create(ctx, type, data, options) {
        return new Chart(ctx, { type: type, data: data, options: deepMerge(baseOptions, options || {}) });
    }

    return { create: create, baseOptions: baseOptions };
})();
