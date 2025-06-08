<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

include 'conexion.php'; // Asegúrate que esta ruta es correcta

if (!isset($_GET['codigo'])) {
    echo json_encode(['error' => 'Zona no especificada']);
    exit;
}

$zona = $_GET['codigo'];

try {
    $stmt = $conn->prepare("SELECT * FROM sucursales WHERE zona = :zona LIMIT 1");
    $stmt->bindParam(':zona', $zona, PDO::PARAM_STR);
    $stmt->execute();
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sucursal) {
        echo json_encode([
            'nombre' => $sucursal['nombre'],
            'direccion' => $sucursal['direccion'],
            'descripcion' => $sucursal['descripcion'],
            'latitud' => floatval($sucursal['latitud']),
            'longitud' => floatval($sucursal['longitud'])
        ]);
    } else {
        echo json_encode(['error' => 'Sucursal no encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
