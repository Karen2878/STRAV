<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['idUsuario'])) {
    die("❌ Error: No has iniciado sesión.");
}

include '../BackEnd/conexion.php';

// Cache de datos de usuario por 5 minutos
if (!isset($_SESSION['usuario_cache']) || 
    !isset($_SESSION['usuario_cache_time']) || 
    (time() - $_SESSION['usuario_cache_time']) > 300) {
    
    try {
        $sql = "SELECT nombreUsuario, correoUsuario, fotoUsuario FROM usuario WHERE idUsuario = :idUsuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idUsuario', $_SESSION['idUsuario'], PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            die("❌ Error: Usuario no encontrado.");
        }
        
        $_SESSION['usuario_cache'] = $usuario;
        $_SESSION['usuario_cache_time'] = time();
    } catch (PDOException $e) {
        die("❌ Error en consulta: " . $e->getMessage());
    }
} else {
    $usuario = $_SESSION['usuario_cache'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Página Principal - STRAV</title>
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/logo.png" />
    <link rel="icon" type="image/x-icon" href="imagenes/logo.png" />
    
    <!-- DNS Prefetch para recursos externos -->
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    
    <!-- Preconnect para Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- CSS Crítico - Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- CSS No crítico - Diferido -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">

    <!-- CSS Crítico Inline -->
    <style>
      body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f5f5;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        color: #212529;
      }

      header {
        background-color: #4a4a4a;
        color: #fff;
        padding: 10px 20px;
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
        font-size: 30px;
        font-weight: bold;
      }
      .main-container {
        max-width: 800px;
        margin: 50px auto;
        padding: 30px;
        border-radius: 5px;
        background-color: #fff;
        flex-grow: 1;
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
        transition: all 0.3s;
        margin: 8px 0;
        font-weight: bold;
      }
      .btn-custom:hover {
        background-color: #666;
        color: #fff;
        transform: scale(1.05);
      }
      .logout-btn {
        background-color: #333;
        color: white;
        transition: all 0.3s;
      }
      .logout-btn:hover {
        background-color: #1a100f;
        color: white;
        transform: scale(1.05);
      }
      .card {
        border: none;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s;
        margin-bottom: 20px;
      }
      .card-header {
        background-color: #4a4a4a;
        color: white;
        font-weight: bold;
      }
      .button-icon {
        margin-right: 8px;
      }
    </style>
</head>
<body>
<header class="mb-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-2 col-sm-4">
        <div class="logo">
          <img src="imagenes/logo.png" 
               alt="Logo de STRAV" 
               class="img-fluid" 
               loading="lazy"
               width="80" 
               height="80" />
        </div>
      </div>
      <div class="col-md-6 col-sm-8">
        <div class="service-text">Sistema Temprano de Recordatorios y Alertas de tu Vehículo</div>
      </div>
      <div class="col-md-4 text-end">
        <div class="header-title">Página Principal - STRAV</div>
      </div>
    </div>
  </div>
</header>

<!-- Switch de modo claro/oscuro -->
<div class="container text-end mb-2">
  <button id="themeToggle" class="btn btn-outline-secondary btn-sm" onclick="toggleTheme()">
    <i id="themeIcon" class="bi bi-moon-stars"></i> Modo Oscuro / Claro
  </button>
</div>

<!-- Perfil de usuario destacado -->
<div class="container mt-4">
  <div class="card border-0 shadow-sm p-3 d-flex flex-row align-items-center justify-content-between flex-wrap bg-light bg-md-transparent">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <div style="position: relative; display: inline-block;">
        <img src="../imagenes/<?php echo !empty($usuario['fotoUsuario']) ? htmlspecialchars($usuario['fotoUsuario']) : 'default_user.png'; ?>"
             alt="Foto de usuario"
             class="rounded-circle img-thumbnail border-0"
             style="width: 70px; height: 70px; object-fit: cover"
             loading="lazy"
             width="70" 
             height="70" />
        <button onclick="document.getElementById('formFotoPerfil').classList.toggle('d-none')"
                style="position: absolute; bottom: 0; right: 0; background-color: rgba(0, 0, 0, 0.6); border: none; border-radius: 50%; padding: 4px;"
                title="Cambiar foto"
                aria-label="Cambiar foto de perfil">
          <i class="bi bi-camera text-white"></i>
        </button>
      </div>

      <div class="mt-2">
        <form id="formFotoPerfil"
              class="d-none"
              action="../FrontEnd/subir_foto.php"
              method="POST"
              enctype="multipart/form-data">
          <input type="file"
                 name="nuevaFoto"
                 accept="image/*"
                 class="form-control form-control-sm mb-1"
                 required />
          <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
        </form>
      </div>
      
      <div class="user-info" style="font-family: 'Poppins', sans-serif">
        <h5 class="mb-0 text-dark fw-semibold"><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></h5>
        <small class="text-muted"><?php echo htmlspecialchars($usuario['correoUsuario']); ?></small>
      </div>
    </div>
    <div class="mt-3 mt-md-0">
      <a href="../BackEnd/cerrarsesion.php" class="btn logout-btn d-flex align-items-center gap-2">
        <i class="bi bi-box-arrow-right"></i>
        <span>Cerrar Sesión</span>
      </a>
    </div>
  </div>
</div>

<div class="container position-relative">
  <div class="main-container">
    <div class="card mb-4">
      <div class="card-header text-center">Bienvenido a tu panel de usuario</div>
      <div class="card-body text-center">
        <h5 class="card-title">
          ¡Hola, <strong><?php echo htmlspecialchars($usuario['nombreUsuario']); ?>!</strong>
        </h5>
        <p class="card-text">Has iniciado sesión correctamente.</p>
        <p class="card-text">Desde aquí podrás gestionar tus vehículos y documentos legales.</p>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-6 mb-2">
        <a href="agregarvehiculo.html" class="btn btn-custom d-block">
          <i class="bi bi-plus-circle button-icon"></i>Agregar Vehículo
        </a>
      </div>
      <div class="col-md-6 mb-2">
        <a href="../BackEnd/mostrarvehiculos.php" class="btn btn-custom d-block">
          <i class="bi bi-car-front button-icon"></i>Consultar Vehículos
        </a>
      </div>
      <div class="col-md-6 mb-2">
        <a href="../FrontEnd/consultarpp.php" class="btn btn-custom d-block">
          <i class="bi bi-calendar-check button-icon"></i>Consultar Pico y Placa
        </a>
      </div>
      <div class="col-md-6 mb-2">
        <a id="btnConsultarVenc" href="../FrontEnd/consultarvd.php" class="btn btn-custom d-block">
          <i class="bi bi-file-earmark-text button-icon"></i>Consultar Venc. de Doc.
        </a>
      </div>
      <div class="col-md-12 mb-2">
        <a href="../BackEnd/sucursales.php" class="btn btn-custom d-block">
          <i class="bi bi-geo-alt button-icon"></i>Sucursales de Pago
        </a>
      </div>
      <div class="col-md-12 mb-2">
        <a href="../Juego3DPreguntas/index.php" class="btn btn-custom d-block">
          <i class="bi bi-joystick button-icon"></i>Sección de Aprendizaje
        </a>
      </div>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    <p class="mb-0">&copy; 2024 STRAV. Todos los derechos reservados.</p>
  </div>
</footer>

<!-- CSS No crítico diferido -->
<style id="deferred-styles">
  /* Dark mode styles */
  [data-bs-theme="dark"] body {
    background-color: #000000;
    color: #f1f1f1;
  }
  [data-bs-theme="dark"] header {
    background-color: #333 !important;
    color: #fff !important;
    border: none !important;
  }
  [data-bs-theme="dark"] .main-container,
  [data-bs-theme="dark"] .card,
  [data-bs-theme="dark"] .dropdown-menu {
    background-color: #606060 !important;
    color: #e0e0e0 !important;
  }
  [data-bs-theme="dark"] .btn-custom {
    background-color: #333 !important;
    color: #fff !important;
  }
  [data-bs-theme="dark"] .btn-custom:hover {
    background-color: #000000 !important;
  }
  [data-bs-theme="dark"] .logout-btn {
    background-color: #444 !important;
    color: white !important;
  }
  [data-bs-theme="dark"] .logout-btn:hover {
    background-color: #000000 !important;
  }
  [data-bs-theme="dark"] footer {
    background-color: #333 !important;
    color: #fff !important;
    border: none !important;
  }
  [data-bs-theme="dark"] .user-info h5 {
    color: #ffffff !important;
  }
  [data-bs-theme="dark"] .user-info small {
    color: #ffffff !important;
  }
  #themeToggle:hover {
    background-color: #4a4a4a;
    color: #fff;
    border-color: #4a4a4a;
  }
  [data-bs-theme="dark"] #themeToggle:hover {
    background-color: #e0e0e0;
    color: #000;
    border-color: #e0e0e0;
  }
</style>

<!-- JavaScript optimizado -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

<script>
(function() {
  'use strict';
  
  // Función para toggle de tema
  function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
  }
  
  // Hacer disponible globalmente
  window.toggleTheme = toggleTheme;
  
  // Cargar tema guardado inmediatamente
  const savedTheme = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', savedTheme);
  
  // Event listener optimizado
  document.addEventListener('DOMContentLoaded', function() {
    // Aplicar tema nuevamente por si acaso
    const theme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', theme);
  });
})();

// Cargar recursos no críticos después del load
window.addEventListener('load', function() {
  // Cargar Botpress de forma diferida
  setTimeout(function() {
    const script1 = document.createElement('script');
    script1.src = 'https://cdn.botpress.cloud/webchat/v2.5/inject.js';
    script1.async = true;
    document.head.appendChild(script1);
    
    script1.onload = function() {
      const script2 = document.createElement('script');
      script2.src = 'https://files.bpcontent.cloud/2025/05/21/03/20250521030247-61DSFN2B.js';
      script2.async = true;
      document.head.appendChild(script2);
    };
  }, 1000); // Cargar después de 1 segundo
});
</script>

</body>
</html>