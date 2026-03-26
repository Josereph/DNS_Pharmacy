let scanner = null;
let scanning = true;

// Inicializar escáner al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    initScanner();
});

function initScanner() {
    scanner = new Instascan.Scanner({ video: document.getElementById('preview') });

    scanner.addListener('scan', function (content) {
        if (!scanning) return;
        scanning = false;

        // Extraer parámetros de la URL
        let params;
        try {
            const url = new URL(content);
            params = {
                token: url.searchParams.get('token'),
                uid: url.searchParams.get('uid')
            };
        } catch(e) {
            // Si no es URL válida, intentar parsear como query string
            const match = content.match(/[?&]token=([^&]+).*[?&]uid=([^&]+)/);
            if (match) {
                params = { token: match[1], uid: match[2] };
            } else {
                showResult('QR inválido. No contiene datos de usuario.', 'error');
                setTimeout(() => { scanning = true; }, 3000);
                return;
            }
        }

        if (!params.token || !params.uid) {
            showResult('QR inválido. No contiene datos de usuario.', 'error');
            setTimeout(() => { scanning = true; }, 3000);
            return;
        }

        // Enviar al servidor
        fetch('../controllers/AsistenciaController.php?accion=registrar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `token=${encodeURIComponent(params.token)}&uid=${encodeURIComponent(params.uid)}&origen=qr`
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                showResult(data.mensaje, 'success');
            } else {
                showResult(data.mensaje, 'error');
            }
            setTimeout(() => { scanning = true; }, 3000);
        })
        .catch(err => {
            console.error(err);
            showResult('Error al comunicarse con el servidor.', 'error');
            setTimeout(() => { scanning = true; }, 3000);
        });
    });

    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
            // Elegir la cámara trasera si existe (índice 1), si no la primera
            const camera = cameras.length > 1 ? cameras[1] : cameras[0];
            scanner.start(camera);
            document.getElementById('camera-status').innerHTML = '<i class="bi bi-camera-fill"></i> Cámara activa';
        } else {
            showResult('No se encontró cámara. Usa el método manual.', 'error');
        }
    }).catch(function (e) {
        console.error(e);
        showResult('Error al acceder a la cámara. Asegúrate de permitir el acceso.', 'error');
    });
}

function showResult(message, type) {
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = message;
    resultDiv.className = `scan-result ${type}`;
    resultDiv.style.display = 'block';
    setTimeout(() => {
        resultDiv.style.display = 'none';
    }, 3000);
}

// Método manual
function registrarManual() {
    const manualToken = document.getElementById('manualToken').value.trim();
    const manualUid = document.getElementById('manualUid').value.trim();
    if (!manualToken || !manualUid) {
        alert('Completa ambos campos: Token y ID de usuario');
        return;
    }

    fetch('../controllers/AsistenciaController.php?accion=registrar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `token=${encodeURIComponent(manualToken)}&uid=${encodeURIComponent(manualUid)}&origen=manual`
    })
    .then(res => res.json())
    .then(data => {
        showResult(data.mensaje, data.ok ? 'success' : 'error');
        if (data.ok) {
            document.getElementById('manualToken').value = '';
            document.getElementById('manualUid').value = '';
        }
    })
    .catch(err => {
        console.error(err);
        showResult('Error al registrar.', 'error');
    });
}