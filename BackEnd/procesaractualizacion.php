

 <?php
session_start();
include 'conexion.php';
// Habilitar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['idUsuario'])) {
    die("❌ Error: No has iniciado sesión.");
}
$idUsuario = $_SESSION['idUsuario'];
// Verificar que se haya enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $idVeh = $_POST['idVeh'];
    $placaVeh = $_POST['placaVeh'];
    $tipoVeh = $_POST['tipoVeh'];
    $marcaVeh = $_POST['marcaVeh'];
    $anioVeh = $_POST['anioVeh'];
    $fechaCompraVeh = $_POST['fechaCompraVeh'];
    // Manejo de la imagen (si se seleccionó una nueva imagen)
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        // Ruta absoluta para la carpeta de imágenes en el BackEnd
        $targetDir = "uploads/";

        // Crear un nombre único para la imagen (por ejemplo, timestamp + extensión)
        $imageFileType = strtolower(pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION));

        $newFileName = uniqid('vehiculo_') . '.' . $imageFileType;
        // Ruta completa para guardar el archivo
        $targetFile = $targetDir . $newFileName;
        // Verificar si el directorio existe
        if (!is_dir($targetDir)) {
            die("❌ Error: El directorio de imágenes no existe.");
        }
        // Comprobar si el archivo es una imagen válida
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            die("❌ Error: Solo se permiten imágenes JPG, JPEG, PNG o GIF.");
        }
        // Mover el archivo al directorio de imágenes
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $targetFile)) {
            $imagenPath = $newFileName; // Ruta de la imagen con el nombre único
        } else {
            die("❌ Error: No se pudo cargar la imagen.");
        }
    } else {
        // Si no se selecciona una nueva imagen, mantener la imagen actual
        $imagenPath = $_POST['imagenActual']; // La imagen que ya estaba en la base de datos
    }
    // Actualizar los datos del vehículo en la base de datos
    $sql = "UPDATE vehiculo SET placaVeh = :placaVeh, tipoVeh = :tipoVeh, marcaVeh = :marcaVeh, anioVeh = :anioVeh, fechaCompraVeh = :fechaCompraVeh, imagen = :imagen  WHERE idVeh = :idVeh AND idUsuario = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':placaVeh', $placaVeh);
    $stmt->bindParam(':tipoVeh', $tipoVeh);
    $stmt->bindParam(':marcaVeh', $marcaVeh);
    $stmt->bindParam(':anioVeh', $anioVeh);
    $stmt->bindParam(':fechaCompraVeh', $fechaCompraVeh);
    $stmt->bindParam(':imagen', $imagenPath); // Ruta de la imagen que se guardó
    $stmt->bindParam(':idVeh', $idVeh);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();
    // Redirigir al usuario a la lista de vehículos después de la actualización
    header("Location: mostrarvehiculos.php");
    exit;
}
?>