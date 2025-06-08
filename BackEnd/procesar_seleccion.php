<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../BackEnd/conexion.php';

if (!isset($_SESSION['idUsuario'])) {
    die("\u274c Error: No has iniciado sesi\u00f3n.");
}

$idUsuario = $_SESSION['idUsuario'];
$sql = "SELECT idVeh, marcaVeh, imagen FROM Vehiculo WHERE idUsuario = :idUsuario";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':idUsuario', $idUsuario);
$stmt->execute();
$vehiculos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Pico y Placa - STRAV</title>
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/logo.png">
    <link rel="icon" type="image/x-icon" href="imagenes/logo.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .container {
            max-width: 600px;
            margin: 100px auto;
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            text-align: center;
        }
        h2 {
            color: #4a4a4a;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            border-radius: 5px;
            max-width: 100px;
            max-height: 100px;
        }
        .form-group {
            margin: 10px 0;
            text-align: left;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .button-container button {
            background-color: #4a4a4a;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s, transform 0.2s;
            flex: 1;
            margin: 0 5px;
        }
        .button-container button:hover {
            background-color: #666;
            transform: scale(1.05);
        }
        #resultado {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">
            <img src="imagenes/logo.png" alt="Logo de STRAV">
        </div>
        <div class="service-text">
            Servicio de recordatorios y alertas tempranas de tu vehículo
        </div>
        <div class="header-title">
            Consultar Pico y Placa - STRAV
        </div>
    </header>

    <div class="container">
        <h2>Selecciona un Vehículo</h2>
        
        <form id="consultaForm">
            <table>
                <tr>
                    <th>Seleccionar</th>
                    <th>ID</th>
                    <th>Marca</th>
                    <th>Imagen</th>
                </tr>

                <?php foreach ($vehiculos as $vehiculo): ?>
                <tr>
                    <td><input type="radio" name="vehiculoSeleccionado" value="<?= $vehiculo['idVeh'] ?>" required></td>
                    <td><?= $vehiculo['idVeh'] ?></td>
                    <td><?= htmlspecialchars($vehiculo['marcaVeh']) ?></td>
                    <td>
                        <img src="<?= "../BackEnd/uploads/" . $vehiculo['imagen'] ?>" alt="Imagen del vehículo">
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div class="form-group">
                <label for="fecha">Seleccionar Fecha:</label>
                <input type="date" id="fecha" name="fecha" required>
            </div>

            <div class="button-container">
                <button type="button" id="consultarBtn">Consultar Pico y Placa</button>
            </div>
        </form>

        <div id="resultado"></div>
    </div>

    <script>
        $(document).ready(function() {
            $('#consultarBtn').click(function() {
                var vehiculoSeleccionado = $('input[name="vehiculoSeleccionado"]:checked').val();
                var fecha = $('#fecha').val();

                if (!vehiculoSeleccionado || !fecha) {
                    alert('Por favor, seleccione un vehículo y una fecha.');
                    return;
                }

                $.ajax({
                    url: 'procesar_seleccion.php',
                    type: 'POST',
                    data: { vehiculoSeleccionado: vehiculoSeleccionado, fecha: fecha },
                    dataType: 'json',
                    success: function(response) {
                        $('#resultado').text(response.restriccion);
                    },
                    error: function() {
                        alert('Error al consultar pico y placa.');
                    }
                });
            });
        });
    </script>
</body>
</html>
