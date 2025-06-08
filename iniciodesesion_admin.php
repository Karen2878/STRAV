<?php
include("BackEnd/conexion.php");

$errorMsg = "";

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'incorrect_password':
            $errorMsg = "⚠️ Contraseña incorrecta. Intenta de nuevo.";
            break;
        case 'user_not_found':
            $errorMsg = "⚠️ Usuario incorrecto. Intenta de nuevo.";
            break;
        case 'campos_vacios':
            $errorMsg = "⚠️ Tienes que llenar los campos vacíos.";
            break;
        case 'internal_error':
            $errorMsg = "⚠️ Error interno. Intenta más tarde.";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../imagenes/logo.png">
    <link rel="icon" type="image/x-icon" href="../imagenes/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            background-color: #4a4a4a;
            color: #fff;
            padding: 10px 20px;
            position: relative;
        }
        .logo img {
            max-height: 80px;
        }
        .service-text {
            font-size: 16px;
            color: #fff;
        }
        .header-title {
            font-size: 30px;
        }
        .login-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 5px;
            border: 2px solid #cecece; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #dddddd;
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 20px 0;
            width: 100%;
            margin-top: auto;
        }
        .btn-custom {
            background-color: #4a4a4a;
            color: #fff;
            transition: background-color 0.3s;
        }
        .btn-custom:hover {
            background-color: #666;
            color: #fff;
        }
    </style>
    <script>
        function validarFormulario() {
            var correo = document.getElementById("correoUsuario").value.trim();
            var contrasenia = document.getElementById("contraseniaUsuario").value.trim();
            if (correo === "" || contrasenia === "") {
                alert("⚠️ Tienes que llenar los campos vacíos.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <header class="mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 col-sm-4">
                    <div class="logo">
                        <img src="../imagenes/logo.png" alt="Logo de la Empresa" class="img-fluid">
                    </div>
                </div>
                <div class="col-md-6 col-sm-8">
                    <div class="service-text">
                        Sistema Temprano de Recordatorios y Alertas de tu Vehículo
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-title">
                        Iniciar Sesión
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Iniciar Sesión</h2>

            <!-- Mensaje de error si existe -->
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?= $errorMsg ?>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="BackEnd/iniciarsesion_admin.php" onsubmit="return validarFormulario()">
                <div class="mb-3">
                    <input type="email" class="form-control" id="correoUsuario" name="correoUsuario" placeholder="Correo" required>
                </div>
                
                <div class="mb-3">
                    <input type="password" class="form-control" id="contraseniaUsuario" name="contraseniaUsuario" placeholder="Contraseña" required>
                </div>
                                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-custom">Iniciar Sesión</button>
                </div>
            </form>
            
            <p class="text-center mt-3">
                ¿No tienes una cuenta? <a href="registro.html">Regístrate aquí</a>
            </p>
            
            <div class="text-center mt-3">
                <a href="/" class="btn btn-secondary">⟵ Volver al inicio</a>
            </div>
        </div>
    </div>
    
    <div style="margin-bottom: 5px; margin-left: 5px;">
        <a href="ActividadUsuarios.php">
            <button type="submit" class="btn btn-custom">Actividad de Usuarios (Admin)</button>
        </a>
    </div>

    <footer>
        <div class="container">
            <p class="mb-0">&copy; 2024 STRAV. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>