document.addEventListener("DOMContentLoaded", () => {
    cargarFacturacionMensual();
    inicializarFiltro();
    inicializarExportadores();
});

function cargarFacturacionMensual() {
    fetch('php/facturacion_controller.php')
        .then(response => validarRespuesta(response))
        .then(data => renderizarResumenMensual(data))
        .catch(error => mostrarError("There was a problem loading the billing data.", error));
}

function verDetalleMes(anio, mes) {
    const mesNombre = new Intl.DateTimeFormat('en', { month: 'long' }).format(new Date(anio, mes - 1));
    document.getElementById("tituloDetalleFacturacion").innerText = `Billing Details – ${mesNombre} ${anio}`;

    fetch(`php/detalle_facturacion_controller.php?anio=${anio}&mes=${mes}`)
        .then(response => validarRespuesta(response))
        .then(data => renderizarDetalleMensual(data))
        .then(() => new bootstrap.Modal(document.getElementById("modalDetalleFacturacion")).show())
        .catch(error => mostrarError("There was a problem loading the billing details.", error));
}

function validarRespuesta(res) {
    if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
    return res.json();
}

function mostrarError(mensaje, error) {
    console.error(mensaje, error);
    alert(mensaje);
}

function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    return `${String(fecha.getDate()).padStart(2, '0')}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${fecha.getFullYear()}`;
}

function capitalizar(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function renderizarResumenMensual(data) {
    const tbody = document.getElementById("tbodyFacturacion");
    tbody.innerHTML = "";

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center">There is no billing data yet.</td></tr>';
        return;
    }

    // 🕒 Sort from most recent to oldest
    data.sort((a, b) => {
        if (a.anio !== b.anio) return b.anio - a.anio;
        return b.mes - a.mes;
    });

    data.forEach(item => {
        const mesNombreCompleto = `${new Intl.DateTimeFormat('en', { month: 'long' }).format(new Date(item.anio, item.mes - 1))} ${item.anio}`;

        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${mesNombreCompleto}</td>
            <td>€ ${item.total_facturado ? parseFloat(item.total_facturado).toFixed(2) : '0.00'}</td>
            <td><button class="btn btn-primary btn-sm" onclick="verDetalleMes(${item.anio}, ${item.mes})">🔍 View</button></td>
        `;
        tbody.appendChild(fila);
    });
    inicializarPaginacionResumenMensual();
}

function renderizarDetalleMensual(data) {
    const tbody = document.getElementById("tbodyDetalleMes");
    tbody.innerHTML = "";

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">No completed classes this month.</td></tr>';
        return;
    }

    let totalCash = 0;
    let totalCard = 0;
    let totalPaid = 0;
    let totalInstructors = 0;

    data.forEach(clase => {
        const cash = parseFloat(clase.pago_efectivo || 0);
        const card = parseFloat(clase.pago_tarjeta || 0);
        const classTotal = cash + card;
        const instructorAmount = parseFloat(clase.importe_profesor || 0);

        totalCash += cash;
        totalCard += card;
        totalPaid += classTotal;
        totalInstructors += instructorAmount;

        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${formatearFecha(clase.fecha_clase)}</td>
            <td>${clase.profesor}</td>
            <td>${clase.alumno}</td>
            <td>€ ${instructorAmount.toFixed(2)}</td>
            <td>€ ${cash.toFixed(2)}</td>
            <td>€ ${card.toFixed(2)}</td>
            <td>€ ${classTotal.toFixed(2)}</td>
        `;
        tbody.appendChild(fila);
    });

    document.getElementById("totalEfectivoMes").innerText = totalCash.toFixed(2);
    document.getElementById("totalTarjetaMes").innerText = totalCard.toFixed(2);
    document.getElementById("totalFacturadoMes").innerText = totalPaid.toFixed(2);
    document.getElementById("totalProfesoresMes").innerText = totalInstructors.toFixed(2);
    document.getElementById("gananciaMes").innerText = (totalPaid - totalInstructors).toFixed(2);

    // 🧹 Destroy DataTable if already initialized
    if ($.fn.DataTable.isDataTable('#tablaDetalleMes')) {
        $('#tablaDetalleMes').DataTable().destroy();
    }

    // ✅ Initialize DataTable with DESC order by date
    $('#tablaDetalleMes').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        order: [[0, 'desc']], // 🔽 Column 0 (Date), descending order
        columnDefs: [
            { type: 'fecha-euro', targets: 0 }
        ],
        searching: false,
        language: {
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            emptyTable: "No data available in the table"
        },
        infoCallback: function (settings, start, end, max, total, pre) {
            return `Showing ${end} of ${total} registered classes`;
        }
    });
}


function inicializarFiltro() {
    const input = document.getElementById("filtroDetalle");
    if (!input) return;

    input.addEventListener("input", () => {
        const filtro = input.value.toLowerCase();
        const filas = document.querySelectorAll("#tablaDetalleMes tbody tr");

        filas.forEach(fila => {
            const texto = fila.innerText.toLowerCase();
            fila.style.display = texto.includes(filtro) ? "" : "none";
        });
    });
}

function inicializarExportadores() {
    const tabla = document.getElementById("tablaDetalleMes");

    document.getElementById("btnExportExcel")?.addEventListener("click", () => {
        const ws = XLSX.utils.table_to_sheet(tabla);
        const lastRow = XLSX.utils.decode_range(ws['!ref']).e.r + 2;

        XLSX.utils.sheet_add_aoa(ws, [
            ["", "", "Financial Summary"],
            ["Total Cash", `€ ${document.getElementById("totalEfectivoMes").innerText}`],
            ["Total by Card", `€ ${document.getElementById("totalTarjetaMes").innerText}`],
            ["Total Billed", `€ ${document.getElementById("totalFacturadoMes").innerText}`],
            ["Amount to Instructors", `€ ${document.getElementById("totalProfesoresMes").innerText}`],
            ["Net Profit", `€ ${document.getElementById("gananciaMes").innerText}`],
        ], { origin: `A${lastRow + 1}` });

        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Details");
        const fechaActual = new Date();
        const dia = String(fechaActual.getDate()).padStart(2, '0');
        const mesNum = fechaActual.getMonth();
        const anio = fechaActual.getFullYear();
        const fechaGenerada = `${dia}-${mesNum + 1}-${anio}`;

        const titulo = document.getElementById("tituloDetalleFacturacion").innerText;
        const [_, mesNombre, anioFact] = titulo.match(/– (\w+) (\d{4})/) || [];

        const nombreArchivo = `billing_${mesNombre?.toLowerCase() ?? 'month'}_${anioFact ?? 'year'}_generated_${fechaGenerada}.xlsx`;

        XLSX.writeFile(wb, nombreArchivo);
    });

    document.getElementById("btnExportPDF")?.addEventListener("click", () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text("Billing Details", 14, 15);
        doc.autoTable({
            html: '#tablaDetalleMes',
            startY: 20,
            styles: { fontSize: 9 }
        });

        const total = document.getElementById("totalFacturadoMes").innerText;
        const efectivo = document.getElementById("totalEfectivoMes").innerText;
        const tarjeta = document.getElementById("totalTarjetaMes").innerText;
        const profesores = document.getElementById("totalProfesoresMes").innerText;
        const ganancia = document.getElementById("gananciaMes").innerText;

        doc.autoTable({
            startY: doc.lastAutoTable.finalY + 10,
            body: [
                ['Total Cash', `€ ${efectivo}`],
                ['Total by Card', `€ ${tarjeta}`],
                ['Total Billed', `€ ${total}`],
                ['Amount to Instructors', `€ ${profesores}`],
                ['Net Profit', `€ ${ganancia}`],
            ],
            theme: 'plain',
            styles: { fontSize: 11, textColor: [0, 0, 0] },
        });

        const fechaActual = new Date();
        const dia = String(fechaActual.getDate()).padStart(2, '0');
        const mesNum = fechaActual.getMonth();
        const anio = fechaActual.getFullYear();
        const fechaGenerada = `${dia}-${mesNum + 1}-${anio}`;

        const titulo = document.getElementById("tituloDetalleFacturacion").innerText;
        const [_, mesNombre, anioFact] = titulo.match(/– (\w+) (\d{4})/) || [];

        const nombreArchivo = `billing_${mesNombre?.toLowerCase() ?? 'month'}_${anioFact ?? 'year'}_generated_${fechaGenerada}.pdf`;

        doc.save(nombreArchivo);
    });
}

function inicializarPaginacionResumenMensual() {
    if ($.fn.DataTable.isDataTable('#tablaFacturacion')) {
        $('#tablaFacturacion').DataTable().destroy();
    }

    $('#tablaFacturacion').DataTable({
        pageLength: 12,
        lengthChange: false,
        ordering: false,
        searching: true,
        language: {
            search: "Search by month or year:",
            paginate: {
                previous: "Previous",
                next: "Next"
            },
            emptyTable: "No billing data available"
        },
        infoCallback: function (settings, start, end, max, total, pre) {
            return `Showing ${end} months of ${total} registered`;
        }
    });
}
