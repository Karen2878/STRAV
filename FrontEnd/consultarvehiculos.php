<?php
// Tu código PHP normal, sin modo oscuro en sesión
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Lista de Vehículos</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    html, body {
      height: 100%;
    }
    body {
      display: flex;
      flex-direction: column;
    }
    .content {
      flex: 1 0 auto;
      padding: 20px;
    }
    .footer {
      flex-shrink: 0;
      background-color: #f8f9fa;
      padding: 10px 0;
      margin-top: 20px;
    }
    .table img {
      border-radius: 5px;
      max-height: 80px;
    }
    @media (max-width: 767px) {
      .card-vehiculo {
        margin-bottom: 20px;
      }
      .vehicle-img {
        width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 5px;
        max-height: 150px;
      }
      .table-desktop {
        display: none;
      }
      .cards-mobile {
        display: block;
      }
    }
    @media (min-width: 768px) {
      .table-desktop {
        display: block;
      }
      .cards-mobile {
        display: none;
      }
    }
    /* === MODO OSCURO === */
    [data-bs-theme="dark"] {
      background-color: #121212;
      color: #e0e0e0;
    }
    [data-bs-theme="dark"] .navbar {
      background-color: #222 !important;
    }
    [data-bs-theme="dark"] .footer {
      background-color: #4a4a4a !important;
      color: white !important;
    }
    [data-bs-theme="dark"] .table {
      background-color: #2c2c2c !important;
      color: #fff !important;
      border-color: #444 !important;
    }
    [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) {
      background-color: #3a3a3a !important;
    }
    [data-bs-theme="dark"] .table-hover > tbody > tr:hover {
      background-color: #4a4a4a !important;
    }
    [data-bs-theme="dark"] .table-dark thead {
      background-color: #4a4a4a !important;
      color: #fff !important;
    }
    [data-bs-theme="dark"] .table th,
    [data-bs-theme="dark"] .table td {
      border-color: #555 !important;
      color: #e0e0e0 !important;
    }
    [data-bs-theme="dark"] .table-responsive {
      background-color: #1e1e1e;
      padding: 10px;
      border-radius: 8px;
    }
    [data-bs-theme="dark"] .card-vehiculo {
      background-color: #2c2c2c !important;
      border-color: #444 !important;
      color: #ddd !important;
    }
    [data-bs-theme="dark"] .card-header {
      background-color: #4a4a4a !important;
      color: #fff !important;
    }
    [data-bs-theme="dark"] .card-footer {
      background-color: #4a4a4a !important;
      color: #ddd !important;
    }
    [data-bs-theme="dark"] .list-group-item {
      background-color: #3a3a3a !important;
      color: #ddd !important;
      border-color: #444 !important;
    }
    [data-bs-theme="dark"] .bg-light {
      background-color: #3a3a3a !important;
    }
    [data-bs-theme="dark"] a.nav-link.active {
      color: #90caf9 !important;
    }
    [data-bs-theme="dark"] .cerrar-sesion {
      color: white !important;
    }
    [data-bs-theme="dark"] h2 {
      color: #ffffff;
      font-weight: 700;
      font-size: 1.8rem;
      border-bottom: 2px solid #90caf9;
      padding-bottom: 10px;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Sistema de Vehículos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link active" href="#">Vehículos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link cerrar-sesion" href="cerrarsesion.php">Cerrar sesión</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container content">
  <div class="row mt-4 mb-3">
    <div class="col">
      <h2>Lista de Vehículos</h2>
    </div>
  </div>

  <!-- Vista escritorio (tabla) -->
  <div class="table-desktop">
    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Fecha de Compra</th>
            <th>Placa</th>
            <th>Tipo</th>
            <th>Marca</th>
            <th>Año</th>
            <th>Usuario</th>
            <th>Imagen</th>
            <th>Tipo Documento</th>
            <th>Fecha Vencimiento</th>
          </tr>
        </thead>
        <tbody>
          <?php
          ob_start();
          include '../consultarvehiculos.php';
          echo ob_get_clean();
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Vista móvil (tarjetas) -->
  <div class="cards-mobile">
    <?php
    include_once '../consultarvehiculos.php';

    if (isset($vehiculos) && is_array($vehiculos)) {
      foreach ($vehiculos as $vehiculo) {
        echo '<div class="card card-vehiculo mb-3 shadow-sm border">';
        echo '<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">';
        echo '<span>ID: ' . htmlspecialchars($vehiculo['id']) . '</span>';
        echo '<span>Placa: ' . htmlspecialchars($vehiculo['placa']) . '</span>';
        echo '</div>';
        echo '<div class="card-body">';
        echo '<div class="row">';

        echo '<div class="col-4">';
        if (!empty($vehiculo['imagen'])) {
          echo '<img src="' . htmlspecialchars($vehiculo['imagen']) . '" class="vehicle-img" alt="Imagen de vehículo">';
        } else {
          echo '<div class="bg-light p-4 text-center">Sin imagen</div>';
        }
        echo '</div>';

        echo '<div class="col-8">';
        echo '<ul class="list-group list-group-flush">';
        echo '<li class="list-group-item"><strong>Marca:</strong> ' . htmlspecialchars($vehiculo['marca']) . '</li>';
        echo '<li class="list-group-item"><strong>Tipo:</strong> ' . htmlspecialchars($vehiculo['tipo']) . '</li>';
        echo '<li class="list-group-item"><strong>Año:</strong> ' . htmlspecialchars($vehiculo['anio']) . '</li>';
        echo '<li class="list-group-item"><strong>Usuario:</strong> ' . htmlspecialchars($vehiculo['usuario']) . '</li>';
        echo '</ul>';
        echo '</div>';

        echo '</div>';
        echo '</div>';

        echo '<div class="card-footer">';
        echo '<small class="text-muted"><strong>Compra:</strong> ' . htmlspecialchars($vehiculo['fecha_compra']) . '</small> | ';
        echo '<small class="text-muted"><strong>Documento:</strong> ' . htmlspecialchars($vehiculo['tipo_documento']) . '</small> | ';
        echo '<small class="text-muted"><strong>Vence:</strong> ' . htmlspecialchars($vehiculo['fecha_vencimiento']) . '</small>';
        echo '</div>';

        echo '</div>';
      }
    } else {
      echo '<div class="alert alert-info">No hay vehículos para mostrar o debes ajustar el archivo consultarvehiculos.php para trabajar con este formato.</div>';
    }
    ?>
  </div>
</div>

<footer class="footer text-center bg-light">
  <div class="container">
    <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Gestión de Vehículos</p>
  </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
