<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit;
}

require_once '../BackEnd/conexion.php'; // Ajusta según tu estructura

$idUsuario = $_SESSION['idUsuario'];

try {
    // Verificar si ya tiene progreso
    $stmt = $conn->prepare("SELECT monedas FROM progreso WHERE usuario_id = ?");
    $stmt->execute([$idUsuario]);

    if ($stmt->rowCount() == 0) {
        // Insertar fila si no existe
        $insert = $conn->prepare("INSERT INTO progreso (usuario_id, monedas, posicion) VALUES (?, 0, 0)");
        $insert->execute([$idUsuario]);
        $monedas = 0;
    } else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $monedas = $row['monedas'];
    }

    echo json_encode(["monedas" => $monedas]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
