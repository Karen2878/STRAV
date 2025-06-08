<?php
// Mostrar errores solo en desarrollo (recomendación: desactivar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Inicia la sesión

require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validar que los campos estén presentes y no vacíos
        if (empty($_POST['correoUsuario']) || empty($_POST['contraseniaUsuario'])) {
            header("Location: ../iniciodesesion.php?error=missing_fields");
            exit();
        }

        $correoUsuario = trim($_POST['correoUsuario']);
        $contraseniaUsuario = $_POST['contraseniaUsuario'];

        // Preparar y ejecutar consulta
        $sql = "SELECT * FROM usuario WHERE correoUsuario = :correoUsuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':correoUsuario', $correoUsuario);
        $stmt->execute();

        // Verificar si el usuario existe
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar la contraseña
            if (password_verify($contraseniaUsuario, $usuario['contraseniaUsuario'])) {
                $_SESSION['idUsuario'] = $usuario['idUsuario'];
                header("Location: ../FrontEnd/paginaprincipal.php");
                exit();
            } else {
                header("Location: ../iniciodesesion.php?error=incorrect_password");
                exit();
            }
        } else {
            header("Location: ../iniciodesesion.php?error=user_not_found");
            exit();
        }
    } catch (PDOException $e) {
        // Registrar el error o notificar, pero no mostrar en producción
        error_log("Error de conexión o ejecución: " . $e->getMessage());
        header("Location: ../iniciodesesion.php?error=internal_error");
        exit();
    } finally {
        // Cerrar conexión si existe
        $conn = null;
    }
}
?>
