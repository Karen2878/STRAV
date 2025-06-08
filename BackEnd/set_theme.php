
<?php
session_start();

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['modoOscuro'])) {
    $_SESSION['modoOscuro'] = (bool)$data['modoOscuro'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No data received']);
}
