<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'conexion.php';

if (!isset($_SESSION['idUsuario'])) {
    die("❌ Error: No has iniciado sesión.");
}

$idUsuario = $_SESSION['idUsuario'];

// Optimización: Usar prepared statement con cache y solo campos necesarios
$sql = "SELECT idVeh, placaVeh, marcaVeh, tipoVeh, anioVeh, imagen FROM vehiculo WHERE idUsuario = :idUsuario ORDER BY placaVeh ASC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Performance: Liberar memoria del statement
$stmt = null;

// Optimización: Pre-calcular datos estáticos
$totalVehiculos = count($vehiculos);
$currentYear = date('Y');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Vehículos - STRAV</title>
    <link rel="icon" href="uploads/logo.png">
    
    <!-- Optimización: Preload de recursos críticos -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" as="style">
    <link rel="preload" href="uploads/logo.png" as="image">
    
    <!-- DNS prefetch para recursos externos -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Optimización: CSS crítico inline para evitar render blocking -->
    <style>
        /* CSS Crítico - Above the fold */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
        }

        header {
            background-color: #4a4a4a;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            position: relative;
        }

        .logo img {
            max-height: 80px;
            width: auto;
            /* Optimización: Evitar layout shift */
            aspect-ratio: auto;
        }

        .service-text {
            font-size: 16px;
            margin-left: 20px;
            color: #fff;
        }

        .header-title {
            position: absolute;
            right: 20px;
            top: 40px;
            font-size: 30px;
            font-weight: bold;
        }

        /* Performance: Optimizar animaciones con transform y opacity */
        .logout-container {
            position: absolute;
            top: 120px;
            right: 20px;
        }

        .logout-button {
            background-color: #333;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
            /* Optimización: GPU acceleration */
            will-change: transform, background-color;
        }

        .logout-button:hover {
            background-color: #1a100f;
            transform: scale(1.05);
        }

        h1 {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
            padding-top: 20px;
        }

        /* Optimización: Table virtualization ready */
        .table-container {
            width: 80%;
            margin: 0 auto;
            overflow-x: auto;
            /* Performance: Smooth scrolling */
            scroll-behavior: smooth;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fdf6ec;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            /* Optimización: Table layout fixed para mejor performance */
            table-layout: fixed;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            /* Optimización: Column widths */
            word-wrap: break-word;
        }

        /* Definir anchos de columnas para layout fixed */
        th:nth-child(1), td:nth-child(1) { width: 15%; } /* Placa */
        th:nth-child(2), td:nth-child(2) { width: 15%; } /* Marca */
        th:nth-child(3), td:nth-child(3) { width: 12%; } /* Tipo */
        th:nth-child(4), td:nth-child(4) { width: 10%; } /* Año */
        th:nth-child(5), td:nth-child(5) { width: 25%; } /* Imagen */
        th:nth-child(6), td:nth-child(6) { width: 23%; } /* Acciones */

        th {
            background-color: rgb(216, 146, 66);
            color: white;
        }

        /* Optimización: Lazy loading de imágenes */
        td img {
            width: 100%;
            height: auto;
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 8px;
            /* Performance: GPU acceleration para imágenes */
            transform: translateZ(0);
            /* Optimización: Evitar layout shift */
            aspect-ratio: 1;
        }

        .acciones {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .boton-actualizar, .boton-eliminar {
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            /* Optimización: GPU acceleration */
            will-change: transform, background-color;
            white-space: nowrap;
        }

        .boton-actualizar {
            background-color: #4CAF50;
        }

        .boton-actualizar:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        .boton-eliminar {
            background-color: #D32F2F;
        }

        .boton-eliminar:hover {
            background-color: #B71C1C;
            transform: translateY(-2px);
        }

        footer {
            background-color: #4a4a4a;
            text-align: center;
            padding: 10px;
            color: #fff;
            margin-top: auto;
        }

        .button-container {
            display: inline-block;
            margin-top: 20px;
            margin-left: 20px;
            margin-bottom: 20px;
        }

        .button-container a {
            background-color: #4a4a4a;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            cursor: pointer;
            border: none;
            will-change: transform, background-color;
        }

        .button-container a:hover {
            background-color: #666;
            transform: scale(1.05);
        }

        /* Optimización: Loading skeleton */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* === MODO OSCURO === */
        [data-bs-theme="dark"] body {
            background-color: #121212 !important;
            color: #f8f9fa !important;
        }

        [data-bs-theme="dark"] .logout-button {
            background-color: #444 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .logout-button:hover {
            background-color: #000 !important;
        }

        [data-bs-theme="dark"] .header-title {
            color: #ffffff !important;
            font-size: 36px !important;
            font-weight: bold !important;
            text-shadow: 2px 2px 4px #000000;
        }

        [data-bs-theme="dark"] table {
            background-color: #1c1c1c !important;
            color: #e0e0e0 !important;
            border: 1px solid #444;
        }

        [data-bs-theme="dark"] th {
            background-color: #333333 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] td {
            background-color: #1c1c1c !important;
            color: #dddddd !important;
            border-color: #444 !important;
        }

        [data-bs-theme="dark"] .boton-actualizar {
            background-color: #2e7d32 !important;
        }

        [data-bs-theme="dark"] .boton-eliminar {
            background-color: #c62828 !important;
        }

        [data-bs-theme="dark"] h1 {
            color: #ffffff !important;
            font-size: 2.5em !important;
            font-weight: bold;
            text-shadow: 2px 2px 5px #000000;
        }

        [data-bs-theme="dark"] footer {
            background-color: #333333 !important;
        }

        /* Optimización: Responsive design mejorado */
        @media (max-width: 768px) {
            .table-container {
                width: 95%;
            }
            
            .acciones {
                flex-direction: column;
                gap: 8px;
            }
            
            .boton-actualizar, .boton-eliminar {
                padding: 8px 12px;
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<!-- Optimización: Script crítico inline para evitar FOUC -->
<script>
    // Performance: Tema cargado inmediatamente sin esperar DOM
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    })();
    
    // Optimización: Preload de imágenes críticas
    document.addEventListener('DOMContentLoaded', function() {
        // Lazy loading para imágenes de vehículos
        const images = document.querySelectorAll('td img');
        
        // Optimización: Intersection Observer para lazy loading
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        }
        
        // Performance: Debounce para eventos de scroll
        let ticking = false;
        function updateScrollPosition() {
            // Aquí se pueden agregar optimizaciones adicionales
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                requestAnimationFrame(updateScrollPosition);
                ticking = true;
            }
        });
    });
</script>

<div class="main-content">
    <header>
        <div class="logo">
            <img src="uploads/logo.png" alt="Logo de STRAV" width="80" height="80">
        </div>
        <div class="service-text">
            Sistema Temprano de Recordatorios y Alertas de tu Vehículo
        </div>
        <div class="header-title">
            Consultar Vehículos
        </div>
    </header>

    <div class="logout-container">
        <a class="logout-button" href="cerrarsesion.php">Cerrar Sesión</a>
    </div>

    <h1>Mis Vehículos<?php if($totalVehiculos > 0): ?> (<?= $totalVehiculos ?>)<?php endif; ?></h1>

    <?php if($totalVehiculos > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Tipo</th>
                    <th>Año</th>
                    <th>Imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Optimización: Buffer de salida para mejor performance
                $tableRows = '';
                foreach ($vehiculos as $index => $vehiculo): 
                    // Optimización: Escape de datos una sola vez
                    $placa = htmlspecialchars($vehiculo['placaVeh'], ENT_QUOTES, 'UTF-8');
                    $marca = htmlspecialchars($vehiculo['marcaVeh'], ENT_QUOTES, 'UTF-8');
                    $tipo = htmlspecialchars($vehiculo['tipoVeh'], ENT_QUOTES, 'UTF-8');
                    $anio = htmlspecialchars($vehiculo['anioVeh'], ENT_QUOTES, 'UTF-8');
                    $imagen = htmlspecialchars($vehiculo['imagen'], ENT_QUOTES, 'UTF-8');
                    $idVeh = (int)$vehiculo['idVeh'];
                    
                    // Performance: Lazy loading de imágenes después de las primeras 3
                    $imgSrc = $index < 3 ? "src='uploads/{$imagen}'" : "data-src='uploads/{$imagen}' src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" width=\"150\" height=\"150\"%3E%3Crect width=\"100%25\" height=\"100%25\" fill=\"%23f0f0f0\"/%3E%3C/svg%3E'";
                    
                    $tableRows .= "
                    <tr>
                        <td>{$placa}</td>
                        <td>{$marca}</td>
                        <td>{$tipo}</td>
                        <td>{$anio}</td>
                        <td>
                            <img {$imgSrc} alt='Imagen del vehículo {$placa}' loading='lazy'>
                        </td>
                        <td>
                            <div class='acciones'>
                                <a href='actualizarvehiculo.php?idVeh={$idVeh}' class='boton-actualizar'>Actualizar</a>
                                <a href='eliminarvehiculo.php?idVeh={$idVeh}' class='boton-eliminar' onclick='return confirm(\"¿Estás seguro de eliminar este vehículo?\")'>Eliminar</a>
                            </div>
                        </td>
                    </tr>";
                endforeach;
                
                // Optimización: Imprimir todo de una vez
                echo $tableRows;
                ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 40px;">
        <p style="font-size: 18px; color: #666;">No tienes vehículos registrados</p>
        <a href="registrarvehiculo.php" style="display: inline-block; margin-top: 15px; background-color: #4CAF50; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">Registrar Primer Vehículo</a>
    </div>
    <?php endif; ?>

    <div class="button-container">
        <a href="../FrontEnd/paginaprincipal.php">Regresar</a>
    </div>
</div>

<footer>
    <p>&copy; <?= $currentYear ?> STRAV. Todos los derechos reservados.</p>
</footer>

<!-- Optimización: Scripts no críticos al final con defer -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

<!-- Performance: Service Worker para cache estático -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('SW registered: ', registration);
        }).catch(function(registrationError) {
            console.log('SW registration failed: ', registrationError);
        });
    });
}
</script>

</body>
</html>