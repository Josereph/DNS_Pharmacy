const API = '../controllers/RecuperarController.php';

/* ── Paso 1: verificar correo ── */
document.getElementById('formPaso1').addEventListener('submit', function(e) {
    e.preventDefault();
    limpiar('errCorreo', 'wrapCorreo', 'alertaPaso1');

    const correo = document.getElementById('correo_recuperar').value.trim();

    if (!correo) {
        mostrarError('errCorreo', 'wrapCorreo', 'El correo es obligatorio.');
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
        mostrarError('errCorreo', 'wrapCorreo', 'Ingresa un correo válido.');
        return;
    }

    const fd = new FormData();
    fd.append('action', 'verificar');
    fd.append('correo', correo);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                mostrarAlerta('alertaPaso1', data.mensaje, 'danger');
                return;
            }
            document.getElementById('correo_confirmado').value = correo;
            document.getElementById('correoVerificado').textContent = correo;
            document.getElementById('paso1').style.display = 'none';
            document.getElementById('paso2').style.display = 'block';
        })
        .catch(() => mostrarAlerta('alertaPaso1', 'Error de conexión.', 'danger'));
});

/* ── Paso 2: nueva contraseña ── */
document.getElementById('formPaso2').addEventListener('submit', function(e) {
    e.preventDefault();
    limpiar('errNueva', 'wrapNueva', 'alertaPaso2');
    limpiar('errConfirmar', 'wrapConfirmar', 'alertaPaso2');

    const correo    = document.getElementById('correo_confirmado').value;
    const nueva     = document.getElementById('nueva_pass').value;
    const confirmar = document.getElementById('confirmar_pass').value;

    let ok = true;

    if (!nueva || nueva.length < 8) {
        mostrarError('errNueva', 'wrapNueva', 'Mínimo 8 caracteres.');
        ok = false;
    }

    if (nueva !== confirmar) {
        mostrarError('errConfirmar', 'wrapConfirmar', 'Las contraseñas no coinciden.');
        ok = false;
    }

    if (!ok) return;

    const fd = new FormData();
    fd.append('action',   'actualizar');
    fd.append('correo',   correo);
    fd.append('password', nueva);

    fetch(API, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                mostrarAlerta('alertaPaso2', data.mensaje, 'danger');
                return;
            }
            document.getElementById('paso2').style.display = 'none';
            document.getElementById('paso3').style.display = 'block';
        })
        .catch(() => mostrarAlerta('alertaPaso2', 'Error de conexión.', 'danger'));
});

/* ── Volver al paso 1 ── */
function volverPaso1() {
    document.getElementById('paso2').style.display = 'none';
    document.getElementById('paso1').style.display = 'block';
    document.getElementById('correo_recuperar').value = '';
    limpiar('errCorreo', 'wrapCorreo', 'alertaPaso1');
}

/* ── Toggle contraseña ── */
function togglePass(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Ocultar';
    } else {
        input.type = 'password';
        btn.textContent = 'Ver';
    }
}

/* ── Helpers ── */
function mostrarError(errId, wrapId, msg) {
    document.getElementById(errId).textContent = msg;
    document.getElementById(wrapId).classList.add('input-error');
}

function mostrarAlerta(alertId, msg, tipo) {
    const el = document.getElementById(alertId);
    el.textContent  = msg;
    el.className    = `alert alert-${tipo}`;
    el.style.display = 'block';
}

function limpiar(errId, wrapId, alertId) {
    document.getElementById(errId).textContent = '';
    document.getElementById(wrapId).classList.remove('input-error');
    const al = document.getElementById(alertId);
    if (al) al.style.display = 'none';
}