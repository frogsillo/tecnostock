<?php
// Protegemos la página. Cualquier archivo que incluya este header pedirá login.
require_once __DIR__ . '/../auth/proteger.php';

// Obtenemos el nombre del archivo actual para resaltar la pestaña activa en el menú
$archivo_actual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TecnoStock - Sistema de Inventario</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Menú de Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="/tecnostock/dashboard/index.php">
                <i class="bi bi-box-seam"></i> TecnoStock
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/tecnostock/dashboard/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tecnostock/categorias/index.php">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tecnostock/productos/index.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/tecnostock/movimientos/index.php">Movimientos</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-white">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nombre']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning fw-bold" href="/tecnostock/logout.php">
                            Cerrar Sesión <i class="bi bi-box-arrow-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal donde irá el contenido de cada página -->
    <div class="container mt-4 mb-5">
