<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['idUsuario'])) {
    die("Error: No has iniciado sesión.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['idVeh'])) {
    $idVeh = $_POST['idVeh'] ?? $_GET['idVeh'];

    if (empty($idVeh)) {
        die("Error: No se ha proporcionado un ID de vehículo válido.");
    }

    try {
        // Verificar si el vehículo pertenece al usuario
        $stmt = $conn->prepare("SELECT COUNT(*) FROM vehiculo WHERE idVeh = :idVeh AND idUsuario = :idUsuario");
        $stmt->bindParam(':idVeh', $idVeh);
        $stmt->bindParam(':idUsuario', $_SESSION['idUsuario']);
        $stmt->execute();

        if ($stmt->fetchColumn() == 0) {
            die("Error: El vehículo no existe o no tienes permisos para eliminarlo.");
        }

        // Eliminar vehículo
        $sql = "DELETE FROM vehiculo WHERE idVeh = :idVeh AND idUsuario = :idUsuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idVeh', $idVeh);
        $stmt->bindParam(':idUsuario', $_SESSION['idUsuario']);
        $stmt->execute();

        header("Location: mostrarvehiculos.php");
        exit;
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
} else {
    die("Error: No se ha proporcionado un ID de vehículo válido.");
}
?>
