// Script to display logged-in teacher's payments

document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("seccionPagos")) {
        cargarPagosProfesor();
    }
});

function cargarPagosProfesor() {
    fetch("php/profesor/obtener_pagos_profesor.php")
        .then(response => response.json())
        .then(data => {
            mostrarClasesCompletadas(data.completadas);
            mostrarPagosRegistrados(data.registrados);
        })
        .catch(error => {
            console.error("Error loading teacher payments:", error);
        });
}

function mostrarClasesCompletadas(clases) {
    const contenedor = document.getElementById("contenedorClasesCompletadas");
    contenedor.innerHTML = "";

    if (!Array.isArray(clases) || clases.length === 0 || !clases[0]?.total_horas) {
        contenedor.innerHTML = `<p class="text-muted">You have no completed classes pending payment.</p>`;
        return;
    }

    const clase = clases[0];
    contenedor.innerHTML = `
        <div class="card shadow-sm rounded-3 border-0 p-3">
            <div class="d-flex align-items-center gap-2 text-primary mb-2">
                <i class="fas fa-hourglass-half"></i>
                <span>Pending Payment</span>
            </div>
            <p class="mb-1">Total Hours: <strong>${clase.total_horas}</strong></p>
            <p class="mb-0">Total to Collect: <strong>€${clase.total}</strong></p>
        </div>
    `;
}

function mostrarPagosRegistrados(pagos) {
    const tabla = document.getElementById("tablaPagosRegistrados");
    const cuerpo = tabla.querySelector("tbody");
    cuerpo.innerHTML = "";

    if (!pagos || pagos.length === 0) {
        cuerpo.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No registered payments found.</td></tr>`;
        return;
    }

    pagos.forEach(pago => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${formatearFecha(pago.fecha_pago)}</td>         
            <td>${pago.total_horas}</td>
            <td>€${pago.total}</td>
            <td>
                <button class="btn btn-sm btn-primary ver-detalle-pago" data-id="${pago.id}">
                    View Details
                </button>
            </td>
        `;
        cuerpo.appendChild(fila);
    });
    
    // Ahora asigna el click a los botones, NO a la fila entera
    cuerpo.querySelectorAll('.ver-detalle-pago').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.stopPropagation(); // Evita que el click se propague si hay otro listener en la fila
            const idPago = this.getAttribute('data-id');
            verDetallePagoProfesor(idPago);
        });
    });

    // Initialize DataTables
    if ($.fn.DataTable.isDataTable('#tablaPagosRegistrados')) {
        $('#tablaPagosRegistrados').DataTable().destroy();
    }

    const tablaPagosProfesor = $('#tablaPagosRegistrados').DataTable({
        dom: 'rtp',
        pageLength: 10,
        lengthChange: false,
        order: [[0, 'desc']], // Order by date column (index 0)
        columnDefs: [
            { type: 'fecha-euro', targets: 0 }
        ],
        searching: false,
        info: false,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/en-GB.json"
        }
    });
    $('#buscadorPagosProfesor').on('keyup', function () {
        tablaPagosProfesor.search(this.value).draw();
    });
    tablaPagosProfesor.on('draw', function () {
        const info = tablaPagosProfesor.page.info();
        document.getElementById('infoPagosProfesor').textContent =
            `Showing ${info.end} payments of ${info.recordsTotal} recorded`;
    });
}


function formatearFecha(fechaISO) {
    const [anio, mes, dia] = fechaISO.split("-");
    return `${dia}-${mes}-${anio}`;
}

document.getElementById("btnDescargarComprobanteProfesor").addEventListener("click", function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const fechaPago = document.querySelector("#tablaPagosRegistrados tbody tr td")?.textContent.trim() || "";
    const filas = document.querySelectorAll("#tablaDetallePagoProfesor tr");

    let totalHoras = 0;
    let totalEuros = 0;

    const clases = [];
    filas.forEach(row => {
        const celdas = row.querySelectorAll("td");
        if (celdas.length === 5) {
            const fecha = celdas[0].textContent.trim();
            const alumno = celdas[1].textContent.trim();
            const duracion = celdas[2].textContent.trim();
            const tarifa = celdas[3].textContent.trim();
            const importe = celdas[4].textContent.trim();

            // Sum totals
            const duracionNum = parseFloat(duracion.replace("hrs", "").trim().replace(",", "."));
            const importeNum = parseFloat(importe.replace("€", "").replace(",", "."));

            if (!isNaN(duracionNum)) totalHoras += duracionNum;
            if (!isNaN(importeNum)) totalEuros += importeNum;

            clases.push([fecha, alumno, duracion, tarifa, importe]);
        }
    });

    const fechaHoyNombre = new Date().toISOString().split("T")[0].split("-").reverse().join("-");

    doc.setFontSize(16);
    doc.text(`Payment Receipt`, 20, 20);

    doc.setFontSize(12);
    doc.text(`Paid Hours: ${totalHoras.toFixed(2)} hrs`, 20, 48);
    doc.text(`Total: €${totalEuros.toFixed(2)}`, 20, 56);
    doc.text(`Payment Date: ${fechaPago}`, 20, 64);

    doc.autoTable({
        startY: 75,
        head: [["Date", "Participant", "Duration", "Hourly Rate", "Amount"]],
        body: clases
    });

    const finalY = doc.lastAutoTable.finalY || 85;
    doc.setFontSize(10);
    doc.text("Thank you for your work." + NOMBRE_ESCUELA, 20, finalY + 20);

    doc.save(`teacher_payment_receipt_${fechaHoyNombre}.pdf`);
});

function verDetallePagoProfesor(idPago) {
    const idProfesor = localStorage.getItem("id_profesor");

    fetch(`php/profesor/obtener_detalle_pago_profesor.php?id=${idPago}&profesor_id=${idProfesor}`)
        .then(response => response.json())
        .then(data => {
            const cuerpo = document.getElementById("tablaDetallePagoProfesor");
            cuerpo.innerHTML = "";

            if (!data || data.length === 0) {
                cuerpo.innerHTML = `<tr><td colspan="5" class="text-center">No classes associated.</td></tr>`;
            } else {
                data.forEach(clase => {
                    const fechaFormateada = formatearFecha(clase.fecha);
                    const fila = `
                        <tr>
                            <td>${fechaFormateada}</td>
                            <td>${clase.alumno_nombre}</td>
                            <td>${clase.duracion} hrs</td>
                            <td>€${parseFloat(clase.tarifa_hora).toFixed(2)}</td>
                            <td>€${parseFloat(clase.importe).toFixed(2)}</td>
                        </tr>
                    `;
                    cuerpo.innerHTML += fila;
                });
            }

            // IMPORTANTE: Inicializa DataTable solo después de poblar la tabla
            // Primero destruye si ya existe
            if ($.fn.DataTable.isDataTable('#tablaDetallePagoProfesorTable')) {
                $('#tablaDetallePagoProfesorTable').DataTable().destroy();
            }
            $('#tablaDetallePagoProfesorTable').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: false,
                ordering: false,
                info: false,
                language: {
                    paginate: {
                        next: 'Next',
                        previous: 'Previous'
                    },
                    emptyTable: 'No payment details available'
                }
            });

            const modal = new bootstrap.Modal(document.getElementById("modalDetallePagoProfesor"));
            modal.show();
        })
        .catch(error => {
            console.error("Error loading payment details:", error);
        });
}

// Limpia DataTable al cerrar el modal
$('#modalDetallePagoProfesor').on('hidden.bs.modal', function () {
    if ($.fn.DataTable.isDataTable('#tablaDetallePagoProfesorTable')) {
        $('#tablaDetallePagoProfesorTable').DataTable().destroy();
    }
});
