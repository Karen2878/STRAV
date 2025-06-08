<?php
// Iniciar la sesión
session_start();

// Limpiar y destruir la sesión
session_unset();
session_destroy();

// Redirigir a index.html en la carpeta correcta
header("Location: https://violet-wolf-820033.hostingersite.com/");
exit();
?>
