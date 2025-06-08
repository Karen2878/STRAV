<?php
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "conexion.php";  

    if (!isset($conn)) {
        $mensaje = "Error: La conexión no está definida.";
    } else {
        $nombre = $_POST["nombreUsuario"];
        $correo = $_POST["correoUsuario"];
        $passwordPlano = $_POST["contraseniaUsuario"];

        // Validación de requisitos de contraseña
        $erroresContrasenia = [];

        if (strlen($passwordPlano) < 8) {
            $erroresContrasenia[] = "La contraseña debe tener al menos 8 caracteres.";
        }
        if (!preg_match('/[A-Z]/', $passwordPlano)) {
            $erroresContrasenia[] = "Debe incluir al menos una letra mayúscula.";
        }
        if (!preg_match('/[a-z]/', $passwordPlano)) {
            $erroresContrasenia[] = "Debe incluir al menos una letra minúscula.";
        }
        if (!preg_match('/[0-9]/', $passwordPlano)) {
            $erroresContrasenia[] = "Debe incluir al menos un número.";
        }
        
        if (!empty($erroresContrasenia)) {
            $mensaje = "⚠️ La contraseña no cumple con los requisitos:<br>" . implode("<br>", $erroresContrasenia);
        } else {
            try {
                $password = password_hash($passwordPlano, PASSWORD_DEFAULT);
                $sql = "INSERT INTO usuario (nombreUsuario, correoUsuario, contraseniaUsuario) 
                        VALUES (:nombreUsuario, :correoUsuario, :contraseniaUsuario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":nombreUsuario", $nombre); 
                $stmt->bindParam(":correoUsuario", $correo);
                $stmt->bindParam(":contraseniaUsuario", $password);

                if ($stmt->execute()) {
                    $mensaje = "Registro exitoso✅";    
                } else {
                    $mensaje = "⚠️Error al registrar usuario.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $mensaje = "⚠️El correo y el usuario ya están en uso. Por favor, crea otro.";
                } else {
                    $mensaje = "Error: " . $e->getMessage();
                }
            } finally {
                $conn = null;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registrarse - STRAV</title>
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/logo.png" />
    <link rel="icon" type="image/x-icon" href="imagenes/logo.png" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet" />
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
      .mensaje {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 10px;
        margin-bottom: 20px;
        color: #721c24;
      }
      .mensaje.exito {
        background-color: #d4edda;
        border-left-color: #28a745;
        color: #155724;
      }
    </style>
</head>
<body>
    <header class="mb-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 col-sm-4">
                    <div class="logo">
                        <img src="/imagenes/logo.png" alt="Logo de la Empresa" />
                    </div>
                </div>
                <div class="col-md-6 col-sm-8">
                    <div class="service-text">
                        Sistema Temprano de Recordatorios y Alertas de tu Vehículo
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-title">
                        Registrarse en STRAV
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">Registro</h2>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje <?php echo strpos($mensaje, 'exitoso') !== false ? 'exito' : ''; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="mb-3">
                    <input
                        type="text"
                        class="form-control"
                        id="nombreUsuario"
                        name="nombreUsuario"
                        placeholder="Nombre de usuario"
                        required
                        value="<?php echo isset($_POST['nombreUsuario']) ? htmlspecialchars($_POST['nombreUsuario']) : ''; ?>"
                    />
                </div>

                <div class="mb-3">
                    <input
                        type="email"
                        class="form-control"
                        id="correoUsuario"
                        name="correoUsuario"
                        placeholder="Correo Electrónico"
                        required
                        value="<?php echo isset($_POST['correoUsuario']) ? htmlspecialchars($_POST['correoUsuario']) : ''; ?>"
                    />
                </div>

                <div class="mb-3">
                    <input
                        type="password"
                        class="form-control"
                        id="contraseniaUsuario"
                        name="contraseniaUsuario"
                        placeholder="Contraseña"
                        required
                    />
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-custom">Registrarse</button>
                </div>
            </form>

            <p class="text-center mt-3">
                ¿Ya tienes una cuenta? <a href="https://violet-wolf-820033.hostingersite.com/iniciodesesion.php">Inicia sesión aquí</a>
            </p>

            <div class="text-center mt-3">
                <a href="/" class="btn btn-secondary">⟵ Volver al inicio</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p class="mb-0">&copy; 2024 STRAV. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
