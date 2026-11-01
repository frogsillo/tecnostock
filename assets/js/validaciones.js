// Validaciones personalizadas para TecnoStock

document.addEventListener('DOMContentLoaded', function() {
    // 1. Prevenir doble clic en botones de envío (evita duplicar registros)
    const formularios = document.querySelectorAll('form');
    
    formularios.forEach(formulario => {
        formulario.addEventListener('submit', function(evento) {
            const botonSubmit = formulario.querySelector('button[type="submit"]');
            if (botonSubmit) {
                // Deshabilitamos el botón inmediatamente para que no se pueda volver a hacer clic
                botonSubmit.disabled = true;
                // Opcional: cambiar el texto del botón
                botonSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
            }
        });
    });

    // 2. Validación personalizada para los campos de cantidad y precio
    // Buscamos los inputs numéricos y verificamos que no sean negativos
    const inputsNumericos = document.querySelectorAll('input[type="number"]');
    
    inputsNumericos.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0; // Forzamos el valor a 0 si el usuario escribe un negativo
            }
        });
    });
});
