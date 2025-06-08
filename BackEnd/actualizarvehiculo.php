<?php
session_start();
include 'conexion.php';

// Función opcional de log
function log_error($mensaje) {
    file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ": " . $mensaje . "\n", FILE_APPEND);
}

// Verificar sesión
if (!isset($_SESSION['idUsuario'])) {
    log_error("Intento sin sesión iniciada.");
    die("❌ Error: No has iniciado sesión.");
}

// Verificar parámetro GET
if (!isset($_GET['idVeh']) || !is_numeric($_GET['idVeh'])) {
    log_error("ID de vehículo inválido o no enviado.");
    die("❌ Error: No se ha especificado un vehículo válido para actualizar.");
}

$idVeh = $_GET['idVeh'];

try {
    $sql = "SELECT * FROM vehiculo WHERE idVeh = :idVeh AND idUsuario = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idVeh', $idVeh, PDO::PARAM_INT);
    $stmt->bindParam(':idUsuario', $_SESSION['idUsuario'], PDO::PARAM_INT);
    $stmt->execute();
    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        log_error("Vehículo con id $idVeh no encontrado para el usuario {$_SESSION['idUsuario']}");
        die("❌ Error: El vehículo no fue encontrado o no tienes permisos.");
    }
} catch (PDOException $e) {
    log_error("Error de base de datos al obtener vehículo: " . $e->getMessage());
    die("❌ Error al recuperar información del vehículo.");
} finally {
    $conn = null;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Vehículo - STRAV</title>
    <link rel="icon" type="image/png" sizes="16x16" href="uploads/logo.png">
    <link rel="icon" type="image/x-icon" href="uploads/logo.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4a4a4a;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            position: relative;
        }
        .logo img {
            max-height: 80px;
        }
        .service-text {
            font-size: 16px;
            margin-left: 20px;
            color: #fff;
        }
        .header-title {
            position: absolute;
            right: 20px;
            top: 40px;
            font-size: 30px;
        }
        .logout-container {
            position: absolute;
            top: 120px;
            right: 20px;
        }
        .logout-button {
            background-color: #333;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-block;
        }
        .logout-button:hover {
            background-color: #1a100f;
            transform: scale(1.05);
        }
        .container {
            max-width: 400px;
            margin: 30px auto 50px;
            background-color: #fff;
            padding: 25px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            text-align: center;
        }
        h2 {
            color: #4a4a4a;
            margin-bottom: 15px;
            font-size: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 8px;
            text-align: left;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 6px;
            font-size: 14px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            background-color: #4a4a4a;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #666;
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 15px 0;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="uploads/logo.png" alt="Logo de STRAV">
        </div>
        <div class="service-text">
            Sistema Temprano de Recordatorios y Alertas de tu Vehículo
        </div>
        <div class="header-title">
            Actualizar Vehículo
        </div>
    </header>

    <div class="logout-container">
        <a class="logout-button" href="cerrarsesion.php">Cerrar Sesión</a>
    </div>

    <div class="container">
        <h2>Actualizar Vehículo</h2>
        <form action="procesaractualizacion.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="idVeh" value="<?= $vehiculo['idVeh']; ?>">
            <input type="hidden" name="imagenActual" value="<?= $vehiculo['imagen']; ?>">

            <label for="placaVeh">Placa:</label>
            <input type="text" name="placaVeh" value="<?= $vehiculo['placaVeh']; ?>" required>

            <label for="tipoVeh">Tipo de Vehículo:</label>
            <input type="text" name="tipoVeh" value="<?= $vehiculo['tipoVeh']; ?>" required>

            <label for="marcaVeh">Marca:</label>
            <input type="text" name="marcaVeh" value="<?= $vehiculo['marcaVeh']; ?>" required>

            <label for="anioVeh">Año:</label>
            <input type="number" name="anioVeh" value="<?= $vehiculo['anioVeh']; ?>" required>

            <label for="fechaCompraVeh">Fecha de Compra:</label>
            <input type="date" name="fechaCompraVeh" value="<?= $vehiculo['fechaCompraVeh']; ?>" required>

            <label for="imagen">Imagen:</label>
            <input type="file" name="imagen">


            <!-- Mostrar imagen actual -->
            <img src="uploads/<?= $vehiculo['imagen']; ?>" width="100" alt="Imagen del vehículo"><br><br>

            <button type="submit">Actualizar Vehículo</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 STRAV. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
