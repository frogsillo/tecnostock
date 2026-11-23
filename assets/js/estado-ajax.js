/**
 * Activar / desactivar sin recargar la página (AJAX con fetch).
 *
 * Cómo funciona:
 *  1. Los formularios llevan data-ajax="activar" o data-ajax="desactivar". validaciones.js
 *     muestra primero el modal de confirmación (data-confirm) y, al aceptar, reenvía el formulario.
 *  2. Aquí se intercepta ese envío: se manda con fetch y el servidor responde JSON.
 *  3. El JSON trae el estado final del producto (0 = inactivo, 1 = activo). Con él se actualiza la
 *     fila: clase row-inactive, insignia y, sobre todo, el botón se reemplaza por el de la acción
 *     contraria, para poder revertir al instante.
 *
 * Sin JavaScript los formularios siguen funcionando: activar.php / desactivar.php redirigen.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Navegador sin fetch: no hacemos nada y los formularios se envían de forma normal.
    if (!window.fetch) return;

    // ------------------------------------------------------------------
    // Configuración: qué cambia entre una acción y otra
    // ------------------------------------------------------------------

    // Cómo se ve el botón/formulario de cada acción
    var ACCIONES = {
        desactivar: {
            archivo: 'desactivar.php',
            claseBoton: 'btn btn-icon btn-outline-danger',
            icono: 'bi-slash-circle',
            titulo: 'Desactivar producto',
            tituloModal: 'Desactivar producto',
            textoOk: 'Desactivar',
            tono: 'danger',
            mensaje: function (nombre) {
                return '¿Desactivar «' + nombre + '»? No se borrará de la base de datos, ' +
                       'solo cambiará a inactivo y sus movimientos se conservan.';
            }
        },
        activar: {
            archivo: 'activar.php',
            claseBoton: 'btn btn-icon btn-outline-success',
            icono: 'bi-check-circle',
            titulo: 'Activar producto',
            tituloModal: 'Activar producto',
            textoOk: 'Activar',
            tono: 'success',
            mensaje: function (nombre) {
                return '¿Activar «' + nombre + '»? Volverá a estar disponible para entradas y salidas.';
            }
        }
    };

    // Cómo queda la fila según el ESTADO FINAL del producto (el que informa el servidor)
    var ESTADOS = {
        0: { inactiva: true,  insignia: 'badge bg-secondary', etiqueta: 'labelInactivo', porDefecto: 'Inactivo', siguiente: 'activar' },
        1: { inactiva: false, insignia: 'badge bg-success',   etiqueta: 'labelActivo',   porDefecto: 'Activo',   siguiente: 'desactivar' }
    };

    // ------------------------------------------------------------------
    // Avisos (Bootstrap Toast): visibles aunque el usuario haya bajado en la tabla
    // ------------------------------------------------------------------
    var contenedorToasts = null;
    var ICONOS = {
        success: 'bi-check-circle-fill',
        danger:  'bi-x-octagon-fill',
        warning: 'bi-exclamation-triangle-fill',
        info:    'bi-info-circle-fill'
    };

    function mostrarToast(tipo, mensaje) {
        if (!window.bootstrap) { window.alert(mensaje); return; }
        if (!ICONOS[tipo]) tipo = 'info';

        if (!contenedorToasts) {
            contenedorToasts = document.createElement('div');
            contenedorToasts.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(contenedorToasts);
        }

        // Se construye con DOM (no con innerHTML) para que el mensaje nunca se interprete como HTML
        var toast = document.createElement('div');
        toast.className = 'toast ts-toast ts-toast-' + tipo;
        toast.setAttribute('role', tipo === 'danger' ? 'alert' : 'status');
        toast.setAttribute('aria-live', tipo === 'danger' ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');

        var linea = document.createElement('div');
        linea.className = 'd-flex';

        var cuerpo = document.createElement('div');
        cuerpo.className = 'toast-body';
        var icono = document.createElement('i');
        icono.className = 'bi ' + ICONOS[tipo];
        var texto = document.createElement('span');
        texto.textContent = mensaje;
        cuerpo.appendChild(icono);
        cuerpo.appendChild(texto);

        var cerrar = document.createElement('button');
        cerrar.type = 'button';
        cerrar.className = 'btn-close me-2 m-auto';
        cerrar.setAttribute('data-bs-dismiss', 'toast');
        cerrar.setAttribute('aria-label', 'Cerrar');

        linea.appendChild(cuerpo);
        linea.appendChild(cerrar);
        toast.appendChild(linea);
        contenedorToasts.appendChild(toast);

        toast.addEventListener('hidden.bs.toast', function () { toast.remove(); });
        window.bootstrap.Toast.getOrCreateInstance(toast, {
            delay: tipo === 'success' ? 4000 : 7000
        }).show();
    }

    // ------------------------------------------------------------------
    // Construir el formulario de la acción contraria
    // ------------------------------------------------------------------
    // Se crea con DOM y se copian id, token CSRF, nombre y etiquetas del formulario anterior.
    function crearFormulario(accion, origen) {
        var def = ACCIONES[accion];
        var nombre = origen.dataset.nombre || 'este producto';

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = def.archivo;                 // relativo a la página actual (misma carpeta)
        form.className = 'inline-form';
        form.dataset.ajax = accion;
        form.dataset.nombre = nombre;
        form.dataset.confirm = def.mensaje(nombre);
        form.dataset.confirmTitle = def.tituloModal;
        form.dataset.confirmOk = def.textoOk;
        form.dataset.confirmTone = def.tono;
        ['labelActivo', 'labelInactivo'].forEach(function (clave) {
            if (origen.dataset[clave]) form.dataset[clave] = origen.dataset[clave];
        });

        ['id', 'csrf_token'].forEach(function (campo) {
            var original = origen.querySelector('input[name="' + campo + '"]');
            var oculto = document.createElement('input');
            oculto.type = 'hidden';
            oculto.name = campo;
            oculto.value = original ? original.value : '';
            form.appendChild(oculto);
        });

        var boton = document.createElement('button');
        boton.type = 'submit';
        boton.className = def.claseBoton;
        boton.title = def.titulo;
        var icono = document.createElement('i');
        icono.className = 'bi ' + def.icono;
        boton.appendChild(icono);
        form.appendChild(boton);

        return form;
    }

    // ------------------------------------------------------------------
    // Actualizar la fila según el estado final
    // ------------------------------------------------------------------
    function aplicarEstado(form, estado) {
        var cfg = ESTADOS[estado];
        var fila = form.closest('tr');

        if (fila) {
            fila.classList.toggle('row-inactive', cfg.inactiva);

            var celda = fila.querySelector('td[data-col="estado"]');
            var insignia = celda ? celda.querySelector('.badge') : null;
            if (insignia) {
                insignia.className = cfg.insignia;
                insignia.textContent = form.dataset[cfg.etiqueta] || cfg.porDefecto;
            }
        }

        // El botón actual se reemplaza por el de la acción contraria
        var nuevo = crearFormulario(cfg.siguiente, form);
        form.replaceWith(nuevo);

        // El botón pulsado desaparece del DOM: devolvemos el foco al nuevo (accesibilidad con teclado)
        var botonNuevo = nuevo.querySelector('button');
        if (botonNuevo) botonNuevo.focus();
    }

    // ------------------------------------------------------------------
    // Envío con fetch
    // ------------------------------------------------------------------
    function procesar(form) {
        var boton = form.querySelector('button[type="submit"]');
        var contenidoOriginal = boton.innerHTML;

        function restaurar() {
            form.dataset.enviando = '';
            boton.disabled = false;
            boton.innerHTML = contenidoOriginal;
        }

        // Estado de carga: botón bloqueado con spinner (evita dobles clics)
        form.dataset.enviando = '1';
        boton.disabled = true;
        boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),                  // incluye id y csrf_token
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',  // el servidor responde JSON al verla
                'Accept': 'application/json'
            }
        })
        .then(function (respuesta) {
            // Si la sesión venció, proteger.php redirige al login y llega HTML en lugar de JSON
            var tipo = respuesta.headers.get('Content-Type') || '';
            if (respuesta.redirected || tipo.indexOf('application/json') === -1) {
                throw new Error('sesion');
            }
            return respuesta.json();   // también se lee el JSON de las respuestas 4xx/5xx
        })
        .then(function (datos) {
            // El servidor informa el estado real cuando lo conoce (éxito, o "ya estaba así")
            if (datos.estado === 0 || datos.estado === 1) {
                aplicarEstado(form, datos.estado);
            } else {
                restaurar();
            }
            mostrarToast(datos.tipo || (datos.ok ? 'success' : 'danger'), datos.mensaje);
        })
        .catch(function (error) {
            restaurar();
            if (error.message === 'sesion') {
                mostrarToast('warning', 'Tu sesión expiró. Te llevamos al inicio de sesión...');
                setTimeout(function () { window.location.reload(); }, 1500);
            } else {
                mostrarToast('danger', 'No se pudo conectar con el servidor. Inténtalo de nuevo.');
            }
        });
    }

    // ------------------------------------------------------------------
    // Un único manejador (delegación): también atiende los formularios creados dinámicamente
    // ------------------------------------------------------------------
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form.matches || !form.matches('form[data-ajax]')) return;
        if (!ACCIONES[form.dataset.ajax]) return;      // solo "activar" y "desactivar"

        // Si el modal de confirmación todavía no se aceptó, validaciones.js ya canceló este envío
        if (evento.defaultPrevented) return;

        evento.preventDefault();                       // evita la recarga completa
        delete form.dataset.confirmado;                // la confirmación se consume: si falla, volverá a preguntar
        if (form.dataset.enviando === '1') return;     // ya hay un envío en curso
        procesar(form);
    });
});
