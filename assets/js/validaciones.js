/**
 * Comportamiento común de los formularios de TecnoStock.
 *
 * 1. Confirmación (modal) antes de acciones sensibles: formularios con data-confirm.
 * 2. Evita envíos duplicados: bloquea el botón al enviar un formulario POST
 *    (los formularios con data-ajax lo gestionan en estado-ajax.js).
 * 3. Los números no pueden ser negativos.
 * 4. Los campos de texto obligatorios no pueden contener solo espacios.
 * 5. Marca en rojo los campos inválidos tras un intento de envío.
 * 6. Las alertas de éxito se cierran solas.
 *
 * Importante: todas estas reglas se repiten en PHP; el navegador nunca es la única defensa.
 */
document.addEventListener('DOMContentLoaded', function () {

    function enviar(form) {
        if (form.requestSubmit) { form.requestSubmit(); } else { form.submit(); }
    }

    // ------------------------------------------------------------------
    // 1) Confirmación con modal (cae a confirm() si el modal no existe)
    // ------------------------------------------------------------------
    var modalEl = document.getElementById('modalConfirmar');
    var hayModal = !!(modalEl && window.bootstrap);
    var modal = hayModal ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var formPendiente = null;

    // En fase de captura: se ejecuta antes que cualquier otro manejador de "submit"
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form.matches || !form.matches('form[data-confirm]')) return;
        if (form.dataset.confirmado === '1') return;   // ya confirmado: dejamos pasar

        evento.preventDefault();

        if (!hayModal) {
            if (window.confirm(form.dataset.confirm)) {
                form.dataset.confirmado = '1';
                enviar(form);
            }
            return;
        }

        formPendiente = form;
        modalEl.querySelector('[data-modal-title]').textContent = form.dataset.confirmTitle || 'Confirmar acción';
        modalEl.querySelector('[data-modal-text]').textContent  = form.dataset.confirm;
        modalEl.querySelector('[data-modal-ok]').textContent    = form.dataset.confirmOk || 'Confirmar';

        // Tono del modal: "danger" (por defecto, acciones destructivas) o "success" (p. ej. activar)
        var exito = form.dataset.confirmTone === 'success';
        var marco = modalEl.querySelector('[data-modal-iconwrap]');
        var icono = modalEl.querySelector('[data-modal-icon]');
        if (marco) marco.className = 'mov-icon ' + (exito ? 'is-in' : 'is-out');
        if (icono) icono.className = 'bi ' + (exito ? 'bi-check-circle' : 'bi-exclamation-triangle');
        modalEl.querySelector('[data-modal-ok]').className = 'btn ' + (exito ? 'btn-primary' : 'btn-danger');

        modal.show();
    }, true);

    if (hayModal) {
        modalEl.querySelector('[data-modal-ok]').addEventListener('click', function () {
            if (!formPendiente) return;
            var form = formPendiente;
            formPendiente = null;
            form.dataset.confirmado = '1';
            modal.hide();
            enviar(form);
        });
        modalEl.addEventListener('hidden.bs.modal', function () { formPendiente = null; });
    }

    // ------------------------------------------------------------------
    // 2) Evitar envíos duplicados (solo formularios POST)
    // ------------------------------------------------------------------
    document.querySelectorAll('form').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            // Si algo lo canceló (modal de confirmación, validación), no bloqueamos nada
            if (evento.defaultPrevented) return;
            // Los formularios AJAX (data-ajax) gestionan su propio estado de carga
            if (formulario.hasAttribute('data-ajax')) return;
            if ((formulario.getAttribute('method') || 'get').toLowerCase() !== 'post') return;

            var boton = formulario.querySelector('button[type="submit"]');
            if (!boton) return;

            // Texto del botón: data-loading-text, o "Guardando..."; los botones de icono solo muestran el spinner
            var texto = boton.dataset.loadingText ||
                        (boton.classList.contains('btn-icon') ? '' : 'Guardando...');

            // Se bloquea en el siguiente ciclo para no interferir con el envío en curso
            setTimeout(function () {
                boton.dataset.original = boton.innerHTML;
                boton.style.minWidth = boton.offsetWidth + 'px';   // evita que el botón "salte"
                boton.disabled = true;

                boton.textContent = '';
                var spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm';
                spinner.setAttribute('role', 'status');
                spinner.setAttribute('aria-hidden', 'true');
                boton.appendChild(spinner);
                if (texto) boton.appendChild(document.createTextNode(' ' + texto));
            }, 0);
        });
    });

    // Si el usuario vuelve atrás con el botón del navegador, restauramos los botones
    window.addEventListener('pageshow', function (evento) {
        if (!evento.persisted) return;
        document.querySelectorAll('button[data-original]').forEach(function (b) {
            b.disabled = false;
            b.innerHTML = b.dataset.original;
            b.style.minWidth = '';
        });
        document.querySelectorAll('form[data-confirmado]').forEach(function (f) {
            delete f.dataset.confirmado;
        });
    });

    // ------------------------------------------------------------------
    // 3) Números no negativos
    // ------------------------------------------------------------------
    document.querySelectorAll('input[type="number"]').forEach(function (input) {
        input.addEventListener('input', function () {
            if (this.value !== '' && parseFloat(this.value) < 0) {
                this.value = 0;
                // Avisamos a otros scripts (p. ej. el resumen de movimientos) del cambio
                this.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    });

    // ------------------------------------------------------------------
    // 4) Obligatorios: no basta con escribir espacios
    // ------------------------------------------------------------------
    document.querySelectorAll('input[type="text"][required], textarea[required]').forEach(function (campo) {
        function revisar() {
            campo.setCustomValidity(campo.value.trim() === '' ? 'Este campo no puede estar vacío.' : '');
        }
        campo.addEventListener('input', revisar);
        revisar();
    });

    // ------------------------------------------------------------------
    // 5) Marcar en rojo los campos inválidos tras un intento de envío
    // ------------------------------------------------------------------
    document.querySelectorAll('form').forEach(function (formulario) {
        // "invalid" no burbujea, por eso se escucha en fase de captura
        formulario.addEventListener('invalid', function () {
            formulario.classList.add('show-errors');
        }, true);
    });

    // ------------------------------------------------------------------
    // 6) Las alertas de éxito se cierran solas a los 6 segundos
    // ------------------------------------------------------------------
    if (window.bootstrap) {
        document.querySelectorAll('.alert-success.alert-dismissible').forEach(function (alerta) {
            setTimeout(function () {
                if (document.body.contains(alerta)) {
                    window.bootstrap.Alert.getOrCreateInstance(alerta).close();
                }
            }, 6000);
        });
    }
});
