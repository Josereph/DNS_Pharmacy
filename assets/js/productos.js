/* =====================
   PRODUCTOS.JS - DNS Pharmacy
   ===================== */

/* ── Utilidades modal ── */
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

/* ══════════════════════════════════════════
   IMAGEN - Upload y previsualización
══════════════════════════════════════════ */

function previsualizarImagen(input) {
    var errImg = document.getElementById('err_imagen');
    errImg.textContent = '';

    if (!input.files || !input.files[0]) return;

    var archivo = input.files[0];
    var tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!tiposPermitidos.includes(archivo.type)) {
        errImg.textContent = 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.';
        input.value = '';
        return;
    }

    if (archivo.size > 2 * 1024 * 1024) {
        errImg.textContent = 'La imagen no debe superar los 2MB.';
        input.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('img-preview-src').src = e.target.result;
        document.getElementById('img-preview-nombre').textContent = archivo.name;
        document.getElementById('filePlaceholder').style.display = 'none';
        document.getElementById('preview-imagen').style.display = 'flex';
    };
    reader.readAsDataURL(archivo);
}

function quitarImagen() {
    document.getElementById('prod_imagen').value = '';
    document.getElementById('img-preview-src').src = '';
    document.getElementById('img-preview-nombre').textContent = '';
    document.getElementById('prod_imagen_actual').value = '';
    document.getElementById('preview-imagen').style.display = 'none';
    document.getElementById('filePlaceholder').style.display = 'flex';
    document.getElementById('err_imagen').textContent = '';
}

/* Drag and drop */
var uploadArea = document.getElementById('fileUploadArea');
if (uploadArea) {
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function() {
        uploadArea.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        var input = document.getElementById('prod_imagen');
        input.files = e.dataTransfer.files;
        previsualizarImagen(input);
    });
}

/* ══════════════════════════════════════════
   PRODUCTOS
══════════════════════════════════════════ */

function abrirModalProducto(datos) {
    limpiarFormProducto();
    if (datos) {
        document.getElementById('tituloModalProducto').textContent = 'Editar Producto';
        document.getElementById('prod_id').value             = datos.id;
        document.getElementById('prod_nombre').value         = datos.nombre;
        document.getElementById('prod_codigo').value         = datos.codigo;
        document.getElementById('prod_categoria').value      = datos.categoria;
        document.getElementById('prod_unidad').value         = datos.unidad;
        document.getElementById('prod_descripcion').value    = datos.descripcion || '';
        document.getElementById('prod_presentacion').value   = datos.presentacion || '';
        document.getElementById('prod_marca').value          = datos.marca || '';
        document.getElementById('prod_laboratorio').value    = datos.laboratorio || '';
        document.getElementById('prod_concentracion').value  = datos.concentracion || '';
        document.getElementById('prod_precio_compra').value  = datos.precio_compra;
        document.getElementById('prod_precio_venta').value   = datos.precio_venta;
        document.getElementById('prod_stock_actual').value   = datos.stock_actual;
        document.getElementById('prod_stock_minimo').value   = datos.stock_minimo;
        document.getElementById('prod_receta').checked       = datos.receta == 1;
        document.getElementById('prod_estado').checked       = datos.estado == 1;

        // Si tiene imagen guardada, mostrarla en el preview
        if (datos.imagen) {
            document.getElementById('prod_imagen_actual').value = datos.imagen;
            document.getElementById('img-preview-src').src = '../' + datos.imagen;
            document.getElementById('img-preview-nombre').textContent = datos.imagen.split('/').pop();
            document.getElementById('filePlaceholder').style.display = 'none';
            document.getElementById('preview-imagen').style.display = 'flex';
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
    var campos = ['nombre', 'codigo', 'categoria', 'unidad', 'precio_compra', 'precio_venta', 'stock_actual', 'stock_minimo'];
    campos.forEach(function(c) {
        var err = document.getElementById('err_' + c);
        if (err) err.textContent = '';
        var input = document.getElementById('prod_' + c);
        if (input) input.classList.remove('input-error');
    });
    document.getElementById('err_imagen').textContent = '';
}

function mostrarError(campo, mensaje) {
    var err = document.getElementById('err_' + campo);
    var input = document.getElementById('prod_' + campo);
    if (err) err.textContent = mensaje;
    if (input) input.classList.add('input-error');
}

function validarFormProducto() {
    limpiarErroresProducto();
    var valido = true;

    var nombre = document.getElementById('prod_nombre').value.trim();
    if (!nombre) {
        mostrarError('nombre', 'El nombre del producto es obligatorio.');
        valido = false;
    } else if (nombre.length < 3) {
        mostrarError('nombre', 'El nombre debe tener al menos 3 caracteres.');
        valido = false;
    }

    var codigo = document.getElementById('prod_codigo').value.trim();
    if (!codigo) {
        mostrarError('codigo', 'El código de barras es obligatorio.');
        valido = false;
    } else if (!/^[a-zA-Z0-9\-]+$/.test(codigo)) {
        mostrarError('codigo', 'Solo se permiten letras, números y guiones.');
        valido = false;
    }

    var categoria = document.getElementById('prod_categoria').value;
    if (!categoria) {
        mostrarError('categoria', 'Selecciona una categoría.');
        valido = false;
    }

    var unidad = document.getElementById('prod_unidad').value;
    if (!unidad) {
        mostrarError('unidad', 'Selecciona la unidad de medida.');
        valido = false;
    }

    var precioCompra = parseFloat(document.getElementById('prod_precio_compra').value);
    if (document.getElementById('prod_precio_compra').value === '' || isNaN(precioCompra)) {
        mostrarError('precio_compra', 'Ingresa el precio de compra.');
        valido = false;
    } else if (precioCompra < 0) {
        mostrarError('precio_compra', 'El precio no puede ser negativo.');
        valido = false;
    }

    var precioVenta = parseFloat(document.getElementById('prod_precio_venta').value);
    if (document.getElementById('prod_precio_venta').value === '' || isNaN(precioVenta)) {
        mostrarError('precio_venta', 'Ingresa el precio de venta.');
        valido = false;
    } else if (precioVenta < 0) {
        mostrarError('precio_venta', 'El precio no puede ser negativo.');
        valido = false;
    } else if (!isNaN(precioCompra) && precioVenta < precioCompra) {
        mostrarError('precio_venta', 'El precio de venta no puede ser menor al de compra.');
        valido = false;
    }

    var stockActual = parseInt(document.getElementById('prod_stock_actual').value);
    if (document.getElementById('prod_stock_actual').value === '' || isNaN(stockActual)) {
        mostrarError('stock_actual', 'Ingresa el stock actual.');
        valido = false;
    } else if (stockActual < 0) {
        mostrarError('stock_actual', 'El stock no puede ser negativo.');
        valido = false;
    }

    var stockMinimo = parseInt(document.getElementById('prod_stock_minimo').value);
    if (document.getElementById('prod_stock_minimo').value === '' || isNaN(stockMinimo)) {
        mostrarError('stock_minimo', 'Ingresa el stock mínimo.');
        valido = false;
    } else if (stockMinimo < 0) {
        mostrarError('stock_minimo', 'El stock mínimo no puede ser negativo.');
        valido = false;
    }

    return valido;
}

document.getElementById('formProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validarFormProducto()) return;
    // Aquí irá la llamada al controlador PHP con fetch/AJAX
    cerrarModal('modalProducto');
    alert('Producto guardado. (Conectar con PHP)');
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
    // Aquí irá la llamada al controlador PHP
    cerrarModal('modalEliminar');
    alert('Producto eliminado. (Conectar con PHP)');
    idProductoEliminar = null;
});


/* ── Filtrar tabla ── */
function filtrarTabla() {
    var buscar    = document.getElementById('buscador').value.toLowerCase();
    var categoria = document.getElementById('filtroCategoria').value.toLowerCase();
    var estado    = document.getElementById('filtroEstado').value.toLowerCase();
    var filas     = document.querySelectorAll('#cuerpoTabla tr');

    filas.forEach(function(fila) {
        if (fila.querySelector('.tabla-vacia')) return;
        var nombre = (fila.cells[3] ? fila.cells[3].textContent.toLowerCase() : '');
        var codigo = (fila.cells[2] ? fila.cells[2].textContent.toLowerCase() : '');
        var cat    = (fila.cells[4] ? fila.cells[4].textContent.toLowerCase() : '');
        var est    = (fila.cells[9] ? fila.cells[9].textContent.toLowerCase() : '');

        var ok = (!buscar    || nombre.includes(buscar) || codigo.includes(buscar))
              && (!categoria || cat.includes(categoria))
              && (!estado    || est.includes(estado));

        fila.style.display = ok ? '' : 'none';
    });
}


/* ══════════════════════════════════════════
   CATEGORÍAS
══════════════════════════════════════════ */

function abrirModalCategorias() {
    limpiarFormCategoria();
    abrirModal('modalCategorias');
}

function limpiarFormCategoria() {
    document.getElementById('formCategoria').reset();
    document.getElementById('cat_id').value = '';
    document.getElementById('cat_estado').checked = true;
    document.getElementById('err_cat_nombre').textContent = '';
    document.getElementById('cat_nombre').classList.remove('input-error');
    document.getElementById('btnGuardarCategoria').textContent = 'Agregar';
    document.getElementById('btnCancelarCategoria').style.display = 'none';
}

function editarCategoria(id, nombre, descripcion, estado) {
    document.getElementById('cat_id').value          = id;
    document.getElementById('cat_nombre').value      = nombre;
    document.getElementById('cat_descripcion').value = descripcion || '';
    document.getElementById('cat_estado').checked    = estado == 1;
    document.getElementById('btnGuardarCategoria').textContent = 'Actualizar';
    document.getElementById('btnCancelarCategoria').style.display = 'inline-block';
    document.getElementById('cat_nombre').focus();
}

function validarFormCategoria() {
    var nombre = document.getElementById('cat_nombre').value.trim();
    document.getElementById('err_cat_nombre').textContent = '';
    document.getElementById('cat_nombre').classList.remove('input-error');

    if (!nombre) {
        document.getElementById('err_cat_nombre').textContent = 'El nombre es obligatorio.';
        document.getElementById('cat_nombre').classList.add('input-error');
        return false;
    } else if (nombre.length < 2) {
        document.getElementById('err_cat_nombre').textContent = 'Mínimo 2 caracteres.';
        document.getElementById('cat_nombre').classList.add('input-error');
        return false;
    }
    return true;
}

document.getElementById('formCategoria').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!validarFormCategoria()) return;
    // Aquí irá la llamada al controlador PHP
    alert('Categoría guardada. (Conectar con PHP)');
    limpiarFormCategoria();
});


/* ── Eliminar categoría ── */
var idCategoriaEliminar = null;

function confirmarEliminarCategoria(id, nombre) {
    idCategoriaEliminar = id;
    document.getElementById('nombreEliminarCat').textContent = nombre;
    abrirModal('modalEliminarCategoria');
}

document.getElementById('btnConfirmarEliminarCat').addEventListener('click', function() {
    if (!idCategoriaEliminar) return;
    // Aquí irá la llamada al controlador PHP
    cerrarModal('modalEliminarCategoria');
    alert('Categoría eliminada. (Conectar con PHP)');
    idCategoriaEliminar = null;
});