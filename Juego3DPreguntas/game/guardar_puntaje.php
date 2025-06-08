
}<?php
// Conexión a la base de datos (ajusta estos valores)
$host = 'localhost';
$db   = 'u598872392_stravhostiger1';
$user = 'u598872392_Grupostrav1';
$pass = 'GrupoStrav2025';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error al conectar a la base de datos.']);
  exit;
}

// Leer JSON del body
$data = json_decode(file_get_contents('php://input'), true);
$monedas = $data['monedas'] ?? 0;

// Guardar el puntaje
$stmt = $pdo->prepare("INSERT INTO puntajes (monedas, fecha) VALUES (?, NOW())");
$stmt->execute([$monedas]);

echo json_encode(['status' => 'ok', 'monedas' => $monedas]);

