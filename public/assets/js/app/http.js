/**
 * Fetch envuelto con CSRF automático y manejo de error estándar (FRONTEND.md, F3).
 * Reemplaza las lecturas manuales del token CSRF repetidas en cada vista
 * (`$('meta[name="csrf-token"]').attr('content')`, 15 veces en el
 * inventario original) y unifica cómo se interpreta una respuesta no-OK.
 */
window.Http = (function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    async function request(url, options) {
        options = options || {};
        var headers = Object.assign({
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
        }, options.headers || {});

        var body = options.body;
        if (body && !(body instanceof FormData) && typeof body !== 'string') {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(body);
        }

        var response = await fetch(url, Object.assign({}, options, { headers: headers, body: body }));

        if (!response.ok) {
            var payload = null;
            try { payload = await response.json(); } catch (e) { /* respuesta sin JSON */ }
            var error = new Error((payload && payload.message) || ('Error ' + response.status));
            error.status = response.status;
            error.payload = payload;
            throw error;
        }

        var contentType = response.headers.get('content-type') || '';
        return contentType.indexOf('application/json') !== -1 ? response.json() : response.text();
    }

    return {
        get: function (url, options) { return request(url, Object.assign({}, options, { method: 'GET' })); },
        post: function (url, body, options) { return request(url, Object.assign({}, options, { method: 'POST', body: body })); },
        put: function (url, body, options) { return request(url, Object.assign({}, options, { method: 'PUT', body: body })); },
        del: function (url, options) { return request(url, Object.assign({}, options, { method: 'DELETE' })); },
    };
})();
