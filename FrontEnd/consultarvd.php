<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    header("Location: ../FrontEnd/iniciodesesion.php?error=not_logged_in");
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../BackEnd/conexion.php';

function log_error($mensaje) {
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . " - $mensaje\n", FILE_APPEND);
}

$mensaje = "";
$tipoMensaje = "";

try {
    if (!isset($_SESSION['idUsuario'])) {
        throw new Exception("No has iniciado sesión.");
    }

    $idUsuario = $_SESSION['idUsuario'];

    $sql = "SELECT V.idVeh, V.marcaVeh, V.imagen, D.idDoc, D.tipoDoc, D.fechaVencimiento
            FROM vehiculo V
            LEFT JOIN documentovehiculo D 
            ON V.idVeh = D.idVeh 
            AND D.tipoDoc IN ('SOAT', 'Tecnomecánica', 'Licencia')
            WHERE V.idUsuario = :idUsuario
            ORDER BY V.idVeh, FIELD(D.tipoDoc, 'SOAT', 'Tecnomecánica', 'Licencia');";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_fecha'])) {
        $idDoc = $_POST['idDoc'] ?? null;
        $fechaBase = $_POST['nueva_fecha'] ?? null;
        $tipoDoc = $_POST['tipo_doc'] ?? null;

        if (!$idDoc || !$fechaBase || !$tipoDoc) {
            $mensaje = "Todos los campos del formulario son obligatorios.";
            $tipoMensaje = "danger";
        } else {
            if ($tipoDoc === 'SOAT' || $tipoDoc === 'Tecnomecánica') {
                $nuevaFecha = date('Y-m-d', strtotime($fechaBase . ' +1 year'));
            } elseif ($tipoDoc === 'Licencia') {
                $nuevaFecha = date('Y-m-d', strtotime($fechaBase . ' +10 years'));
            } else {
                $nuevaFecha = $fechaBase;
            }

            try {
                $sqlUpdate = "UPDATE documentovehiculo 
                              SET fechaVencimiento = :nuevaFecha 
                              WHERE idDoc = :idDoc 
                              AND idVeh IN (SELECT idVeh FROM vehiculo WHERE idUsuario = :idUsuario)";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bindParam(':nuevaFecha', $nuevaFecha);
                $stmtUpdate->bindParam(':idDoc', $idDoc, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);

                if ($stmtUpdate->execute()) {
                    $mensaje = "✅ Fecha actualizada correctamente. La nueva fecha de vencimiento es: <strong>$nuevaFecha</strong>";
                    $tipoMensaje = "success";

                    $stmt->execute();
                    $documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $mensaje = "❌ Error al actualizar la fecha.";
                    $tipoMensaje = "danger";
                }
            } catch (PDOException $e) {
                log_error("Error al actualizar documento $idDoc: " . $e->getMessage());
                $mensaje = "❌ Error en la base de datos al actualizar el documento.";
                $tipoMensaje = "danger";
            }
        }
    }

} catch (PDOException $e) {
    log_error("Error DB: " . $e->getMessage());
    $mensaje = "❌ Error al consultar los datos.";
    $tipoMensaje = "danger";
} catch (Exception $e) {
    log_error("Error general: " . $e->getMessage());
    $mensaje = "❌ " . $e->getMessage();
    $tipoMensaje = "danger";
} finally {
    $conn = null;
}

function diasRestantes($fechaVencimiento) {
    if (empty($fechaVencimiento)) return null;
    $hoy = new DateTime();
    $vencimiento = new DateTime($fechaVencimiento);
    $diferencia = $hoy->diff($vencimiento);
    return $vencimiento < $hoy ? -$diferencia->days : $diferencia->days;
}

function getAlertaClase($dias) {
    if ($dias === null) return "";
    if ($dias < 0) return "table-danger";
    if ($dias <= 5) return "table-warning";
    if ($dias <= 15) return "table-info";
    if ($dias <= 30) return "table-success";
    return "";
}

function getAlertaMensaje($dias, $tipoDoc) {
    if ($dias === null) return "";
    if ($dias < 0) return "¡$tipoDoc VENCIDO! Han pasado " . abs($dias) . " días.";
    if ($dias == 0) return "¡ATENCIÓN! $tipoDoc vence HOY.";
    if ($dias == 1) return "¡ATENCIÓN! Solo queda 1 día para el vencimiento del $tipoDoc.";
    if ($dias <= 5) return "¡ATENCIÓN! Quedan $dias días para el vencimiento del $tipoDoc.";
    if ($dias <= 15) return "Atención: Quedan $dias días para el vencimiento del $tipoDoc.";
    if ($dias <= 30) return "Atención: Quedan $dias días para el vencimiento del $tipoDoc.";
    return "";
}
?>

<!DOCTYPE html>
<html lang="es" >
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Consultar Vencimiento de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet" />
    <style>
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
        display: flex;
        flex-direction: column;
        min-height: 100vh;
      }

      .main-content {
        flex: 1;
      }

      .header-custom {
          background-color: #4a4a4a;
          color: #fff;
      }
      .logo img {
          max-height: 80px;
      }
      .logout-button {
          background-color: #333;
          color: white;
          transition: background-color 0.3s, transform 0.2s;
      }
      .logout-button:hover {
          background-color: #1a100f;
          color: white;
          transform: scale(1.05);
      }
      .vehicle-img {
          max-width: 100px;
          max-height: 100px;
          border-radius: 5px;
      }
      .btn-modificar {
          background-color: #4a4a4a;
          color: white;
      }
      .btn-modificar:hover {
          background-color: #666;
          color: white;
      }
      .alerta-item {
          border-left: 5px solid;
          margin-bottom: 8px;
      }
      .alerta-item.table-danger {
          border-left-color: #dc3545;
      }
      .alerta-item.table-warning {
          border-left-color: #ffc107;
      }
      .alerta-item.table-info {
          border-left-color: #0dcaf0;
      }
      .alerta-item.table-success {
          border-left-color: #198754;
      }
      .dias-restantes {
          font-weight: bold;
      }

      footer {
        margin-top: auto;
      }

      /* === MODO OSCURO === */

      [data-bs-theme="dark"] body {
          background-color: #121212 !important;
          color: #f8f9fa !important;
      }
    
      [data-bs-theme="dark"] .logout-button {
          background-color: #444 !important;
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .logout-button:hover {
          background-color: #000 !important;
      }
      [data-bs-theme="dark"] .card {
          background-color: #1e1e1e !important;
          color: #e0e0e0 !important;
      }
      [data-bs-theme="dark"] .card-header,
      [data-bs-theme="dark"] .card-footer {
          background-color: #2a2a2a !important;
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .alerta-item {
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .alerta-item.table-danger {
          border-left-color: #ff6b6b !important;
      }
      [data-bs-theme="dark"] .alerta-item.table-warning {
          border-left-color: #ffdf6b !important;
      }
      [data-bs-theme="dark"] .alerta-item.table-info {
          border-left-color: #6bcfff !important;
      }
      [data-bs-theme="dark"] .alerta-item.table-success {
          border-left-color: #69bf6a !important;
      }
      [data-bs-theme="dark"] .table {
          color: #f0f0f0 !important;
      }
      [data-bs-theme="dark"] .table thead {
          background-color: #2e2e2e !important;
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .table tbody tr {
          background-color: #1f1f1f !important;
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .table tbody tr.table-danger {
          background-color: #5e1c1c !important;
      }
      [data-bs-theme="dark"] .table tbody tr.table-warning {
          background-color: #5e541c !important;
      }
      [data-bs-theme="dark"] .table tbody tr.table-info {
          background-color: #1c3f5e !important;
      }
      [data-bs-theme="dark"] .table tbody tr.table-success {
          background-color: #1c5e1c !important;
      }
      [data-bs-theme="dark"] .btn-modificar {
          background-color: #444 !important;
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .btn-modificar:hover {
          background-color: #666 !important;
          color: #f8f9fa !important;
      }
      [data-bs-theme="dark"] .vehicle-img {
          border: 1px solid #555 !important;
      }
      [data-bs-theme="dark"] .modal-content {
          background-color: #2a2a2a !important;
          color: #f8f9fa !important;
      }

      [data-bs-theme="dark"] footer.bg-dark {
          background-color: #222 !important;
          color: #eee !important;
      }
      
      [data-bs-theme="dark"] footer.bg-dark p {
          color: #ccc !important;
      }
    </style>
</head>
<body>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
    });
</script>

<div class="main-content">
    <header class="header-custom py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="logo">
                        <img src="imagenes/logo.png" alt="Logo de STRAV">
                    </div>
                </div>
                <div class="col">
                    <div class="service-text">
                        Sistema Temprano de Recordatorios y Alertas de tu Vehículo
                    </div>
                </div>
                <div class="col-auto">
                    <h1 class="fs-3 mb-0">Consultar Vencimiento de Documentos</h1>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row mb-3">
            <div class="col text-end">
                <a class="btn logout-button" href="../BackEnd/cerrarsesion.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header bg-light">
                        <h2 class="mb-0 fs-4">Documentos de Vehículos Registrados</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
                                <?= $mensaje ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info mb-4">
                            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Información sobre cálculo de fechas</h5>
                            <p class="mb-0">Al modificar las fechas, el sistema calculará automáticamente:</p>
                            <ul class="mb-0">
                                <li>Para SOAT y Tecnomecánica: 1 año a partir de la fecha ingresada</li>
                                <li>Para Licencia de conducción: 10 años a partir de la fecha ingresada</li>
                            </ul>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Sistema de Alertas de Vencimiento</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $hayAlertas = false;
                                foreach ($documentos as $doc):
                                    if (!empty($doc['fechaVencimiento'])) {
                                        $dias = diasRestantes($doc['fechaVencimiento']);
                                        if ($dias !== null && $dias <= 30) {
                                            $hayAlertas = true;
                                            $claseAlerta = getAlertaClase($dias);
                                            $mensajeAlerta = getAlertaMensaje($dias, $doc['tipoDoc']);
                                            ?>
                                            <div class="alerta-item p-3 mb-2 <?= $claseAlerta ?>">
                                                <strong>vehiculo <?= htmlspecialchars($doc['marcaVeh']) ?> (ID: <?= $doc['idVeh'] ?>):</strong> 
                                                <?= $mensajeAlerta ?>
                                            </div>
                                            <?php
                                        }
                                    }
                                endforeach;
                                
                                if (!$hayAlertas) {
                                    echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>No hay alertas de vencimiento en los próximos 30 días.</div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Marca</th>
                                        <th>Foto</th>
                                        <th>Tipo de Documento</th>
                                        <th>Fecha de Vencimiento</th>
                                        <th>Días Restantes</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documentos as $doc): 
                                        $dias = null;
                                        $claseAlerta = "";
                                        
                                        if (!empty($doc['fechaVencimiento'])) {
                                            $dias = diasRestantes($doc['fechaVencimiento']);
                                            $claseAlerta = getAlertaClase($dias);
                                        }
                                    ?>
                                    <tr class="<?= $claseAlerta ?>">
                                        <td><?= $doc['idVeh'] ?></td>
                                        <td><?= htmlspecialchars($doc['marcaVeh']) ?></td>
                                        <td><img src="<?= "../BackEnd/uploads/" . $doc['imagen'] ?>" alt="Imagen del vehículo" class="vehicle-img"></td>
                                        <td><?= htmlspecialchars($doc['tipoDoc']) ?></td>
                                        <td><?= $doc['fechaVencimiento'] ?></td>
                                        <td class="dias-restantes">
                                            <?php 
                                            if ($dias !== null) {
                                                if ($dias < 0) {
                                                    echo '<span class="badge bg-danger">Vencido hace ' . abs($dias) . ' días</span>';
                                                } elseif ($dias == 0) {
                                                    echo '<span class="badge bg-danger">Vence hoy</span>';
                                                } else {
                                                    $colorClass = ($dias <= 5) ? 'bg-warning text-dark' : 
                                                                (($dias <= 15) ? 'bg-info text-dark' : 'bg-success');
                                                    echo '<span class="badge ' . $colorClass . '">' . $dias . ' días</span>';
                                                }
                                            } else {
                                                echo '<span class="badge bg-secondary">No disponible</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($doc['idDoc'])): ?>
                                                <button 
                                                    id="btnModificar_<?= $doc['idDoc'] ?>" 
                                                    class="btn btn-sm btn-modificar" 
                                                    onclick="abrirModal('<?= $doc['idDoc'] ?>', '<?= $doc['fechaVencimiento'] ?>', '<?= $doc['tipoDoc'] ?>')">
                                                    <i class="fas fa-edit me-1"></i>Modificar
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No disponible</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="paginaprincipal.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Regresar
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditarLabel">Modificar Fecha de Vencimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="formEditarFecha" method="POST" action="">
                        <div class="modal-body">
                            <input type="hidden" id="idDoc" name="idDoc" value="">
                            <input type="hidden" id="tipo_doc" name="tipo_doc" value="">
                            <div class="mb-3">
                                <label for="nueva_fecha" class="form-label">Fecha Base:</label>
                                <input type="date" id="nueva_fecha" name="nueva_fecha" class="form-control" required>
                                <div id="info-calculo-fecha" class="form-text"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="guardar_fecha" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-3">
    <div class="container text-center">
        <p class="mb-0">&copy; 2024 STRAV. Todos los derechos reservados.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function abrirModal(idDoc, fechaActual, tipoDoc) {
        document.getElementById('idDoc').value = idDoc;
        document.getElementById('nueva_fecha').value = fechaActual;
        document.getElementById('tipo_doc').value = tipoDoc;

        let infoCalculo = document.getElementById('info-calculo-fecha');
        if (tipoDoc === 'SOAT' || tipoDoc === 'Tecnomecánica') {
            infoCalculo.textContent = "La fecha de vencimiento se calculará automáticamente como 1 año después de la fecha ingresada.";
        } else if (tipoDoc === 'Licencia') {
            infoCalculo.textContent = "La fecha de vencimiento se calculará automáticamente como 10 años después de la fecha ingresada.";
        } else {
            infoCalculo.textContent = "";
        }

        const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
        modalEditar.show();
    }
</script>
</body>
</html>