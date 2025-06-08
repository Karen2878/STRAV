<?php
session_start();
include '../BackEnd/conexion.php';

if (!isset($_SESSION['idUsuario'])) {
    die("❌ No autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["nuevaFoto"])) {
    $nombreArchivo = basename($_FILES["nuevaFoto"]["name"]);
    $rutaDestino = "../imagenes/" . $nombreArchivo;

    if (move_uploaded_file($_FILES["nuevaFoto"]["tmp_name"], $rutaDestino)) {
        $sql = "UPDATE usuario SET fotoUsuario = :foto WHERE idUsuario = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':foto', $nombreArchivo);
        $stmt->bindParam(':id', $_SESSION['idUsuario'], PDO::PARAM_INT);
        $stmt->execute();
        header("Location: ../FrontEnd/paginaprincipal.php");
        exit();
    } else {
        echo "❌ Error al subir la imagen.";
    }
}
?>
