<?php
session_start();
if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(["error" => "No autenticado"]);
    exit;
}

require_once '../BackEnd/conexion.php';

$data = json_decode(file_get_contents("php://input"), true);
$monedasGanadas = intval($data["monedas"]);
$idUsuario = $_SESSION['idUsuario'];

try {
    // Sumar monedas al progreso
    $stmt = $conn->prepare("UPDATE progreso SET monedas = monedas + ? WHERE usuario_id = ?");
    $stmt->execute([$monedasGanadas, $idUsuario]);
    echo json_encode(["ok" => true]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
