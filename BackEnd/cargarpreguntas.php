
<?php
header('Content-Type: application/json'); // Indica que la respuesta es JSON

$servername = "localhost";
$username = "root"; // Usuario por defecto de XAMPP
$password = ""; // En XAMPP, la contraseña suele estar vacía
$dbname = "stravdb"; // Reemplaza con el nombre correcto de tu BD

// Conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Consulta SQL
$sql = "SELECT * FROM PreguntaFrec";
$result = $conn->query($sql);

// Verificar si hay resultados
$preguntas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $preguntas[] = $row;
    }
}

// Cerrar conexión
$conn->close();

// Enviar respuesta en formato JSON
echo json_encode($preguntas, JSON_UNESCAPED_UNICODE);
?>
