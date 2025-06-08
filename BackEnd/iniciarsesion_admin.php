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
                
                if ($correoUsuario === 'admin@gmail.com' && $contraseniaUsuario === 'stravi') {
                    // Redirigir a ActividadUsuarios.php sin insertar evento
                    header("Location: ActividadUsuarios.php");
                    exit();
                }

                // --- INSERTAR EL EVENTO DE INICIO DE SESIÓN ---
                $descripcion = "Inicio de sesión";
                date_default_timezone_set('America/Bogota');
                $horaActual = date('Y-m-d H:i:s');  // ejemplo: 2025-05-27 15:30:45
                $descripcion = "Inicio de sesión - Hora: " . $horaActual;
                $sqlEvento = "INSERT INTO eventos (id_usuario, descripcion) VALUES (:id_usuario, :descripcion)";
                $stmtEvento = $conn->prepare($sqlEvento);
                $stmtEvento->bindParam(':id_usuario', $usuario['idUsuario'], PDO::PARAM_INT);
                $stmtEvento->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);

                if ($stmtEvento->execute()) {
                    // Inserción exitosa: mostrar alerta y redirigir
                    echo "<script>
                            alert('Inicio de sesión registrado correctamente.');
                            window.location.href = '../FrontEnd/paginaprincipal.php';
                          </script>";
                } else {
                    // Inserción fallida: mostrar alerta y redirigir a login
                    echo "<script>
                            alert('Error al registrar el inicio de sesión. Intenta de nuevo.');
                            window.location.href = '../iniciodesesion.php';
                          </script>";
                }
                exit();
                // ----------------------------------------------
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
