
<?php
$conn = new mysqli("localhost", "root", "", "juego");
if ($conn->connect_error) die("Error: " . $conn->connect_error);

$usuario_id = 1;
$monedas = intval($_POST['monedas']);
$posicion = intval($_POST['posicion']);

$sql = "UPDATE progreso SET monedas = $monedas, posicion = $posicion WHERE usuario_id = $usuario_id";

if ($conn->query($sql) === TRUE) {
    echo "OK";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
