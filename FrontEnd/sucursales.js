// sucursales.js - Manejo avanzado de sucursales con API

class SucursalesManager {
    constructor() {
        this.map = null;
        this.markers = [];
        this.infoWindows = [];
        this.apiUrl = '../BackEnd/api_geolocal.php';
        this.sucursalesData = {};
    }

    // Inicializar el manager
    async init() {
        try {
            await this.cargarSucursales();
            this.setupEventListeners();
        } catch (error) {
            console.error('Error inicializando SucursalesManager:', error);
        }
    }

    // Cargar sucursales desde la API
    async cargarSucursales() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get_all_sucursales`);
            const data = await response.json();
            
            if (data.success) {
                // Organizar datos por zona
                data.data.forEach(sucursal => {
                    this.sucursalesData[sucursal.zona] = sucursal;
                });
            } else {
                throw new Error(data.error || 'Error cargando sucursales');
            }
        } catch (error) {
            console.error('Error cargando sucursales:', error);
            // Usar datos de respaldo si falla la API
            this.usarDatosRespaldo();
        }
    }

    // Datos de respaldo en caso de fallo de API
    usarDatosRespaldo() {
        this.sucursalesData = {
            'NORTE': {
                nombre_sucursal: 'Centro de Servicios Automotrices Chapinero',
                direccion: 'Carrera 13 #63-42, Chapinero, Bogotá',
                latitud: 4.651710,
                longitud: -74.065950,
                servicios: 'SOAT, Licencia de Conducción, Tecnomecánica, Revisión Preventiva',
                telefono: '601-3456789',
                horario: 'Lunes a Viernes: 8:00 AM - 5:00 PM, Sábados: 8:00 AM - 12:00 PM'
            },
            'SUR': {
                nombre_sucursal: 'Centro Integral Vehicular San Andresito',
                direccion: 'Carrera 38 #5A-22, San Andresito, Bogotá',
                latitud: 4.578250,
                longitud: -74.112340,
                servicios: 'SOAT, Tecnomecánica, Licencia de Conducción, Peritajes',
                telefono: '601-2789456',
                horario: 'Lunes a Viernes: 7:30 AM - 5:30 PM, Sábados: 8:00 AM - 1:00 PM'
            },
            'ORIENTE': {
                nombre_sucursal: 'Servicios Automotrices Restrepo',
                direccion: 'Calle 17 Sur #22-45, Restrepo, Bogotá',
                latitud: 4.602340,
                longitud: -74.084560,
                servicios: 'SOAT, Tecnomecánica, Licencia de Conducción, Seguros Vehiculares',
                telefono: '601-4567123',
                horario: 'Lunes a Viernes: 8:00 AM - 6:00 PM, Sábados: 8:00 AM - 2:00 PM'
            },
            'OCCIDENTE': {
                nombre_sucursal: 'Centro de Trámites Vehiculares Fontibón',
                direccion: 'Avenida Esperanza #95-42, Fontibón, Bogotá',
                latitud: 4.689450,
                longitud: -74.145670,
                servicios: 'SOAT, Licencia de Conducción, Tecnomecánica, Trámites RUNT',
                telefono: '601-6789234',
                horario: 'Lunes a Viernes: 8:00 AM - 5:00 PM, Sábados: 9:00 AM - 1:00 PM'
            }
        };
    }

    // Configurar event listeners
    setupEventListeners() {
        // Listener para el botón de cerrar mapa
        const closeMapBtn = document.querySelector('.close-map-btn');
        if (closeMapBtn) {
            closeMapBtn.addEventListener('click', () => this.cerrarMapa());
        }

        // Listeners para los botones de zona
        const botonesSucursal = document.querySelectorAll('.btn-sucursal');
        botonesSucursal.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const zona = e.target.textContent.trim();
                this.mostrarMapa(zona);
            });
        });
    }

    // Mostrar mapa para una zona específica
    async mostrarMapa(zona) {
        try {
            const sucursal = this.sucursalesData[zona];
            if (!sucursal) {
                throw new Error(`No se encontró información para la zona ${zona}`);
            }

            const mapContainer = document.getElementById('mapContainer');
            const sucursalInfo = document.getElementById('sucursalInfo');
            const infoContent = document.getElementById('infoContent');
            
            // Mostrar contenedores con animación
            mapContainer.style.display = 'block';
            sucursalInfo.style.display = 'block';
            
            // Limpiar markers anteriores
            this.limpiarMarcadores();
            
            // Crear el mapa
            this.map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: { 
                    lat: parseFloat(sucursal.latitud), 
                    lng: parseFloat(sucursal.longitud) 
                },
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                styles: this.getMapStyles()
            });
            
            // Crear marcador
            const marker = new google.maps.Marker({
                position: { 
                    lat: parseFloat(sucursal.latitud), 
                    lng: parseFloat(sucursal.longitud) 
                },
                map: this.map,
                title: sucursal.nombre_sucursal,
                animation: google.maps.Animation.DROP,
                icon: this.getCustomMarkerIcon()
            });
            
            this.markers.push(marker);
            
            // Crear ventana de información
            const infoWindow = new google.maps.InfoWindow({
                content: this.createInfoWindowContent(sucursal)
            });
            
            this.infoWindows.push(infoWindow);
            
            // Abrir ventana de información al hacer clic en el marcador
            marker.addListener('click', () => {
                // Cerrar otras ventanas de información
                this.infoWindows.forEach(window => window.close());
                infoWindow.open(this.map, marker);
            });
            
            // Abrir automáticamente la ventana de información
            setTimeout(() => {
                infoWindow.open(this.map, marker);
            }, 500);
            
            // Mostrar información de la sucursal
            infoContent.innerHTML = this.createSucursalInfoContent(sucursal);
            
            // Scroll hacia el mapa
            mapContainer.scrollIntoView({ behavior: 'smooth' });
            
            // Agregar botón para obtener direcciones
            this.agregarBotonDirecciones(sucursal);
            
        } catch (error) {
            console.error('Error mostrando mapa:', error);
            this.mostrarError('Error cargando la información de la sucursal');
        }
    }

    // Crear contenido de la ventana de información
    createInfoWindowContent(sucursal) {
        return `
            <div style="max-width: 300px; font-family: 'Poppins', sans-serif;">
                <h6 style="color: #2d5a87; font-weight: bold; margin-bottom: 10px;">
                    <i class="bi bi-building" style="margin-right: 5px;"></i>
                    ${sucursal.nombre_sucursal}
                </h6>
                <p style="margin: 5px 0; font-size: 14px;">
                    <strong>📍 Dirección:</strong><br>
                    ${sucursal.direccion}
                </p>
                <p style="margin: 5px 0; font-size: 14px;">
                    <strong>📞 Teléfono:</strong> ${sucursal.telefono || 'No disponible'}
                </p>
                <p style="margin: 5px 0; font-size: 14px;">
                    <strong>🕒 Horario:</strong><br>
                    ${sucursal.horario || 'Consultar por teléfono'}
                </p>
                <div style="margin-top: 10px;">
                    <button onclick="sucursalesManager.abrirEnGoogleMaps('${sucursal.latitud}', '${sucursal.longitud}')" 
                            style="background: #4285f4; color: white; border: none; padding: 5px 10px; border-radius: 3px; font-size: 12px;">
                        Ver en Google Maps
                    </button>
                </div>
            </div>
        `;
    }

    // Crear contenido de información de sucursal
    createSucursalInfoContent(sucursal) {
        return `
            <h5 style="color: #2d5a87; margin-top: 10px;">
                <i class="bi bi-building me-2"></i>${sucursal.nombre_sucursal}
            </h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong><i class="bi bi-geo-alt me-2"></i>Dirección:</strong><br>
                    ${sucursal.direccion}</p>
                    <p><strong><i class="bi bi-telephone me-2"></i>Teléfono:</strong><br>
                    ${sucursal.telefono || 'No disponible'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong><i class="bi bi-clock me-2"></i>Horario:</strong><br>
                    ${sucursal.horario || 'Consultar por teléfono'}</p>
                    <p><strong><i class="bi bi-tools me-2"></i>Servicios:</strong><br>
                    ${sucursal.servicios}</p>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary btn-sm me-2" onclick="sucursalesManager.abrirEnGoogleMaps('${sucursal.latitud}', '${sucursal.longitud}')">
                    <i class="bi bi-map me-1"></i>Ver en Google Maps
                </button>
                <button class="btn btn-success btn-sm" onclick="sucursalesManager.compartirUbicacion('${sucursal.nombre_sucursal}', '${sucursal.direccion}')">
                    <i class="bi bi-share me-1"></i>Compartir
                </button>
            </div>
        `;
    }

    // Obtener estilos personalizados para el mapa
    getMapStyles() {
        return [
            {
                "featureType": "poi",
                "elementType": "labels",
                "stylers": [{"visibility": "off"}]
            },
            {
                "featureType": "transit",
                "elementType": "labels",
                "stylers": [{"visibility": "off"}]
            }
        ];
    }

    // Obtener ícono personalizado para el marcador
    getCustomMarkerIcon() {
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" fill="#2d5a87" stroke="#ffffff" stroke-width="2"/>
                    <text x="20" y="26" font-family="Arial" font-size="14" fill="white" text-anchor="middle" font-weight="bold">🏢</text>
                </svg>
            `),
            scaledSize: new google.maps.Size(40, 40),
            anchor: new google.maps.Point(20, 20)
        };
    }

    // Limpiar marcadores anteriores
    limpiarMarcadores() {
        this.markers.forEach(marker => marker.setMap(null));
        this.infoWindows.forEach(window => window.close());
        this.markers = [];
        this.infoWindows = [];
    }

    // Cerrar mapa
    cerrarMapa() {
        const mapContainer = document.getElementById('mapContainer');
        const sucursalInfo = document.getElementById('sucursalInfo');
        
        mapContainer.style.display = 'none';
        sucursalInfo.style.display = 'none';
        
        this.limpiarMarcadores();
        this.map = null;
    }

    // Abrir en Google Maps
    abrirEnGoogleMaps(lat, lng) {
        const url = `https://www.google.com/maps?q=${lat},${lng}`;
        window.open(url, '_blank');
    }

    // Compartir ubicación
    compartirUbicacion(nombre, direccion) {
        if (navigator.share) {
            navigator.share({
                title: `Sucursal STRAV - ${nombre}`,
                text: `Te comparto la ubicación de esta sucursal: ${direccion}`,
                url: window.location.href
            });
        } else {
            // Fallback para navegadores que no soportan Web Share API
            const texto = `Sucursal STRAV - ${nombre}\nDirección: ${direccion}`;
            navigator.clipboard.writeText(texto).then(() => {
                alert('Información copiada al portapapeles');
            });
        }
    }

    // Mostrar error
    mostrarError(mensaje) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.innerHTML = `
            <strong>Error:</strong> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.sucursales-container');
        container.insertBefore(errorDiv, container.firstChild);
    }

    // Buscar sucursales cercanas (funcionalidad adicional)
    async buscarSucursalesCercanas(lat, lng, radio = 10) {
        try {
            const response = await fetch(`${this.apiUrl}?action=search_nearby&lat=${lat}&lng=${lng}&radio=${radio}`);
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Error buscando sucursales cercanas:', error);
            return [];
        }
    }

    // Obtener ubicación del usuario
    async obtenerUbicacionUsuario() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocalización no soportada'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    resolve({
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                },
                (error) => {
                    reject(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        });
    }
}

// Instancia global del manager
let sucursalesManager;

// Inicializar cuando se cargue la página
document.addEventListener('DOMContentLoaded', async () => {
    sucursalesManager = new SucursalesManager();
    await sucursalesManager.init();
});

// Función global para mostrar mapa (compatibilidad con el código existente)
function mostrarMapa(zona) {
    if (sucursalesManager) {
        sucursalesManager.mostrarMapa(zona);
    }
}

// Función global para cerrar mapa (compatibilidad con el código existente)
function cerrarMapa() {
    if (sucursalesManager) {
        sucursalesManager.cerrarMapa();
    }
}