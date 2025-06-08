<?php
$servername = "localhost";  // En Hostinger casi siempre es 'localhost'
$username = "u598872392_Grupostrav1";
$password = "GrupoStrav2025";
$dbname = "u598872392_stravhostiger1";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Configura el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}
?>
