// Validaciones 

document.addEventListener('DOMContentLoaded', function() {
    // (evita duplicar registros)
    const formularios = document.querySelectorAll('form');
    
    formularios.forEach(formulario => {
        formulario.addEventListener('submit', function(evento) {
            const botonSubmit = formulario.querySelector('button[type="submit"]');
            if (botonSubmit) {
                // Deshabilito el botón inmediatamente para que no se pueda volver a hacer clic
                botonSubmit.disabled = true;
                // Opcional seria cambiar el texto del botón
                botonSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
            }
        });
    });

    //  Validación personalizada 
    //  vemos que no sean negativos
    const inputsNumericos = document.querySelectorAll('input[type="number"]');
    
    inputsNumericos.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0; // Forzamos el valor a 0  par no hacer-1
            }
        });
    });
});
