<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'conexion.php';

function registrarEvento($idUsuario, $descripcion) {
    global $conn; // Usamos la conexión ya establecida
    
    try {
        $sql = "INSERT INTO eventos (id_usuario, descripcion) 
                VALUES (:id_usuario, :descripcion)";
        $stmt = $conn->prepare($sql);
        
        // Agregar fecha/hora a la descripción
        $descripcionCompleta = $descripcion . ' - Hora: ' . date('Y-m-d H:i:s');
        
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->bindParam(':descripcion', $descripcionCompleta);
        $stmt->execute();

    } catch(PDOException $e) {
        // Loggear el error sin interrumpir el flujo principal
        error_log("Error al registrar evento: " . $e->getMessage());
    }
}

// Función de log para errores
function log_error($message) {
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ': ' . $message . "\n", FILE_APPEND);
}

// Verificar sesión
if (!isset($_SESSION['idUsuario'])) {
    log_error("No hay usuario autenticado");
    die("❌ Error: No hay usuario autenticado.");
}

$idUsuario = $_SESSION['idUsuario'];

// Recuperar datos del formulario
$fechaCompraVeh = $_POST['fechaCompraVeh'] ?? null;
$placaVeh = $_POST['placaVeh'] ?? null;
$tipoVeh = $_POST['tipoVeh'] ?? null;
$marcaVeh = $_POST['marcaVeh'] ?? null;
$anioVeh = $_POST['anioVeh'] ?? null;
$imagen = $_FILES['imagen'] ?? null;

$fechaBaseSOAT = $_POST['fechaVenSOAT'] ?? null;
$fechaBaseTecno = $_POST['fechaVenTecno'] ?? null;
$fechaBaseLic = $_POST['fechaVenLic'] ?? null;

// Validar campos obligatorios
if (!$fechaCompraVeh || !$placaVeh || !$tipoVeh || !$marcaVeh || !$anioVeh || !$fechaBaseSOAT || !$fechaBaseLic || !$fechaBaseTecno) {
    log_error("Faltan campos requeridos");
    die("❌ Error: Todos los campos son obligatorios.");
}

// Calcular fechas de vencimiento
$fechaVenSOAT = date('Y-m-d', strtotime($fechaBaseSOAT . ' + 1 year'));
$fechaVenTecno = date('Y-m-d', strtotime($fechaBaseTecno . ' + 1 year'));
$fechaVenLic = date('Y-m-d', strtotime($fechaBaseLic . ' + 10 years'));

// Validar imagen
if (!$imagen || $imagen['error'] !== UPLOAD_ERR_OK) {
    log_error("Error en la carga de imagen: " . $imagen['error']);
    die("❌ Error: No se ha cargado la imagen correctamente.");
}

$imageFileType = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
    log_error("Tipo de archivo no permitido: " . $imageFileType);
    die("❌ Error: Solo se permiten imágenes JPG, JPEG, PNG, o GIF.");
}

if ($imagen['size'] > 2 * 1024 * 1024) {
    log_error("Imagen demasiado grande: " . $imagen['size']);
    die("❌ Error: La imagen es demasiado grande.");
}

// Preparar destino
$imagenNombre = time() . '_' . basename($imagen['name']);
$targetDir = "../BackEnd/uploads/";
$targetFile = $targetDir . $imagenNombre;

// Asegurar que el directorio existe
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        log_error("No se pudo crear el directorio de uploads");
        die("❌ Error: No se pudo crear el directorio de carga.");
    }
}

try {
    // Subir imagen
    if (!move_uploaded_file($imagen['tmp_name'], $targetFile)) {
        log_error("No se pudo mover la imagen subida");
        die("❌ Error: No se pudo cargar la imagen.");
    }

    // Insertar vehículo
    $sql = "INSERT INTO vehiculo (idUsuario, fechaCompraVeh, placaVeh, tipoVeh, marcaVeh, imagen, anioVeh) 
            VALUES (:idUsuario, :fechaCompraVeh, :placaVeh, :tipoVeh, :marcaVeh, :imagen, :anioVeh)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->bindParam(':fechaCompraVeh', $fechaCompraVeh);
    $stmt->bindParam(':placaVeh', $placaVeh);
    $stmt->bindParam(':tipoVeh', $tipoVeh);
    $stmt->bindParam(':marcaVeh', $marcaVeh);
    $stmt->bindParam(':imagen', $imagenNombre);
    $stmt->bindParam(':anioVeh', $anioVeh);
    $stmt->execute();

    $idVeh = $conn->lastInsertId();
    
    $descripcion = "Vehículo agregado - Hora: " . date("Y-m-d H:i:s");

    // Inserta el evento en la tabla `eventos`
    $sql_evento = "INSERT INTO eventos (id_usuario, descripcion) VALUES (:id_usuario, :descripcion)";
    $stmt_evento = $pdo->prepare($sql_evento);
    $stmt_evento->execute([
        ':id_usuario' => $id_usuario,
        ':descripcion' => $descripcion
    ]);

    
    // Después de insertar el vehículo y los documentos, antes del redirect
    $descripcionEvento = "Nuevo vehículo registrado: " . $marcaVeh . " " . $anioVeh . " (Placa: " . $placaVeh . ")";
    registrarEvento($idUsuario, $descripcionEvento);
    
    // Redirigir tras éxito
    header("Location:/FrontEnd/paginaprincipal.php");
    exit();


    // Insertar documentos relacionados
    $documentos = [
        'SOAT' => $fechaVenSOAT,
        'Tecnomecánica' => $fechaVenTecno,
        'Licencia' => $fechaVenLic
    ];

    foreach ($documentos as $tipoDoc => $fechaVencimiento) {
        $sqlDoc = "INSERT INTO documentovehiculo (tipoDoc, fechaVencimiento, idVeh) 
                   VALUES (:tipoDoc, :fechaVencimiento, :idVeh)";
        $stmtDoc = $conn->prepare($sqlDoc);
        $stmtDoc->bindParam(':tipoDoc', $tipoDoc);
        $stmtDoc->bindParam(':fechaVencimiento', $fechaVencimiento);
        $stmtDoc->bindParam(':idVeh', $idVeh);
        $stmtDoc->execute();
    }

    // Redirigir tras éxito
    header("Location: ../FrontEnd/paginaprincipal.php");
    exit();
} catch (PDOException $e) {
    log_error("Error PDO: " . $e->getMessage());
    die("❌ Error al registrar vehículo.");
} finally {
    $conn = null;
}
?>
