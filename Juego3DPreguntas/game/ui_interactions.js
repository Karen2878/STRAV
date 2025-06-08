
// ui-interactions.js
document.getElementById('btn-inicio').addEventListener('click', () => {
  alert('Botón Inicio pulsado.');
});
document.getElementById('btn-puntajes').addEventListener('click', () => {
  alert('Botón Puntajes pulsado.');
});
document.getElementById('btn-ayuda').addEventListener('click', () => {
  alert('Botón Ayuda pulsado.');
});
document.getElementById('link-contacto').addEventListener('click', e => {
  e.preventDefault();
  alert('Contacto: tuemail@ejemplo.com');
});
