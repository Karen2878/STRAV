<?php
include 'conexion.php';

try {
    $stmt = $conn->query("SELECT id, marca, imagen FROM Vehiculo");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="vehiculo-option">';
        echo '<input type="radio" name="vehiculo" value="' . $row['id'] . '"> ';
        echo '<label>' . $row['id'] . ' - ' . $row['marca'] . '</label>';
        echo '<img src="' . $row['imagen'] . '" alt="Imagen del vehículo">';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
