
// game.js

const preguntas = [
  {
    texto: "¿Cuál es uno de los principales factores de accidentes viales en Bogotá?",
    opciones: ["Exceso de velocidad", "Buena señalización", "Alumbrado público"],
    correcta: 0
  },
  {
    texto: "¿Qué elemento de seguridad es obligatorio al conducir una motocicleta en Bogotá?",
    opciones: ["Chaleco reflectivo", "Casco homologado", "Guantes de cuero"],
    correcta: 1
  },
  {
    texto: "¿Qué significa una luz roja en un semáforo?",
    opciones: ["Avanzar con precaución", "Detenerse completamente", "Aumentar la velocidad"],
    correcta: 1
  },
  {
    texto: "¿Qué día es pico y placa para placas terminadas en 1 y 2 en Bogotá (2025)?",
    opciones: ["Lunes", "Martes", "Viernes"],
    correcta: 0 // Asegura actualizar si cambian las reglas
  },
  {
    texto: "¿Cuál es la velocidad máxima permitida en vías urbanas en Bogotá?",
    opciones: ["30 km/h", "50 km/h", "70 km/h"],
    correcta: 1
  },
  {
    texto: "¿Qué hacer si hay un peatón cruzando por un paso de cebra?",
    opciones: ["Tocar el claxon", "Detener el vehículo", "Acelerar para pasar primero"],
    correcta: 1
  },
  {
    texto: "¿Quién tiene prioridad en una glorieta en Bogotá?",
    opciones: ["El que entra a la glorieta", "El que ya circula dentro", "El vehículo más grande"],
    correcta: 1
  },
  {
    texto: "¿Qué significa una señal de tránsito en forma de triángulo invertido?",
    opciones: ["Pare", "Ceda el paso", "No girar"],
    correcta: 1
  },
  {
    texto: "¿Cuál es una infracción de tránsito en Colombia?",
    opciones: ["Detenerse en un semáforo en rojo", "No portar licencia de conducción", "Respetar los límites de velocidad"],
    correcta: 1
  },
  {
    texto: "¿Cuál de estos elementos es obligatorio para vehículos en Colombia?",
    opciones: ["Botiquín de primeros auxilios", "Sistema de navegación GPS", "Extintor de humo de colores"],
    correcta: 0
  }
];

let posicionActual = 0;
let monedas = 0;
let puedeAvanzar = true;

// Reproducir música de fondo
const musicaFondo = new Audio("models/BGM.mp3"); // ajusta la ruta si está en otra carpeta
musicaFondo.loop = true;
musicaFondo.volume = 0.15; // Volumen (0.0 a 1.0)

document.addEventListener("DOMContentLoaded", () => {
  // Reproducir cuando el usuario interactúe (por políticas de navegadores)
  const iniciarMusica = () => {
    musicaFondo.play().catch(err => {
      console.log("Autoplay bloqueado, espera interacción del usuario.");
    });
    document.removeEventListener("click", iniciarMusica);
  };
  document.addEventListener("click", iniciarMusica);
});

function mostrarInicio() {
  const contenedor = document.getElementById("contenedor");
  contenedor.innerHTML = `
    <div style="padding: 20px;">
      <h2 style="
        font-family: 'Press Start 2P', cursive;
        color: #FFD700;
        font-size: 25px;
        margin-bottom: 10px;
        text-align: left;
        text-shadow:
          -1px -1px 0 #000,
          1px -1px 0 #000,
          -1px  1px 0 #000,
          1px  1px 0 #000;
      ">
        ¡La Aventura de Stravi!
      </h2>
    </div>

    <div id="pantalla-inicio" class="pantalla" style="display: flex; align-items: flex-start; gap: 20px; padding: 20px; margin-top: -30px;">
      <img src="models/Stravi_sentado.png" alt="Stravi" style="width: 40%; height: auto; margin-left: 20px;" />
      
      <div style="
        background: #222;
        color: #fff;
        border: 4px solid #FFD700;
        border-radius: 12px;
        padding: 20px;
        max-width: 300px;
        font-family: 'Press Start 2P', cursive;
        position: relative;
        font-size: 10px;
        line-height: 1.8;
        margin-top: 40px;
      ">
        <p style="margin: 0;">¡Hola! Soy Stravi y te acompañaré en esta aventura. Presiona "Iniciar" para comenzar.</p>
        <div style="
          content: '';
          position: absolute;
          left: -20px;
          top: 30px;
          width: 0;
          height: 0;
          border-top: 10px solid transparent;
          border-bottom: 10px solid transparent;
          border-right: 20px solid #222;
        "></div>
      </div>
    </div>

    <div style="text-align: center; margin-top: -150px; margin-left: 250px;">
      <button id="btnContinuarHistoria" style="
        flex: 1;
        background: #222;
        color: #fff;
        padding: 20px;
        border: 4px solid #FFD700;
        border-radius: 12px;
        font-family: 'Press Start 2P', cursive;
        text-align: center;
        max-width: 50%;
        cursor: pointer;
      ">Iniciar</button>
    </div>
  `;
  document.getElementById("btnContinuarHistoria").onclick = mostrarHistoria;
}

function mostrarHistoria() {
  

  const contenedor = document.getElementById("contenedor");
  contenedor.innerHTML = `
    <div id="pantalla-historia" class="pantalla" style="display: flex; align-items: flex-start; justify-content: center; gap: 20px; padding: 60px 20px 20px; flex-wrap: wrap;">
      <img src="models/Stravi2.png" alt="Stravi" style="width: 55%; height: auto; margin-top: 30px;" />
      <div style="margin-top: 50px; flex: 1; background: #222; color: #fff; padding: 20px; border: 4px solid #FFD700; border-radius: 12px; font-family: 'Press Start 2P', cursive; text-align: center; max-width: 50%;">
        <p style="font-size: 10px; line-height: 1.5;">
          Hola, soy Stravi y vengo del CiberKilómetro 9, un lugar en donde los usuarios sí cumplen con sus documentos a tiempo.
          He sido enviado al mundo real para ayudarte a conquistar la jungla del papeleo vial. ¡Ven y demuéstra tus conocimientos en seguridad vial!
        </p>
        <button id="btnIniciarJuego" style="font-family: 'Press Start 2P', cursive;
          margin: 0 10px;
          padding: 5px 10px;
          background-color: #FFD700;
          border: none;
          border-radius: 6px;
          cursor: pointer;">Comenzar
        </button>
      </div>
    </div>
  `;
  document.getElementById("btnIniciarJuego").onclick = iniciarJuego;
}

function iniciarJuego() {
  const contenedor = document.getElementById("contenedor");
  contenedor.innerHTML = "<div id='posiciones'></div>";

  for (let i = 0; i < 10; i++) {
    const pos = document.createElement("div");
    pos.className = "posicion";
    document.getElementById("posiciones").appendChild(pos);
  }

  const personaje = document.createElement("img");
  personaje.src = "models/Stravi.png";
  personaje.id = "personaje";
  personaje.style.position = "absolute";
  personaje.style.bottom = "0px";
  personaje.style.left = "5%";
  personaje.style.width = "150px";
  personaje.style.transition = "left 0.3s";
  document.getElementById("posiciones").appendChild(personaje);

  mostrarPregunta(posicionActual);
  actualizarMonedas();
}

// Fuera de la función, al inicio del archivo (después del fondo musical)
const sonidoArranque = new Audio("models/arranque.mp3");
const sonidoFrenada = new Audio("models/frenada.mp3");

function mostrarPregunta(index) {
  if (index >= preguntas.length) {
    finalizarJuego();
    return;
  }

  const pregunta = preguntas[index];
  document.getElementById("textoPregunta").innerText = pregunta.texto;
  const opcionesDiv = document.getElementById("opciones");
  opcionesDiv.innerHTML = "";

  pregunta.opciones.forEach((opcion, i) => {
    const btn = document.createElement("button");
    btn.textContent = opcion;
    btn.onclick = () => {
      if (i === pregunta.correcta) {
        sonidoArranque.currentTime = 0;  // Reinicia el audio
        sonidoArranque.play();           // Reproduce sonido correcto
        if (puedeAvanzar) {
          monedas++;
          actualizarMonedas();
          avanzarPersonaje();
        } else {
          puedeAvanzar = true;
          avanzarPersonaje();
        }
        mostrarPregunta(posicionActual);
      } else {
        sonidoFrenada.currentTime = 0;   // Reinicia el audio
        sonidoFrenada.play();            // Reproduce sonido incorrecto
        mostrarMensajeError();
      }
    };
    opcionesDiv.appendChild(btn);
  });
}

function avanzarPersonaje() {
  posicionActual++;
  actualizarPosicionVisual();
  if (posicionActual >= 10) {
    finalizarJuego();
  }
}

function retrocederPersonaje() {
  if (posicionActual > 0) {
    posicionActual--;
    actualizarPosicionVisual();
  }
}

function actualizarMonedas() {
  document.getElementById("monedas").textContent = monedas;
}

function actualizarPosicionVisual() {
  const personaje = document.getElementById("personaje");
  personaje.style.left = (posicionActual * 10 + 5) + "%";
}

function finalizarJuego() {
  document.getElementById("preguntas-container").innerHTML = `
    <h2 style="color: #FFD700; text-align: center">🎉 ¡Juego Terminado!</h2>
    <p style="text-align: center">Obtuviste ${monedas} monedas</p>
    <button onclick="location.reload()" style="display:block; margin:auto">Jugar de nuevo</button>
  `;

  fetch("guardar_puntaje.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ monedas })
  });
}

function mostrarMensajeError() {
  const frases = [
    "Wow… eso fue una respuesta. No la correcta, pero sí una respuesta.",
    "Interesante elección... pero el universo tenía otros planes.",
    "No era esa, pero al menos ya descartamos una, ¿no? Avances.",
    "Tu intuición se tomó el día libre, ¿cierto?",
    "Te lanzaste con confianza. El resultado… ya es otra historia.",
    "No te preocupes, yo también me equivoco… bueno, casi nunca, pero igual.",
    "Error táctico. Pero hey, perder también es parte del plan… creo.",
    "¿Estrategia de desgaste? Me gusta, me gusta.",
    "No era esa, pero tranquilo, no hay puntos por llorar.",
    "¡Ups! ¿Querías probar qué se siente fallar? Ya lo sabes. Sigamos.",
    "¡Bravooo! Te equivocaste con estilo. Pocos logran eso.",
    "Y en el ranking de respuestas creativas… estás ganando.",
    "Con esa respuesta desbloqueaste el modo ‘no importa, seguimos’."
  ];

  const mensaje = frases[Math.floor(Math.random() * frases.length)];

  const overlay = document.createElement("div");
  overlay.id = "mensaje-error-overlay";
  Object.assign(overlay.style, {
    position: "fixed",
    top: "0",
    left: "0",
    width: "100%",
    height: "100%",
    backgroundColor: "rgba(0, 0, 0, 0.8)",
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    zIndex: "9999",
  });

  // Imagen flotante por fuera y encima del mensajeBox
  const imagenDecorativa = document.createElement("img");
  imagenDecorativa.src = "models/Stravi_hablando.png";
  Object.assign(imagenDecorativa.style, {
    position: "absolute",
    bottom: "calc(50% + 55px)", // ajusta la distancia exacta
    left: "50%",
    transform: "translateX(-50%)",
    width: "20%",
    height: "auto",
    zIndex: "10000",
    margin: "0",
    padding: "0"
  });

  const mensajeBox = document.createElement("div");
  Object.assign(mensajeBox.style, {
    backgroundColor: "#222",
    border: "4px solid #FFD700",
    borderRadius: "12px",
    padding: "20px",
    color: "#fff",
    maxWidth: "400px",
    textAlign: "center",
    fontFamily: "'Press Start 2P', cursive",
    fontSize: "10px",
    display: "flex",
    flexDirection: "column",
    gap: "10px",
    margin: "0"
  });

  mensajeBox.innerHTML = `
    <p style="margin: 0;">${mensaje}</p>
    <button id="cerrarMensajeError" style="
      font-family: 'Press Start 2P', cursive;
      background-color: #FFD700;
      border: none;
      padding: 10px;
      border-radius: 6px;
      cursor: pointer;
    ">Continuar Intentando...</button>
  `;

  overlay.appendChild(imagenDecorativa); // ← Imagen encima
  overlay.appendChild(mensajeBox);
  document.body.appendChild(overlay);

  document.getElementById("cerrarMensajeError").onclick = () => {
    document.body.removeChild(overlay);
    mostrarPregunta(posicionActual);
  };
}

// Inicializar
window.onload = () => {
  mostrarInicio();
};
