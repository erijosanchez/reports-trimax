/**
 * Envoltorio sobre SweetAlert2 (FRONTEND.md, F3). Un solo lugar para cambiar
 * cómo se ven los avisos de éxito/error/confirmación en toda la app, en vez
 * de repetir la misma forma de Swal.fire(...) en cada vista (63 en el
 * inventario original). Requiere que la vista ya cargue sweetalert2.all.min.js.
 */
window.Notify = (function () {
    function success(text, title) {
        return Swal.fire({ icon: 'success', title: title || '¡Listo!', text: text, timer: 2000, showConfirmButton: false });
    }
    function error(text, title) {
        return Swal.fire({ icon: 'error', title: title || 'Error', text: text });
    }
    function warning(text, title) {
        return Swal.fire({ icon: 'warning', title: title || 'Atención', text: text });
    }
    function info(text, title) {
        return Swal.fire({ icon: 'info', title: title || 'Info', text: text });
    }
    function confirm(text, title) {
        return Swal.fire({
            icon: 'question',
            title: title || '¿Estás seguro?',
            text: text,
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
        }).then(function (r) { return r.isConfirmed; });
    }
    return { success: success, error: error, warning: warning, info: info, confirm: confirm };
})();
