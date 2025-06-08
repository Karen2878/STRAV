<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "u598872392_Grupostrav1";
$password = "GrupoStrav2025";
$dbname = "u598872392_stravhostiger1";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT e.id_evento, u.nombreUsuario, e.descripcion 
            FROM eventos e
            INNER JOIN usuario u ON e.id_usuario = u.idUsuario
            ORDER BY e.id_evento DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error en la conexión o consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Actividad de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h2>Actividad de Usuarios</h2>

    <?php if (count($resultados) > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Evento</th>
                <th>Usuario</th>
                <th>Descripción (log)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($resultados as $fila): ?>
            <tr>
                <td><?= htmlspecialchars($fila['id_evento']) ?></td>
                <td><?= htmlspecialchars($fila['nombreUsuario']) ?></td>
                <td><?= htmlspecialchars($fila['descripcion']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="alert alert-info">No hay eventos registrados</div>
    <?php endif; ?>

    <a href="iniciodesesion_admin.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Regresar
    </a>
</body>
</html>
