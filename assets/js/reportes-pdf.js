function toggleCustomDates(select) {
    const card = select.closest('.reporte-card');
    const customBlock = card.querySelector('.custom-dates');

    if (select.value === 'custom') {
        customBlock.classList.remove('d-none');
    } else {
        customBlock.classList.add('d-none');
    }
}

function generarReporteDesdeCard(button) {
    const card = button.closest('.reporte-card');
    const tipo = card.dataset.tipo;
    const periodo = card.querySelector('.periodo-select').value;

    let url = `../controllers/GenerarReportePDF.php?tipo=${encodeURIComponent(tipo)}&periodo=${encodeURIComponent(periodo)}`;

    if (periodo === 'custom') {
        const desde = card.querySelector('.fecha-desde').value;
        const hasta = card.querySelector('.fecha-hasta').value;

        if (!desde || !hasta) {
            alert('Debe seleccionar ambas fechas.');
            return;
        }

        if (desde > hasta) {
            alert('La fecha desde no puede ser mayor que la fecha hasta.');
            return;
        }

        url += `&desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`;
    }

    window.open(url, '_blank');
}