    </main>

    <!-- Pie de página -->
    <footer class="app-footer mt-auto">
        <div class="container">
            <small>&copy; <?= date('Y') ?> TecnoStock - Sistema de Inventario</small>
        </div>
    </footer>

    <!-- Modal de confirmación: lo usa cualquier formulario con el atributo data-confirm -->
    <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <div class="d-flex gap-3">
                        <span class="mov-icon is-out" data-modal-iconwrap><i class="bi bi-exclamation-triangle" data-modal-icon></i></span>
                        <div>
                            <h2 class="h5 mb-1" id="modalConfirmarTitulo" data-modal-title>Confirmar acción</h2>
                            <p class="mb-0 text-muted" data-modal-text></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" data-modal-ok>Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!--  archivo de validaciones personalizadas -->
    <script src="/tecnostock/assets/js/validaciones.js"></script>
</body>
</html>
