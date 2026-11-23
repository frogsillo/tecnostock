document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[data-movimiento]');
    if (!form) return;

    var esSalida   = form.getAttribute('data-movimiento') === 'salida';
    var select     = document.getElementById('id_producto');
    var cantidad   = document.getElementById('cantidad');
    var elProducto = document.getElementById('res-producto');
    var elActual   = document.getElementById('res-actual');
    var elDelta    = document.getElementById('res-delta');
    var elNuevo    = document.getElementById('res-nuevo');
    var elAviso    = document.getElementById('res-aviso');

    var RAYA = '\u2014';

    function aviso(texto) {
        cantidad.setCustomValidity(texto);
        elAviso.textContent = texto;
        elAviso.classList.add('show');
    }

    function render() {
        var opcion = select.options[select.selectedIndex];

        // Limpiamos el estado anterior
        cantidad.setCustomValidity('');
        elAviso.textContent = '';
        elAviso.classList.remove('show');

        if (!opcion || opcion.value === '') {
            elProducto.textContent = 'Selecciona un producto para ver el resultado.';
            elActual.textContent = elDelta.textContent = elNuevo.textContent = RAYA;
            return;
        }

        var stock = parseInt(opcion.getAttribute('data-stock'), 10);
        var texto = cantidad.value.trim();

        elProducto.textContent = opcion.getAttribute('data-nombre');
        elActual.textContent = stock;

        if (texto === '' || isNaN(Number(texto))) {
            elDelta.textContent = elNuevo.textContent = RAYA;
            return;
        }

        var cant = Number(texto);

        if (!Number.isInteger(cant)) {
            elDelta.textContent = elNuevo.textContent = RAYA;
            aviso('La cantidad debe ser un número entero.');
            return;
        }
        if (cant <= 0) {
            elDelta.textContent = elNuevo.textContent = RAYA;
            aviso('La cantidad debe ser mayor a cero.');
            return;
        }

        elDelta.textContent = (esSalida ? '\u2212' : '+') + cant;

        var nuevo = esSalida ? stock - cant : stock + cant;
        if (nuevo < 0) {
            elNuevo.textContent = RAYA;
            aviso('Stock insuficiente: solo hay ' + stock + ' unidades disponibles.');
            return;
        }
        elNuevo.textContent = nuevo;
    }

    select.addEventListener('change', render);
    cantidad.addEventListener('input', render);
    render(); // por si el formulario vuelve con datos tras un error del servidor
});
