// Asistencia Scan con jsQR
let video = null;
let canvasElement = null;
let canvasCtx = null;
let placeholder = null;
let scanning = false;
let stream = null;
let animationId = null;

const dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
function pad(n) { return String(n).padStart(2,'0'); }
function actualizarReloj() {
    const now = new Date();
    const relojEl = document.getElementById('reloj');
    const fechaEl = document.getElementById('fecha');
    if (relojEl) relojEl.innerHTML = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    if (fechaEl) fechaEl.innerHTML = `${dias[now.getDay()]} ${now.getDate()} de ${meses[now.getMonth()]} de ${now.getFullYear()}`;
}

document.addEventListener('DOMContentLoaded', function() {
    canvasElement = document.getElementById('qr-canvas');
    if (!canvasElement) {
        console.error('No se encontró el canvas #qr-canvas');
        return;
    }
    canvasCtx = canvasElement.getContext('2d', { willReadFrequently: true });
    placeholder = document.getElementById('camera-placeholder');
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
    // Intentar encender cámara automáticamente
    encenderCamara();
});

function encenderCamara() {
    if (stream) cerrarCamara();
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(s) {
            stream = s;
            video = document.createElement('video');
            video.setAttribute('playsinline', true);
            video.srcObject = stream;
            video.play();
            placeholder.style.display = 'none';
            canvasElement.style.display = 'block';
            scanning = true;
            tick();
        })
        .catch(err => {
            console.error('Error cámara:', err);
            placeholder.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i><span>Error: no se pudo acceder a la cámara. Verifica permisos.</span>';
            placeholder.style.display = 'flex';
            canvasElement.style.display = 'none';
        });
}

function cerrarCamara() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    if (animationId) cancelAnimationFrame(animationId);
    scanning = false;
    canvasElement.style.display = 'none';
    placeholder.style.display = 'flex';
    placeholder.innerHTML = '<i class="bi bi-camera-video-off"></i><span>Cámara desactivada</span>';
    if (video) video = null;
}

function tick() {
    if (!scanning || !video || video.readyState !== video.HAVE_ENOUGH_DATA) {
        animationId = requestAnimationFrame(tick);
        return;
    }
    canvasElement.height = video.videoHeight;
    canvasElement.width  = video.videoWidth;
    canvasCtx.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
    scan();
    animationId = requestAnimationFrame(tick);
}

function scan() {
    if (!scanning) return;
    try {
        const imageData = canvasCtx.getImageData(0, 0, canvasElement.width, canvasElement.height);
        const code = jsQR(imageData.data, canvasElement.width, canvasElement.height);
        if (code) {
            scanning = false;
            procesarQR(code.data);
            setTimeout(() => { scanning = true; }, 3000);
        }
    } catch(e) {
        console.warn('Error en escaneo:', e);
    }
}

function procesarQR(qrData) {
    fetch('../controllers/AsistenciaController.php?accion=registrar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'qrData=' + encodeURIComponent(qrData)
    })
    .then(res => res.json())
    .then(res => {
        if (res.ok) {
            mostrarModal(res.tipo === 'entrada' ? 'entrada' : 'salida',
                          res.tipo === 'entrada' ? '✅' : '🚪',
                          res.tipo === 'entrada' ? 'ENTRADA REGISTRADA' : 'SALIDA REGISTRADA',
                          res.mensaje,
                          res.id_usuario);
        } else {
            mostrarModal('error', '❌', 'ERROR', res.mensaje, '');
        }
    })
    .catch(err => {
        console.error(err);
        mostrarModal('error', '💥', 'ERROR', 'Error de conexión', '');
    });
}

function registrarManual() {
    const uid = document.getElementById('manualUid').value.trim();
    const token = document.getElementById('manualToken').value.trim();
    if (!uid || !token) {
        alert('Completa ambos campos (ID y Token)');
        return;
    }
    const jsonData = JSON.stringify({ u: parseInt(uid), t: token });
    fetch('../controllers/AsistenciaController.php?accion=registrar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'qrData=' + encodeURIComponent(jsonData)
    })
    .then(res => res.json())
    .then(res => {
        if (res.ok) {
            mostrarModal(res.tipo === 'entrada' ? 'entrada' : 'salida',
                          res.tipo === 'entrada' ? '✅' : '🚪',
                          res.tipo === 'entrada' ? 'ENTRADA REGISTRADA' : 'SALIDA REGISTRADA',
                          res.mensaje,
                          res.id_usuario);
            document.getElementById('manualUid').value = '';
            document.getElementById('manualToken').value = '';
        } else {
            mostrarModal('error', '❌', 'ERROR', res.mensaje, '');
        }
    })
    .catch(err => {
        console.error(err);
        mostrarModal('error', '💥', 'ERROR', 'Error de conexión', '');
    });
}

let closeTimer = null;
function mostrarModal(tipo, icono, estado, mensaje, codigo) {
    clearTimeout(closeTimer);
    const overlay = document.getElementById('modal-overlay');
    const modal = document.getElementById('modal-box');
    modal.className = 'modal-box ' + tipo;
    document.getElementById('modalIcon').innerHTML = icono;
    document.getElementById('modalEstado').innerHTML = estado;
    document.getElementById('modalMensaje').innerHTML = mensaje;
    document.getElementById('modalCodigo').innerHTML = codigo ? "ID: " + codigo : '';
    overlay.classList.add('show');
    closeTimer = setTimeout(() => {
        overlay.classList.remove('show');
    }, 3000);
}

function cerrarModal() {
    document.getElementById('modal-overlay').classList.remove('show');
    clearTimeout(closeTimer);
}

window.encenderCamara = encenderCamara;
window.cerrarCamara = cerrarCamara;
window.registrarManual = registrarManual;
window.cerrarModal = cerrarModal;