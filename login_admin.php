<?php
session_start();
include 'conexion.php';
include 'registrar_evento.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $contrasenia = $_POST['contrasenia'];
    
    $query = "SELECT * FROM administrador WHERE correoUsuario='$correo'";
    $resultado = mysqli_query($conexion, $query);
    $admin = mysqli_fetch_assoc($resultado);
    
    if ($admin && password_verify($contrasenia, $admin['contraseniaUsuario'])) {
        $_SESSION['admin'] = $admin;
        registrar_evento($admin['idUsuario'], $admin['nombreUsuario']." (admin) inició sesión. " . date("d-m-y h:ia"), $conexion);
        header("Location: ActividadUsuarios.php");
        exit();
    } else {
        echo "Correo o contraseña incorrectos.";
    }
}
?>

<form method="POST">
    <input type="email" name="correo" required placeholder="Correo Admin"><br>
    <input type="password" name="contrasenia" required placeholder="Contraseña"><br>
    <button type="submit">Iniciar como Admin</button>
</form>