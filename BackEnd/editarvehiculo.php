<?php
include 'conexion.php';
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['idUsuario'])) {
    die("Error: No has iniciado sesión.");
}

// Verificar que se haya proporcionado un ID de vehículo
if (!isset($_GET['idVeh'])) {
    die("❌ Error: No se ha especificado un vehículo para editar.");
}

$idVeh = $_GET['idVeh'];
$idUsuario = $_SESSION['idUsuario'];

// Obtener los datos del vehículo a editar
$sql = "SELECT * FROM Vehiculo WHERE idVeh = :idVeh AND idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idVeh', $idVeh);
$stmt->bindParam(':idUsuario', $idUsuario);
$stmt->execute();
$vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehiculo) {
    die("❌ Error: El vehículo no existe o no pertenece a este usuario.");
}

// Si el formulario se envía, actualizar los datos
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fechaCompraVeh = $_POST['fechaCompraVeh'] ?? NULL;
    $placaVeh = $_POST['placaVeh'] ?? NULL;
    $tipoVeh = $_POST['tipoVeh'] ?? NULL;
    $marcaVeh = $_POST['marcaVeh'] ?? NULL;
    $anioVeh = $_POST['anioVeh'] ?? NULL;
    $imagen = $_FILES['imagen'] ?? NULL;

    // Manejo de la imagen (si se seleccionó una nueva imagen)
    if ($imagen && $imagen['error'] == 0) {
        // Ruta absoluta a la carpeta 'uploads'
        $targetDir = "public_html/BackEnd/uploads/"; // Ajusta la ruta según tu configuración
        $targetFile = $targetDir . basename($imagen["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Verificar si el archivo es una imagen válida
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            die("❌ Error: Solo se permiten imágenes JPG, JPEG, PNG o GIF.");
        }

        // Mover la imagen cargada al directorio 'uploads'
        if (!move_uploaded_file($imagen["tmp_name"], $targetFile)) {
            die("❌ Error: No se pudo cargar la imagen.");
        }

        $imagenPath = basename($imagen["name"]);
    } else {
        // Si no se seleccionó imagen, usar la imagen actual
        $imagenPath = $vehiculo['imagen'];
    }

    // Actualizar los datos del vehículo en la base de datos
    $sql = "UPDATE Vehiculo SET 
            fechaCompraVeh = :fechaCompraVeh,
            placaVeh = :placaVeh,
            tipoVeh = :tipoVeh,
            marcaVeh = :marcaVeh,
            imagen = :imagen,
            anioVeh = :anioVeh 
            WHERE idVeh = :idVeh AND idUsuario = :idUsuario";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fechaCompraVeh', $fechaCompraVeh);
    $stmt->bindParam(':placaVeh', $placaVeh);
    $stmt->bindParam(':tipoVeh', $tipoVeh);
    $stmt->bindParam(':marcaVeh', $marcaVeh);
    $stmt->bindParam(':imagen', $imagenPath);
    $stmt->bindParam(':anioVeh', $anioVeh);
    $stmt->bindParam(':idVeh', $idVeh);
    $stmt->bindParam(':idUsuario', $idUsuario);
    
    if ($stmt->execute()) {
        echo "✅ Vehículo actualizado exitosamente.";
    } else {
        echo "❌ Error al actualizar el vehículo.";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <label for="placaVeh">Placa:</label>
    <input type="text" name="placaVeh" id="placaVeh" value="<?= $vehiculo['placaVeh']; ?>" required><br>

    <label for="tipoVeh">Tipo:</label>
    <input type="text" name="tipoVeh" id="tipoVeh" value="<?= $vehiculo['tipoVeh']; ?>" required><br>

    <label for="marcaVeh">Marca:</label>
    <input type="text" name="marcaVeh" id="marcaVeh" value="<?= $vehiculo['marcaVeh']; ?>" required><br>

    <label for="anioVeh">Año:</label>
    <input type="text" name="anioVeh" id="anioVeh" value="<?= $vehiculo['anioVeh']; ?>" required><br>

    <label for="fechaCompraVeh">Fecha de Compra:</label>
    <input type="date" name="fechaCompraVeh" id="fechaCompraVeh" value="<?= $vehiculo['fechaCompraVeh']; ?>" required><br>

    <label for="imagen">Imagen (opcional):</label>
    <input type="file" name="imagen" id="imagen"><br>
    
    <input type="hidden" name="imagenActual" value="<?= $vehiculo['imagen']; ?>"> <!-- Para mantener la imagen actual si no se sube una nueva -->
    
    <input type="submit" value="Actualizar Vehículo">
</form>
