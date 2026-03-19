/* =====================
   PRODUCTOS.JS - DNS Pharmacy
   Con conexión a BD via fetch
   ===================== */

const CONTROLLER = '/DNS_Pharmacy/controllers/ProductoController.php';

/* ── Modales ── */
function abrirModal(id) {
    document.getElementById(id).classList.add('activo');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('activo');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('activo');
            document.body.style.overflow = '';
        }
    });
});

/* ── Toast ── */
function mostrarToast(mensaje, tipo) {
    var toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = mensaje;
    toast.className   = 'toast toast-' + (tipo || 'ok');
    toast.classList.add('toast-visible');
    setTimeout(function() { toast.classList.remove('toast-visible'); }, 3500);
}

/* ══════════════════════════════════════════
   STATS
══════════════════════════════════════════ */
function cargarStats() {
    fetch(CONTROLLER + '?accion=stats')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            document.getElementById('statTotal').textContent     = res.datos.total;
            document.getElementById('statActivos').textContent   = res.datos.activos;
            document.getElementById('statStockBajo').textContent = res.datos.stock_bajo;
            document.getElementById('statInactivos').textContent = res.datos.inactivos;
        });
}

/* ══════════════════════════════════════════
   TABLA PRODUCTOS
══════════════════════════════════════════ */
function cargarProductos() {
    fetch(CONTROLLER + '?accion=listar')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            renderizarTabla(res.datos);
        });
}

function renderizarTabla(productos) {
    var tbody = document.getElementById('cuerpoTabla');

    if (!productos || productos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="tabla-vacia">No hay productos registrados.</td></tr>';
        actualizarContador(0, 0);
        return;
    }

    tbody.innerHTML = productos.map(function(p, i) {
        var img = p.imagen_url
            ? '<img src="/DNS_Pharmacy/' + p.imagen_url + '" class="tabla-img" alt="' + p.nombre + '">'
            : '<div class="tabla-img-placeholder"><i class="fas fa-pills"></i></div>';

        var stock     = parseInt(p.stock_actual);
        var minimo    = parseInt(p.stock_minimo);
        var stockHtml = stock <= minimo
            ? '<span class="stock-bajo">' + stock + '</span>'
            : '<span class="stock-ok">' + stock + '</span>';

        var estadoHtml = p.estado == 1
            ? '<span class="badge-activo">Activo</span>'
            : '<span class="badge-inactivo">Inactivo</span>';

        var recetaHtml = p.requiere_receta == 1
            ? '<span class="badge-receta">Sí</span>'
            : '<span class="badge-no">No</span>';

        var datosEditar = JSON.stringify({
            id: p.id_producto, nombre: p.nombre, codigo: p.codigo_barras,
            categoria: p.id_categoria, unidad: p.unidad_medida,
            descripcion: p.descripcion || '', presentacion: p.presentacion || '',
            marca: p.marca || '', laboratorio: p.laboratorio || '',
            precio_compra: p.precio_compra, precio_venta: p.precio_venta,
            stock_actual: p.stock_actual, stock_minimo: p.stock_minimo,
            receta: p.requiere_receta, estado: p.estado, imagen: p.imagen_url || ''
        }).replace(/'/g, "&#39;");

        return '<tr data-nombre="' + p.nombre.toLowerCase() + '"'
             + ' data-codigo="' + p.codigo_barras.toLowerCase() + '"'
             + ' data-categoria="' + p.nombre_categoria.toLowerCase() + '"'
             + ' data-estado="' + (p.estado == 1 ? 'activo' : 'inactivo') + '">'
             + '<td>' + (i+1) + '</td>'
             + '<td>' + img + '</td>'
             + '<td class="td-codigo">' + p.codigo_barras + '</td>'
             + '<td style="font-weight:500">' + p.nombre + '</td>'
             + '<td>' + p.nombre_categoria + '</td>'
             + '<td>$' + parseFloat(p.precio_compra).toFixed(2) + '</td>'
             + '<td class="td-precio-venta">$' + parseFloat(p.precio_venta).toFixed(2) + '</td>'
             + '<td>' + stockHtml + '</td>'
             + '<td>' + recetaHtml + '</td>'
             + '<td>' + estadoHtml + '</td>'
             + '<td>'
             + '<button class="btn-accion btn-editar" onclick=\'abrirModalProducto(' + datosEditar + ')\'>Editar</button>'
             + '<button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarProducto(' + p.id_producto + ',\'' + p.nombre.replace(/'/g,"&#39;") + '\')">Eliminar</button>'
             + '</td></tr>';
    }).join('');

    actualizarContador(productos.length, productos.length);
}

function actualizarContador(visible, total) {
    var cv = document.getElementById('contadorVisible');
    var ct = document.getElementById('contadorTotal');
    if (cv) cv.textContent = visible;
    if (ct) ct.textContent = total;
}

/* ── Filtros ── */
function filtrarTabla() {
    var buscar    = document.getElementById('buscador').value.toLowerCase();
    var categoria = document.getElementById('filtroCategoria').value.toLowerCase();
    var estado    = document.getElementById('filtroEstado').value.toLowerCase();
    var filas     = document.querySelectorAll('#cuerpoTabla tr[data-nombre]');
    var visible   = 0;

    filas.forEach(function(fila) {
        var ok = (!buscar    || fila.dataset.nombre.includes(buscar) || fila.dataset.codigo.includes(buscar))
              && (!categoria || fila.dataset.categoria.includes(categoria))
              && (!estado    || fila.dataset.estado === estado);
        fila.style.display = ok ? '' : 'none';
        if (ok) visible++;
    });

    actualizarContador(visible, filas.length);
}

/* ══════════════════════════════════════════
   MODAL PRODUCTO
══════════════════════════════════════════ */
function abrirModalProducto(datos) {
    limpiarFormProducto();
    cargarCategoriasEnSelect();

    if (datos) {
        document.getElementById('tituloModalProducto').textContent = 'Editar Producto';
        document.getElementById('prod_id').value            = datos.id;
        document.getElementById('prod_nombre').value        = datos.nombre;
        document.getElementById('prod_codigo').value        = datos.codigo;
        document.getElementById('prod_descripcion').value   = datos.descripcion;
        document.getElementById('prod_presentacion').value  = datos.presentacion;
        document.getElementById('prod_marca').value         = datos.marca;
        document.getElementById('prod_laboratorio').value   = datos.laboratorio;
        document.getElementById('prod_precio_compra').value = datos.precio_compra;
        document.getElementById('prod_precio_venta').value  = datos.precio_venta;
        document.getElementById('prod_stock_actual').value  = datos.stock_actual;
        document.getElementById('prod_stock_minimo').value  = datos.stock_minimo;
        document.getElementById('prod_receta').checked      = datos.receta == 1;
        document.getElementById('prod_estado').checked      = datos.estado == 1;
        document.getElementById('prod_imagen_actual').value = datos.imagen;

        setTimeout(function() {
            document.getElementById('prod_categoria').value = datos.categoria;
            document.getElementById('prod_unidad').value    = datos.unidad;
        }, 300);

        if (datos.imagen) {
            document.getElementById('img-preview-src').src            = '/DNS_Pharmacy/' + datos.imagen;
            document.getElementById('img-preview-nombre').textContent = datos.imagen.split('/').pop();
            document.getElementById('filePlaceholder').style.display  = 'none';
            document.getElementById('preview-imagen').style.display   = 'flex';
        }
    } else {
        document.getElementById('tituloModalProducto').textContent = 'Nuevo Producto';
    }
    abrirModal('modalProducto');
}

function limpiarFormProducto() {
    document.getElementById('formProducto').reset();
    document.getElementById('prod_id').value = '';
    document.getElementById('prod_estado').checked = true;
    quitarImagen();
    limpiarErroresProducto();
}

function limpiarErroresProducto() {
    ['nombre','codigo','categoria','unidad','precio_compra','precio_venta','stock_actual','stock_minimo'].forEach(function(c) {
        var err = document.getElementById('err_' + c);
        var inp = document.getElementById('prod_' + c);
        if (err) err.textContent = '';
        if (inp) inp.classList.remove('input-error');
    });
    document.getElementById('err_imagen').textContent = '';
}

function mostrarError(campo, mensaje) {
    var err = document.getElementById('err_' + campo);
    var inp = document.getElementById('prod_' + campo);
    if (err) err.textContent = mensaje;
    if (inp) inp.classList.add('input-error');
}

function validarFormProducto() {
    limpiarErroresProducto();
    var v = true;

    var nombre = document.getElementById('prod_nombre').value.trim();
    if (!nombre) { mostrarError('nombre','El nombre es obligatorio.'); v=false; }
    else if (nombre.length < 3) { mostrarError('nombre','Mínimo 3 caracteres.'); v=false; }

    if (!document.getElementById('prod_codigo').value.trim()) { mostrarError('codigo','Código de barras obligatorio.'); v=false; }
    if (!document.getElementById('prod_categoria').value)     { mostrarError('categoria','Selecciona una categoría.'); v=false; }
    if (!document.getElementById('prod_unidad').value)        { mostrarError('unidad','Selecciona la unidad.'); v=false; }

    var pc = parseFloat(document.getElementById('prod_precio_compra').value);
    var pv = parseFloat(document.getElementById('prod_precio_venta').value);

    if (document.getElementById('prod_precio_compra').value === '' || isNaN(pc)) { mostrarError('precio_compra','Precio de compra obligatorio.'); v=false; }
    else if (pc < 0) { mostrarError('precio_compra','No puede ser negativo.'); v=false; }

    if (document.getElementById('prod_precio_venta').value === '' || isNaN(pv)) { mostrarError('precio_venta','Precio de venta obligatorio.'); v=false; }
    else if (pv < 0) { mostrarError('precio_venta','No puede ser negativo.'); v=false; }
    else if (!isNaN(pc) && pv < pc) { mostrarError('precio_venta','No puede ser menor al precio de compra.'); v=false; }

    var sa = parseInt(document.getElementById('prod_stock_actual').value);
    var sm = parseInt(document.getElementById('prod_stock_minimo').value);

    if (document.getElementById('prod_stock_actual').value === '' || isNaN(sa)) { mostrarError('stock_actual','Stock actual obligatorio.'); v=false; }
    else if (sa < 0) { mostrarError('stock_actual','No puede ser negativo.'); v=false; }

    if (document.getElementById('prod_stock_minimo').value === '' || isNaN(sm)) { mostrarError('stock_minimo','Stock mínimo obligatorio.'); v=false; }
    else if (sm < 0) { mostrarError('stock_minimo','No puede ser negativo.'); v=false; }

    return v;
}

document.getElementById('formProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validarFormProducto()) return;

    var formData = new FormData(this);
    formData.append('accion', 'guardar');
    formData.set('requiere_receta', document.getElementById('prod_receta').checked ? '1' : '');
    formData.set('estado',          document.getElementById('prod_estado').checked  ? '1' : '');

    var btn = this.querySelector('.btn-guardar');
    btn.textContent = 'Guardando...';
    btn.disabled    = true;

    fetch(CONTROLLER, { method:'POST', body:formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            btn.textContent = 'Guardar producto';
            btn.disabled    = false;
            if (res.ok) {
                cerrarModal('modalProducto');
                mostrarToast(res.mensaje, 'ok');
                cargarProductos();
                cargarStats();
            } else {
                mostrarToast(res.mensaje, 'error');
            }
        })
        .catch(function() {
            btn.textContent = 'Guardar producto';
            btn.disabled    = false;
            mostrarToast('Error de conexión.', 'error');
        });
});

/* ── Eliminar producto ── */
var idProductoEliminar = null;

function confirmarEliminarProducto(id, nombre) {
    idProductoEliminar = id;
    document.getElementById('nombreEliminar').textContent = nombre;
    abrirModal('modalEliminar');
}

document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
    if (!idProductoEliminar) return;
    var fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id_producto', idProductoEliminar);
    fetch(CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            cerrarModal('modalEliminar');
            mostrarToast(res.mensaje, res.ok ? 'ok' : 'error');
            if (res.ok) { cargarProductos(); cargarStats(); }
            idProductoEliminar = null;
        });
});

/* ══════════════════════════════════════════
   IMAGEN
══════════════════════════════════════════ */
function previsualizarImagen(input) {
    var err = document.getElementById('err_imagen');
    err.textContent = '';
    if (!input.files || !input.files[0]) return;
    var archivo = input.files[0];
    if (!['image/jpeg','image/png','image/webp','image/gif'].includes(archivo.type)) {
        err.textContent = 'Solo JPG, PNG, WEBP o GIF.'; input.value=''; return;
    }
    if (archivo.size > 2*1024*1024) {
        err.textContent = 'Máximo 2MB.'; input.value=''; return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('img-preview-src').src            = e.target.result;
        document.getElementById('img-preview-nombre').textContent = archivo.name;
        document.getElementById('filePlaceholder').style.display  = 'none';
        document.getElementById('preview-imagen').style.display   = 'flex';
    };
    reader.readAsDataURL(archivo);
}

function quitarImagen() {
    var input = document.getElementById('prod_imagen');
    if (input) input.value = '';
    var src = document.getElementById('img-preview-src');
    if (src) src.src = '';
    var nombre = document.getElementById('img-preview-nombre');
    if (nombre) nombre.textContent = '';
    var actual = document.getElementById('prod_imagen_actual');
    if (actual) actual.value = '';
    var preview = document.getElementById('preview-imagen');
    if (preview) preview.style.display = 'none';
    var ph = document.getElementById('filePlaceholder');
    if (ph) ph.style.display = 'flex';
    var err = document.getElementById('err_imagen');
    if (err) err.textContent = '';
}

var uploadArea = document.getElementById('fileUploadArea');
if (uploadArea) {
    uploadArea.addEventListener('dragover', function(e) { e.preventDefault(); uploadArea.classList.add('drag-over'); });
    uploadArea.addEventListener('dragleave', function()  { uploadArea.classList.remove('drag-over'); });
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault(); uploadArea.classList.remove('drag-over');
        var input = document.getElementById('prod_imagen');
        input.files = e.dataTransfer.files;
        previsualizarImagen(input);
    });
}

/* ══════════════════════════════════════════
   CATEGORÍAS
══════════════════════════════════════════ */
function cargarCategoriasEnSelect() {
    fetch(CONTROLLER + '?accion=listar_categorias')
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.ok) return;
            var select = document.getElementById('prod_categoria');
            var actual = select.value;
            select.innerHTML = '<option value="">Seleccionar categoría</option>';
            res.datos.forEach(function(cat) {
                var opt = document.createElement('option');
                opt.value = cat.id_categoria;
                opt.textContent = cat.nombre;
                select.appendChild(opt);
            });
            if (actual) select.value = actual;
            actualizarFiltroCategorias(res.datos);
            renderizarTablaCategorias(res.datos);
        });
}

function actualizarFiltroCategorias(cats) {
    var filtro = document.getElementById('filtroCategoria');
    var actual = filtro.value;
    filtro.innerHTML = '<option value="">Todas las categorías</option>';
    cats.forEach(function(cat) {
        var opt = document.createElement('option');
        opt.value = cat.nombre;
        opt.textContent = cat.nombre;
        filtro.appendChild(opt);
    });
    if (actual) filtro.value = actual;
}

function renderizarTablaCategorias(cats) {
    var tbody = document.getElementById('cuerpoTablaCategoria');
    if (!cats || cats.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="tabla-vacia">No hay categorías.</td></tr>';
        return;
    }
    tbody.innerHTML = cats.map(function(cat) {
        return '<tr>'
             + '<td>' + cat.id_categoria + '</td>'
             + '<td>' + cat.nombre + '</td>'
             + '<td>' + (cat.descripcion || '—') + '</td>'
             + '<td>' + (cat.estado==1 ? '<span class="badge-activo">Activo</span>' : '<span class="badge-inactivo">Inactivo</span>') + '</td>'
             + '<td>'
             + '<button class="btn-accion btn-editar" onclick="editarCategoria(' + cat.id_categoria + ',\'' + cat.nombre.replace(/'/g,"&#39;") + '\',\'' + (cat.descripcion||'').replace(/'/g,"&#39;") + '\',' + cat.estado + ')">Editar</button>'
             + '<button class="btn-accion btn-eliminar-sm" onclick="confirmarEliminarCategoria(' + cat.id_categoria + ',\'' + cat.nombre.replace(/'/g,"&#39;") + '\')">Eliminar</button>'
             + '</td></tr>';
    }).join('');
}

function abrirModalCategorias() {
    limpiarFormCategoria();
    cargarCategoriasEnSelect();
    abrirModal('modalCategorias');
}

function limpiarFormCategoria() {
    document.getElementById('formCategoria').reset();
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_estado').checked = true;
    document.getElementById('err_cat_nombre').textContent = '';
    document.getElementById('cat_nombre').classList.remove('input-error');
    document.getElementById('btnGuardarCategoria').textContent    = 'Agregar';
    document.getElementById('btnCancelarCategoria').style.display = 'none';
}

function editarCategoria(id, nombre, descripcion, estado) {
    document.getElementById('cat_id').value          = id;
    document.getElementById('cat_nombre').value      = nombre;
    document.getElementById('cat_descripcion').value = descripcion;
    document.getElementById('cat_estado').checked    = estado == 1;
    document.getElementById('btnGuardarCategoria').textContent    = 'Actualizar';
    document.getElementById('btnCancelarCategoria').style.display = 'inline-block';
    document.getElementById('cat_nombre').focus();
}

document.getElementById('formCategoria').addEventListener('submit', function(e) {
    e.preventDefault();
    var nombre = document.getElementById('cat_nombre').value.trim();
    document.getElementById('err_cat_nombre').textContent = '';
    document.getElementById('cat_nombre').classList.remove('input-error');

    if (!nombre) {
        document.getElementById('err_cat_nombre').textContent = 'El nombre es obligatorio.';
        document.getElementById('cat_nombre').classList.add('input-error');
        return;
    }

    var fd = new FormData();
    fd.append('accion',       'guardar_categoria');
    fd.append('id_categoria', document.getElementById('cat_id').value);
    fd.append('nombre',       nombre);
    fd.append('descripcion',  document.getElementById('cat_descripcion').value.trim());
    if (document.getElementById('cat_estado').checked) fd.append('estado', '1');

    fetch(CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            mostrarToast(res.mensaje, res.ok ? 'ok' : 'error');
            if (res.ok) { limpiarFormCategoria(); cargarCategoriasEnSelect(); }
        });
});

var idCategoriaEliminar = null;

function confirmarEliminarCategoria(id, nombre) {
    idCategoriaEliminar = id;
    document.getElementById('nombreEliminarCat').textContent = nombre;
    abrirModal('modalEliminarCategoria');
}

document.getElementById('btnConfirmarEliminarCat').addEventListener('click', function() {
    if (!idCategoriaEliminar) return;
    var fd = new FormData();
    fd.append('accion',       'eliminar_categoria');
    fd.append('id_categoria', idCategoriaEliminar);
    fetch(CONTROLLER, { method:'POST', body:fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            cerrarModal('modalEliminarCategoria');
            mostrarToast(res.mensaje, res.ok ? 'ok' : 'error');
            if (res.ok) cargarCategoriasEnSelect();
            idCategoriaEliminar = null;
        });
});

/* ── Init ── */
document.addEventListener('DOMContentLoaded', function() {
    cargarProductos();
    cargarStats();
    cargarCategoriasEnSelect();
});