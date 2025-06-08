<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['idUsuario'])) {
    die("❌ Error: No has iniciado sesión.");
}

include '../BackEnd/conexion.php';

try {
    $sql = "SELECT nombreUsuario, correoUsuario, fotoUsuario FROM usuario WHERE idUsuario = :idUsuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idUsuario', $_SESSION['idUsuario'], PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("❌ Error: Usuario no encontrado.");
    }
} catch (PDOException $e) {
    die("❌ Error en consulta: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sucursales de Pago - STRAV</title>
    <link rel="icon" type="image/png" sizes="16x16" href="imagenes/logo.png" />
    <link rel="icon" type="image/x-icon" href="imagenes/logo.png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&amp;display=swap" rel="stylesheet" />

    <!-- Leaflet CSS -->
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      crossorigin=""
    />
    <style>
      /* Modo normal */
      #mapaSucursal {
        height: 400px;
        width: 100%;
        border-radius: 10px;
        margin-top: 15px;
        border: 2px solid #dee2e6;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }

      /* Clase para fullscreen */
      .fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1050 !important;
        border-radius: 0 !important;
        background: white;
        border: none !important;
      }

      /* Botón salir pantalla completa posicionado en la esquina */
      #btnSalirFull.fullscreen-exit-btn {
        position: fixed !important;
        top: 20px !important;
        right: 20px !important;
        z-index: 1060 !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid #333 !important;
        color: #333 !important;
        font-weight: bold !important;
        padding: 12px 20px !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
        font-size: 16px !important;
      }

      #btnSalirFull.fullscreen-exit-btn:hover {
        background-color: rgba(255, 255, 255, 1) !important;
        transform: scale(1.05) !important;
      }

      body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        color: #212529;
      }

      header {
        background-color: #4a4a4a;
        color: #fff;
        padding: 15px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        font-size: 28px;
        font-weight: bold;
      }
      .main-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 30px;
        border-radius: 10px;
        background-color: #fff;
        flex-grow: 1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
        margin: 8px;
        font-weight: bold;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .btn-custom:hover {
        background-color: #333;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      }
      .btn-custom.active {
        background-color: #28a745;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(40,167,69,0.3);
      }
      .btn-back {
        background-color: #6c757d;
        color: white;
        transition: all 0.3s;
        text-decoration: none;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
      }
      .btn-back:hover {
        background-color: #5a6268;
        color: white;
        transform: translateY(-1px);
        text-decoration: none;
      }
      .card {
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: all 0.3s;
        margin-bottom: 25px;
        border-radius: 10px;
      }
      .card-header {
        background: linear-gradient(135deg, #4a4a4a, #333);
        color: white;
        font-weight: bold;
        border-radius: 10px 10px 0 0 !important;
        padding: 20px;
      }
      .button-icon {
        margin-right: 8px;
      }
      .sucursal-info {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #4a4a4a;
      }
      .sucursal-info h4 {
        color: #4a4a4a;
        margin-bottom: 15px;
        font-weight: 600;
      }
      .sucursal-info p {
        margin-bottom: 8px;
        color: #666;
      }
      .sucursal-info strong {
        color: #333;
      }
      /* Dark mode styles */
      [data-bs-theme="dark"] body {
        background-color: #121212;
        color: #f1f1f1;
      }
      [data-bs-theme="dark"] header {
        background-color: #1a1a1a !important;
        color: #fff !important;
        border: none !important;
      }
      [data-bs-theme="dark"] .main-container,
      [data-bs-theme="dark"] .card {
        background-color: #2d2d2d !important;
        color: #e0e0e0 !important;
      }
      [data-bs-theme="dark"] .card-header {
        background: linear-gradient(135deg, #333, #1a1a1a) !important;
      }
      [data-bs-theme="dark"] .btn-custom {
        background-color: #404040 !important;
        color: #fff !important;
      }
      [data-bs-theme="dark"] .btn-custom:hover {
        background-color: #555 !important;
      }
      [data-bs-theme="dark"] .btn-custom.active {
        background-color: #28a745 !important;
      }
      [data-bs-theme="dark"] .btn-back {
        background-color: #555 !important;
        color: white !important;
      }
      [data-bs-theme="dark"] .btn-back:hover {
        background-color: #666 !important;
      }
      [data-bs-theme="dark"] footer {
        background-color: #1a1a1a !important;
        color: #fff !important;
        border: none !important;
      }
      [data-bs-theme="dark"] .sucursal-info {
        background-color: #333 !important;
        border-left-color: #666 !important;
      }
      [data-bs-theme="dark"] .sucursal-info h4 {
        color: #e0e0e0 !important;
      }
      [data-bs-theme="dark"] .sucursal-info p {
        color: #ccc !important;
      }
      [data-bs-theme="dark"] .sucursal-info strong {
        color: #fff !important;
      }
      [data-bs-theme="dark"] #btnSalirFull.fullscreen-exit-btn {
        background-color: rgba(45, 45, 45, 0.95) !important;
        border: 2px solid #fff !important;
        color: #fff !important;
      }
      [data-bs-theme="dark"] #btnSalirFull.fullscreen-exit-btn:hover {
        background-color: rgba(45, 45, 45, 1) !important;
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
</head>
<body>
<header class="mb-4">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-2 col-sm-4">
        <div class="logo">
          <img src="imagenes/logo.png" alt="Logo de STRAV" class="img-fluid" />
        </div>
      </div>
      <div class="col-md-6 col-sm-8">
        <div class="service-text">Sistema Temprano de Recordatorios y Alertas de tu Vehículo</div>
      </div>
      <div class="col-md-4 text-end">
        <div class="header-title">Sucursales de Pago</div>
      </div>
    </div>
  </div>
</header>

<!-- Botones de navegación y tema -->
<div class="container">
  <div class="row mb-3">
    <div class="col-md-6">
      <a href="/FrontEnd/paginaprincipal.php" class="btn btn-back">
  <i class="bi bi-arrow-left button-icon"></i>Volver al Inicio


      </a>
    </div>
    <div class="col-md-6 text-end">
      <button id="themeToggle" class="btn btn-outline-secondary btn-sm" onclick="toggleTheme()">
        <i id="themeIcon" class="bi bi-moon-stars"></i> Modo Oscuro / Claro
      </button>
    </div>
  </div>
</div>

<div class="container position-relative">
  <div class="main-container">
    <!-- Información del usuario -->
    <div class="card mb-4">
      <div class="card-header text-center">
        <i class="bi bi-geo-alt-fill me-2"></i>
        Sucursales de Pago - STRAV
      </div>
      <div class="card-body text-center">
        <h5 class="card-title">
          Bienvenido, <strong><?php echo htmlspecialchars($usuario['nombreUsuario']); ?>!</strong>
        </h5>
        <p class="card-text">Encuentra la sucursal de pago más cercana a tu ubicación.</p>
        <p class="card-text">Selecciona una zona para ver la información detallada y ubicación en el mapa.</p>
      </div>
    </div>

    <!-- Botones de sucursales -->
    <div class="card mb-4">
      <div class="card-header text-center">
        <i class="bi bi-building me-2"></i>
        Selecciona una Sucursal
      </div>
      <div class="card-body">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="row g-3">
              <div class="col-6 col-md-3">
                <button class="btn btn-custom w-100" onclick="mostrarMapa('NORTE')" id="btn-NORTE">
                  <i class="bi bi-compass button-icon"></i>NORTE
                </button>
              </div>
              <div class="col-6 col-md-3">
                <button class="btn btn-custom w-100" onclick="mostrarMapa('SUR')" id="btn-SUR">
                  <i class="bi bi-compass button-icon"></i>SUR
                </button>
              </div>
              <div class="col-6 col-md-3">
                <button class="btn btn-custom w-100" onclick="mostrarMapa('ORIENTE')" id="btn-ORIENTE">
                  <i class="bi bi-compass button-icon"></i>ORIENTE
                </button>
              </div>
              <div class="col-6 col-md-3">
                <button class="btn btn-custom w-100" onclick="mostrarMapa('OCCIDENTE')" id="btn-OCCIDENTE">
                  <i class="bi bi-compass button-icon"></i>OCCIDENTE
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Información de la sucursal seleccionada -->
    <div id="infoSucursal" class="sucursal-info" style="display: none;">
      <h4 id="nombreSucursal"></h4>
      <p><strong>Dirección:</strong> <span id="direccionSucursal"></span></p>
      <p><strong>Descripción:</strong> <span id="descripcionSucursal"></span></p>
      <p><strong>Zona:</strong> <span id="zonaSucursal"></span></p>
    </div>

    <!-- Mapa -->
    <div class="card">
      <div class="card-header text-center">
        <i class="bi bi-map me-2"></i>
        Ubicación en el Mapa
      </div>
      <div class="card-body">
        <div id="mapaSucursal"></div>
        
        <div class="text-center mt-3">
          <button id="btnPantallaCompleta" class="btn btn-outline-primary">
            <i class="bi bi-arrows-fullscreen me-2"></i>Ver en Pantalla Completa
          </button>
          <button id="btnSalirFull" class="btn btn-outline-secondary" style="display:none;">
            <i class="bi bi-arrows-collapse me-2"></i>Salir de Pantalla Completa
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

<footer>
  <div class="container">
    <p class="mb-0">&copy; 2024 STRAV. Todos los derechos reservados.</p>
  </div>
</footer>

<!-- Bootstrap JS Bundle con Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
  let mapa;
  let marcador;
  let sucursalActual = null;

  function mostrarMapa(codigo) {
    fetch(`../BackEnd/api_sucursal.php?codigo=${codigo}`)

      .then((res) => res.json())
      .then((data) => {
        if (data.error) {
          alert(data.error);
          return;
        }

        const lat = data.latitud;
        const lng = data.longitud;

        // Actualizar información de la sucursal
        document.getElementById('nombreSucursal').textContent = data.nombre;
        document.getElementById('direccionSucursal').textContent = data.direccion;
        document.getElementById('descripcionSucursal').textContent = data.descripcion;
        document.getElementById('zonaSucursal').textContent = codigo;
        document.getElementById('infoSucursal').style.display = 'block';

        // Crear o actualizar mapa
        if (!mapa) {
          mapa = L.map('mapaSucursal').setView([lat, lng], 16);
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          }).addTo(mapa);
        } else {
          mapa.setView([lat, lng], 16);
          if (marcador) mapa.removeLayer(marcador);
        }

        // Crear marcador personalizado
        const customIcon = L.divIcon({
          className: 'custom-marker',
          html: `<div style="background-color: #4a4a4a; color: white; padding: 8px 12px; border-radius: 20px; font-weight: bold; font-size: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); border: 2px solid white;">
                   <i class="bi bi-building" style="margin-right: 5px;"></i>${codigo}
                 </div>`,
          iconSize: [120, 40],
          iconAnchor: [60, 40]
        });

        marcador = L.marker([lat, lng], { icon: customIcon })
          .addTo(mapa)
          .bindPopup(`
            <div style="font-family: 'Poppins', sans-serif; max-width: 250px;">
              <h6 style="color: #4a4a4a; margin-bottom: 10px; font-weight: bold;">
                <i class="bi bi-building me-2"></i>${data.nombre}
              </h6>
              <p style="margin-bottom: 8px; color: #666;">
                <i class="bi bi-geo-alt me-2"></i><strong>Dirección:</strong><br>
                ${data.direccion}
              </p>
              <p style="margin-bottom: 0; color: #666;">
                <i class="bi bi-info-circle me-2"></i><strong>Descripción:</strong><br>
                ${data.descripcion}
              </p>
            </div>
          `)
          .openPopup();

        // Actualizar botones activos
        document.querySelectorAll('.btn-custom').forEach(btn => {
          btn.classList.remove('active');
        });
        document.getElementById(`btn-${codigo}`).classList.add('active');

        sucursalActual = codigo;

        // Desplazar hacia el mapa suavemente
        document.getElementById('mapaSucursal').scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center' 
        });
      })
      .catch((e) => {
        console.error('Error:', e);
        alert('Error cargando información de la sucursal: ' + e.message);
      });
  }

  // Mostrar NORTE por defecto al cargar la página
  document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      mostrarMapa('NORTE');
    }, 500);
  });

  // Toggle tema claro/oscuro
  function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Actualizar icono
    const icon = document.getElementById('themeIcon');
    if (newTheme === 'dark') {
      icon.className = 'bi bi-sun-fill';
    } else {
      icon.className = 'bi bi-moon-stars';
    }
  }

  // Cargar tema guardado
  document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    
    const icon = document.getElementById('themeIcon');
    if (savedTheme === 'dark') {
      icon.className = 'bi bi-sun-fill';
    } else {
      icon.className = 'bi bi-moon-stars';
    }
  });

  // Funcionalidad de pantalla completa
  const btnPantallaCompleta = document.getElementById('btnPantallaCompleta');
  const btnSalirFull = document.getElementById('btnSalirFull');
  const mapaDiv = document.getElementById('mapaSucursal');

  btnPantallaCompleta.addEventListener('click', () => {
    const isFullscreen = mapaDiv.classList.toggle('fullscreen');
    
    // Cambiar visibilidad de botones
    btnPantallaCompleta.style.display = isFullscreen ? 'none' : '';
    btnSalirFull.style.display = isFullscreen ? '' : 'none';
    
    // Agregar clase especial para posicionamiento cuando está en pantalla completa
    if (isFullscreen) {
      btnSalirFull.classList.add('fullscreen-exit-btn');
      document.body.style.overflow = 'hidden'; // Prevenir scroll del body
    } else {
      btnSalirFull.classList.remove('fullscreen-exit-btn');
      document.body.style.overflow = 'auto';
    }

    // Redimensionar mapa
    if (mapa) {
      setTimeout(() => {
        mapa.invalidateSize();
      }, 100);
    }
  });

  btnSalirFull.addEventListener('click', () => {
    // Salir de fullscreen
    mapaDiv.classList.remove('fullscreen');
    btnSalirFull.classList.remove('fullscreen-exit-btn');
    btnPantallaCompleta.style.display = '';
    btnSalirFull.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Redimensionar mapa
    if (mapa) {
      setTimeout(() => {
        mapa.invalidateSize();
      }, 100);
    }
  });

  // Cerrar pantalla completa con tecla Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && mapaDiv.classList.contains('fullscreen')) {
      btnSalirFull.click();
    }
  });

  // Añadir algunos estilos CSS personalizados para el marcador
  const style = document.createElement('style');
style.textContent = `
  .custom-marker {
    background: transparent !important;
    border: none !important;
  }

  .leaflet-popup-content-wrapper {
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }

  .leaflet-popup-tip {
    background: white;
  }

  [data-bs-theme="dark"] .leaflet-popup-content-wrapper {
    background-color: #2d2d2d !important;
    color: #e0e0e0 !important;
  }

  [data-bs-theme="dark"] .leaflet-popup-content-wrapper h6,
  [data-bs-theme="dark"] .leaflet-popup-content-wrapper p,
  [data-bs-theme="dark"] .leaflet-popup-content-wrapper strong {
    color: #f1f1f1 !important;
  }

  [data-bs-theme="dark"] .leaflet-popup-tip {
    background: #2d2d2d !important;
  }

  .leaflet-control-zoom a {
    background-color: #4a4a4a !important;
    color: white !important;
    border: 1px solid #333 !important;
  }

  .leaflet-control-zoom a:hover {
    background-color: #333 !important;
  }

  [data-bs-theme="dark"] .leaflet-control-zoom a {
    background-color: #333 !important;
    color: white !important;
    border: 1px solid #555 !important;
  }

  [data-bs-theme="dark"] .leaflet-control-zoom a:hover {
    background-color: #555 !important;
  }
`;
document.head.appendChild(style);
</script>

</body>
</html>