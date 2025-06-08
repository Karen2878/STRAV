// STRAV Frontend JavaScript Optimizado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tema
    initTheme();
    
    // Configurar event listeners
    setupEventListeners();
    
    // Precargar imágenes críticas
    preloadImages();
    
    // Lazy loading si está disponible
    if ('IntersectionObserver' in window) {
        setupLazyLoading();
    }
});

// Manejo de tema claro/oscuro
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);
}

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
    const icon = document.getElementById('themeIcon');
    if (icon) {
        icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars';
    }
}

// Event listeners optimizados
function setupEventListeners() {
    // Theme toggle
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Formulario de foto
    const photoForm = document.getElementById('formFotoPerfil');
    if (photoForm) {
        photoForm.addEventListener('submit', handlePhotoUpload);
    }

    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Manejo del formulario de foto
function handlePhotoUpload(e) {
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const fileInput = e.target.querySelector('input[type="file"]');
    
    if (!fileInput.files.length) {
        e.preventDefault();
        alert('Por favor selecciona una imagen');
        return;
    }

    // Validación de archivo
    const file = fileInput.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (file.size > maxSize) {
        e.preventDefault();
        alert('La imagen es muy grande. Máximo 5MB');
        return;
    }

    if (!allowedTypes.includes(file.type)) {
        e.preventDefault();
        alert('Tipo de archivo no válido. Use JPEG, PNG, GIF o WebP');
        return;
    }

    // Mostrar indicador de carga
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Subiendo...';
        
        // Restaurar botón después de 10 segundos (timeout)
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Actualizar';
        }, 10000);
    }
}

// Lazy loading para imágenes
function setupLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px'
    });

    images.forEach(img => imageObserver.observe(img));
}

// Precargar imágenes críticas
function preloadImages() {
    const criticalImages = [
        'imagenes/logo.png',
        'imagenes/default_user.png'
    ];

    criticalImages.forEach(src => {
        const img = new Image();
        img.src = src;
    });
}

// Función para mostrar/ocultar formulario de foto (global para compatibilidad)
function togglePhotoForm() {
    const form = document.getElementById('formFotoPerfil');
    if (form) {
        form.classList.toggle('d-none');
        
        // Focus en el input de archivo cuando se muestra
        if (!form.classList.contains('d-none')) {
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput) {
                setTimeout(() => fileInput.focus(), 100);
            }
        }
    }
}

// Utilidades adicionales
const Utils = {
    // Debounce function para optimizar eventos
    debounce: function(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    },

    // Función para mostrar notificaciones
    showNotification: function(message, type = 'info') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
};

// Error handling global
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // En producción, podrías enviar este error a un servicio de logging
});

// Performance monitoring (opcional)
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            console.log('Page Load Time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
        }, 0);
    });
}