
<?php
$conn = new mysqli("localhost", "root", "", "juego");
if ($conn->connect_error) die("Error: " . $conn->connect_error);

$usuario_id = 1;
$sql = "SELECT monedas, posicion FROM progreso WHERE usuario_id = $usuario_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["monedas" => 0, "posicion" => 0]);
}

$conn->close();
?>
