<?php
// Buffer de salida: permite redirigir con header("Location: ...") aunque ya se haya
// emitido el HTML de la cabecera, sin depender del tamaño del buffer de php.ini.
ob_start();

require_once __DIR__ . '/../auth/proteger.php';
$archivo_actual = basename($_SERVER['PHP_SELF']);

// Token CSRF de la sesión: lo usan los formularios que modifican datos (p. ej. desactivar)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Solo presentación: detecta la sección actual para resaltar el menú
$seccion = 'dashboard';
foreach (['categorias', 'productos', 'movimientos'] as $s) {
    if (strpos($_SERVER['PHP_SELF'], "/$s/") !== false) {
        $seccion = $s;
    }
}
$inicial = mb_strtoupper(mb_substr($_SESSION['nombre'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock - Sistema de Inventario</title>

    <!-- Tipografía -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Estilos propios (siempre después de Bootstrap) -->
    <link rel="stylesheet" href="/tecnostock/assets/css/estilos.css">
</head>
<body class="app-body">

    <!-- Menú de Navegación -->
    <nav class="navbar navbar-expand-lg app-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/tecnostock/dashboard/index.php">
                <span class="brand-mark"><i class="bi bi-box-seam"></i></span>
                TecnoStock
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menú">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto ms-lg-4 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link <?= $seccion === 'dashboard' ? 'active' : '' ?>" href="/tecnostock/dashboard/index.php" <?= $seccion === 'dashboard' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-grid-1x2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $seccion === 'categorias' ? 'active' : '' ?>" href="/tecnostock/categorias/index.php" <?= $seccion === 'categorias' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-tags"></i> Categorías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $seccion === 'productos' ? 'active' : '' ?>" href="/tecnostock/productos/index.php" <?= $seccion === 'productos' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-box"></i> Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $seccion === 'movimientos' ? 'active' : '' ?>" href="/tecnostock/movimientos/index.php" <?= $seccion === 'movimientos' ? 'aria-current="page"' : '' ?>>
                            <i class="bi bi-arrow-left-right"></i> Movimientos
                        </a>
                    </li>
                </ul>

                <div class="dropdown">
                    <a class="user-chip dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar"><?= htmlspecialchars($inicial) ?></span>
                        <?= htmlspecialchars($_SESSION['nombre']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item text-danger" href="/tecnostock/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal donde irá el contenido de cada página -->
    <main class="container app-main">
