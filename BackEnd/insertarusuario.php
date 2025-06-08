<?php
include 'conexion.php'; // Asegura que la conexión se incluya correctamente

// Verificar si la conexión está definida
if (!isset($conn)) {
    die("❌ ERROR: No se pudo establecer conexión con la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreUsuario = $_POST['nombreUsuario'] ?? null;
    $contraseniaUsuario = isset($_POST['contraseniaUsuario']) ? password_hash($_POST['contraseniaUsuario'], PASSWORD_BCRYPT) : null;
    $correoUsuario = $_POST['correoUsuario'] ?? null;

    if ($nombreUsuario && $contraseniaUsuario && $correoUsuario) {
        try {
            $sql = "INSERT INTO Usuario (nombreUsuario, contraseniaUsuario, correoUsuario) 
                    VALUES (:nombreUsuario, :contraseniaUsuario, :correoUsuario)";
            $sentencia = $conn->prepare($sql);

            $sentencia->bindParam(':nombreUsuario', $nombreUsuario);
            $sentencia->bindParam(':contraseniaUsuario', $contraseniaUsuario);
            $sentencia->bindParam(':correoUsuario', $correoUsuario);

            $sentencia->execute();
            echo "✅ Usuario registrado exitosamente.";
        } catch (PDOException $e) {
            echo "❌ Error al registrar usuario: " . $e->getMessage();
        }
    } else {
        echo "❗ Todos los campos son obligatorios.";
    }
}
?>

<form method="POST">
    <input type="text" name="nombreUsuario" placeholder="Nombre de usuario" required><br>
    <input type="email" name="correoUsuario" placeholder="Correo" required><br>
    <input type="password" name="contraseniaUsuario" placeholder="Contraseña" required><br>
    <button type="submit">Registrar Usuario</button>
</form>
