
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../BackEnd/conexion.php';

function log_error($mensaje) {
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - " . $mensaje . "\n", FILE_APPEND);
}

try {
    if (!isset($_SESSION['idUsuario'])) {
        log_error("Intento de acceso sin sesión.");
        die("❌ Error: No has iniciado sesión.");
    }

    $idUsuario = $_SESSION['idUsuario'];

    $sql = "SELECT idVeh, marcaVeh, placaVeh, imagen FROM vehiculo WHERE idUsuario = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    log_error("Error al consultar vehículos: " . $e->getMessage());
    die("❌ Error al consultar los vehículos. Intenta más tarde.");
} finally {
    $conn = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consultar Pico y Placa - STRAV</title>
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/logo.png" />
    <link rel="icon" type="image/x-icon" href="imagenes/logo.png" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .boton-custom {
            background-color: #6f42c1; /* morado */
            color: white;
            border: none;
            transition: background-color 0.9s ease;
        }
        .header-custom {
            background-color: #4a4a4a;
            color: white;
            padding: 15px 0;
        }
        .footer {
            background-color: #4a4a4a;
            color: white;
            padding: 15px 0;
        }

        .logo-img {
            max-height: 80px;
        }
        .main-content {
            flex: 1;
            padding: 30px 0;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
        }
        .card-header {
            background-color: #4a4a4a;
            color: white;
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
        .table img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            object-fit: cover;
        }
        .btn-dark {
            background-color: #3a3a3a;
            border-color: #4a4a4a;
        }
        .btn-dark:hover {
            background-color: #333;
            border-color: #333;
        }
        .alert-success {
            border-left: 5px solid #28a745;
        }
        .alert-warning {
            border-left: 5px solid #ff107;
        }
        .logout-btn {
            background-color: #333;
            color: white;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background-color: #1a100f;
            color: white;
            transform: scale(1.05);
        }
        
        /* Modo oscuro */
        [data-bs-theme="dark"] body {
          background-color: #79797d !important;
          color: #f8f9fa !important;
        }
        
       
        [data-bs-theme="dark"] .table thead {
          background-color: #ddd !important;
          color: #fff !important;
        }
        
        [data-bs-theme="dark"] .table tbody {
          background-color: #ddd !important;
          color: #ddd !important;
        }

        
        [data-bs-theme="dark"] .table tr:hover {
          background-color: #333 !important;
        }
        
        [data-bs-theme="dark"] .table,
        [data-bs-theme="dark"] .table td,
        [data-bs-theme="dark"] .table th {
          border-color: #444 !important;
        }
    </style>
</head>
<body>
<script>
  // Al cargar la página, leer tema guardado y aplicarlo
  document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
  });
</script>

    <!-- Header -->
    <header class="header-custom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                    <img src="imagenes/logo.png" alt="Logo de STRAV" class="logo-img" />
                </div>
                <div class="col-md-8">
                    <h1 class="text-center">Consultar Pico y Placa - STRAV</h1>
                    <p class="text-center mb-0">Sistema Temprano de Recordatorios y Alertas de tu Vehículo</p>
                </div>
            </div>
        </div>
    </header>

    <div class="container text-end mt-3">
      <a href="../BackEnd/cerrarsesion.php" class="btn logout-btn d-inline-flex align-items-center gap-2">
        <i class="bi bi-box-arrow-right"></i>
        <span>Cerrar Sesión</span>
      </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0 text-center">Selecciona un Vehículo</h4>
                        </div>
                        <div class="card-body">
                            <form id="formulario">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Seleccionar</th>
                                                <th>Placa</th>
                                                <th>Marca</th>
                                                <th>Imagen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($vehiculos) > 0): ?>
                                                <?php foreach ($vehiculos as $vehiculo): ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="vehiculoSeleccionado" 
                                                                value="<?= $vehiculo['placaVeh'] ?>" required />
                                                        </div>
                                                    </td>
                                                    <td><?= $vehiculo['placaVeh'] ?></td>
                                                    <td><?= htmlspecialchars($vehiculo['marcaVeh']) ?></td>
                                                    <td>
                                                        <img src="<?= "../BackEnd/uploads/" . $vehiculo['imagen'] ?>" 
                                                            alt="Imagen del vehículo" class="img-thumbnail" />
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay vehículos registrados</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha" class="form-label">Seleccionar Fecha:</label>
                                        <input type="date" id="fecha" name="fecha" class="form-control" required />
                                    </div>
                                </div>

                                <!-- Resultado se mostrará aquí -->
                                <div id="resultado-container"></div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="submit" class="logout-button d-inline-flex align-items-center gap-2">
                                        <i class="fas fa-search"></i> Consultar Pico y Placa
                                    </button>
                                    
                                    <a href="paginaprincipal.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Regresar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center py-4">
      <div class="container">
        <p class="mb-0">&copy; 2024 STRAV. Todos los derechos reservados.</p>
      </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("formulario");
        const resultadoContainer = document.getElementById("resultado-container");

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const vehiculoSeleccionado = document.querySelector("input[name='vehiculoSeleccionado']:checked");
            const fechaSeleccionada = document.getElementById("fecha").value;

            if (!vehiculoSeleccionado || !fechaSeleccionada) {
                mostrarAlerta("❌ Debes seleccionar un vehículo y una fecha.", "danger");
                return;
            }

            const placa = vehiculoSeleccionado.value;
            const ultimoDigito = parseInt(placa.slice(-1));
            
            const [year, month, day] = fechaSeleccionada.split('-').map(Number);
            const fecha = new Date(year, month - 1, day);
            
            const diaSemana = fecha.getDay();
            const diaDelMes = fecha.getDate();

            const esFinDeSemana = diaSemana === 0 || diaSemana === 6;
            const esDiaFestivo = esFestivo(fecha);

            if (esFinDeSemana || esDiaFestivo) {
                let razon = esFinDeSemana ? "fin de semana" : "día festivo";
                let mensaje = `✅ Puedes circular sin restricciones el ${formatearFecha(fechaSeleccionada)} (${razon}).`;
                mostrarAlerta(mensaje, "success");
                return;
            }

            let tienePicoYPlaca = false;
            if ((diaDelMes % 2 !== 0 && [6, 7, 8, 9, 0].includes(ultimoDigito)) ||  
                (diaDelMes % 2 === 0 && [1, 2, 3, 4, 5].includes(ultimoDigito))) {
                tienePicoYPlaca = true;
            }

            let mensaje = "";
            if (tienePicoYPlaca) {
                mensaje = `⚠️ No puedes circular el ${formatearFecha(fechaSeleccionada)} debido al Pico y Placa de Bogotá.<br>`;
                mensaje += diaDelMes % 2 !== 0
                    ? "<strong>En días impares circulan placas terminadas en 1, 2, 3, 4 y 5.</strong>"
                    : "<strong>En días pares circulan placas terminadas en 6, 7, 8, 9 y 0.</strong>";
                mostrarAlerta(mensaje, "warning");
            } else {
                mensaje = `✅ Puedes circular sin restricciones el ${formatearFecha(fechaSeleccionada)}.<br>`;
                mensaje += diaDelMes % 2 !== 0
                    ? "<strong>En días impares circulan placas terminadas en 1, 2, 3, 4 y 5.</strong>"
                    : "<strong>En días pares circulan placas terminadas en 6, 7, 8, 9 y 0.</strong>";
                mostrarAlerta(mensaje, "success");
            }
        });

        function mostrarAlerta(mensaje, tipo) {
            resultadoContainer.innerHTML = `
                <div class="alert alert-${tipo} mt-4">
                    ${mensaje}
                </div>`;
            resultadoContainer.scrollIntoView({ behavior: 'smooth' });
        }

        function formatearFecha(fecha) {
            const [year, month, day] = fecha.split('-').map(Number);
            const fechaObj = new Date(year, month - 1, day);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return fechaObj.toLocaleDateString('es-ES', options);
        }

        function esFestivo(fecha) {
            const festivos = [
                "2025-01-01", "2025-01-06", "2025-03-24", "2025-04-17", "2025-04-18",
                "2025-05-01", "2025-05-05", "2025-06-30",
                "2025-07-20", "2025-08-07", "2025-08-18", "2025-10-13", "2025-11-03",
                "2025-11-17", "2025-12-08", "2025-12-25"
            ];
            const año = fecha.getFullYear();
            const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            const dia = fecha.getDate().toString().padStart(2, '0');
            const fechaStr = `${año}-${mes}-${dia}`;
            return festivos.includes(fechaStr);
        }
    });
    </script>
</body>
</html>
