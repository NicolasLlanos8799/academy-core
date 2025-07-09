// =========================================================
// File: admin_pagos.js
// Purpose: Display completed classes grouped by instructor,
// register payments, and list registered payments with detail via modal.
// =========================================================

function cargarPagos() {
    const contenedor = document.getElementById("seccionPagos") || document.getElementById("pagos");

    const loader = document.createElement("div");
    loader.id = "pagosLoader";
    loader.className = "text-center my-4";
    loader.innerHTML = `<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading payments...</span></div>`;

    // Loader con delay: solo se muestra si pasan 300 ms
    let loaderTimeout = setTimeout(() => {
        contenedor?.parentNode.insertBefore(loader, contenedor);
    }, 300);

    fetch("php/listar_pagos.php")
        .then(response => response.json())
        .then(data => {
            const cuerpoTablaCompletadas = document.getElementById("cuerpoTablaPagosPendientes");
            const cuerpoTablaRegistrados = document.getElementById("cuerpoTablaPagosRealizados");

            if ($.fn.DataTable.isDataTable('#tablaPagosRealizados')) {
                $('#tablaPagosRealizados').DataTable().destroy();
            }
            if ($.fn.DataTable.isDataTable('#tablaPagosPendientes')) {
                $('#tablaPagosPendientes').DataTable().destroy();
            }

            cuerpoTablaCompletadas.innerHTML = "";
            cuerpoTablaRegistrados.innerHTML = "";

            data.completadas.forEach(pago => {
                cuerpoTablaCompletadas.innerHTML += `
                    <tr class="border-bottom border-light">
                        <td><i class="bi bi-person me-1"></i>${pago.profesor_nombre}</td>
                        <td class="text-end">${pago.total_horas}</td>
                        <td class="text-end"><i class="bi bi-cash-coin me-1"></i>€${pago.total}</td>
                        <td class="text-center">
                            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" onclick="verDetalleClasesPendientes(${pago.profesor_id})" title="View payment details">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>
                `;
            });

            data.registrados.forEach(pago => {
                const fila = document.createElement("tr");
                fila.className = "border-bottom border-light";
                fila.innerHTML = `
                    <td>${formatearFecha(pago.fecha_pago)}</td>
                    <td><i class="bi bi-person me-1"></i>${pago.profesor_nombre}</td>
                    <td class="text-end">${pago.total_horas}</td>
                    <td class="text-end"><i class="bi bi-cash-coin me-1"></i>€${pago.total}</td>
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 btn-detalle-pago" data-id="${pago.id}" title="View payment details">
                            <i class="bi bi-eye"></i> View
                        </button>
                    </td>
                `;
                cuerpoTablaRegistrados.appendChild(fila);
            });
            
            // 🟦 Asignar eventos a los botones de detalle
            document.querySelectorAll(".btn-detalle-pago").forEach(btn => {
                btn.addEventListener("click", () => {
                    const idPago = btn.getAttribute("data-id");
                    const pago = data.registrados.find(p => p.id == idPago);
                    if (pago) {
                        mostrarDetallePago(pago);
                    } else {
                        console.error("Pago no encontrado para ID:", idPago);
                    }
                });
            });

            $('#tablaPagosPendientes').DataTable({
                pageLength: 10,
                lengthChange: false,
                ordering: true,
                searching: true,
                language: {
                    search: "Search by name or amount:",
                    emptyTable: "No completed classes yet",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                },
                infoCallback: function (settings, start, end, max, total, pre) {
                    return `Showing ${end} classes of ${total} registered`;
                }
            });

            $('#tablaPagosRealizados').DataTable({
                pageLength: 10,
                lengthChange: false,
                order: [[3, 'desc']],
                columnDefs: [
                    { type: 'fecha-guion', targets: 3 }
                ],
                searching: true,
                ordering: true,
                language: {
                    search: "Search by name, date or amount:",
                    emptyTable: "No payments recorded yet",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                },
                infoCallback: function (settings, start, end, max, total, pre) {
                    return `Showing ${end} payments of ${total} recorded`;
                }
            });

        })
        .catch(error => console.error("Error loading payments:", error))
        .finally(() => {
            clearTimeout(loaderTimeout);
            document.getElementById("pagosLoader")?.remove();
        });
}

function mostrarDetallePago(pago) {
    // Destruye la instancia previa de DataTable (si existe)
    if ($.fn.DataTable.isDataTable('#tablaDetallePagoProfesor')) {
        $('#tablaDetallePagoProfesor').DataTable().destroy();
    }

    // Limpia el tbody antes de mostrar nuevos datos
    const tbody = document.getElementById("tbodyDetallePagoProfesor");
    if (!tbody) {
        console.error("❌ No se encontró el tbody 'tbodyDetallePagoProfesor' en el DOM.");
        return;
    }
    tbody.innerHTML = `<tr><td colspan="5">Loading classes...</td></tr>`;

    // Carga los datos básicos del pago
    document.getElementById("modalPagoProfesor").textContent = pago.profesor_nombre;
    document.getElementById("modalPagoHoras").textContent = pago.total_horas;
    document.getElementById("modalPagoTotal").textContent = `€${pago.total}`;
    document.getElementById("modalPagoFecha").textContent = formatearFecha(pago.fecha_pago);

    // Fetch con timestamp para evitar caché
    fetch(`php/clases_por_pago.php?pago_id=${pago.id}&_=${Date.now()}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.clases)) throw new Error("Invalid response");

            const clases = data.clases;

            if (clases.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5">No classes associated.</td></tr>`;
                return;
            }

            tbody.innerHTML = ""; // Limpia antes de agregar filas

            clases.forEach(clase => {
                const duracion = calcularHoras(clase.hora_inicio, clase.hora_fin);
                const tarifa = parseFloat(clase.tarifa_hora).toFixed(2);
                const monto = (duracion * tarifa).toFixed(2);

                const fila = `<tr>
                    <td>${formatearFecha(clase.fecha)}</td>
                    <td>${clase.alumno_nombre}</td>
                    <td>${duracion} hrs</td>
                    <td>€${tarifa}</td>
                    <td>€${monto}</td>
                </tr>`;

                tbody.innerHTML += fila;
            });

            // Inicializa DataTable nuevamente
            $('#tablaDetallePagoProfesor').DataTable({
                pageLength: 10,
                lengthChange: false,
                searching: false,
                ordering: false,
                info: true,
                language: {
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    },
                    emptyTable: "No classes to display"
                },
                infoCallback: function(settings, start, end, max, total, pre) {
                    return `Showing ${end} classes of ${total} registered`;
                }
            });
        })
        .catch(error => {
            console.error("Error loading associated classes:", error);
            tbody.innerHTML = `<tr><td colspan="5">Error loading classes</td></tr>`;
        });

    // ✅ Muestra el modal
    const modalElement = document.getElementById("modalDetallePago");
    if (!modalElement) {
        console.error("❌ No se encontró el modal con ID 'modalDetallePago' en el DOM.");
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

// Limpia el detalle al cerrar el modal (opcional, pero recomendado)
document.getElementById("modalDetallePago").addEventListener("hidden.bs.modal", function () {
    const tbody = document.getElementById("tbodyDetallePagoProfesor");
    if (tbody) tbody.innerHTML = "";
});



function calcularHoras(inicio, fin) {
    const [h1, m1] = inicio.split(":" ).map(Number);
    const [h2, m2] = fin.split(":" ).map(Number);

    const minutosInicio = h1 * 60 + m1;
    const minutosFin = h2 * 60 + m2;

    const diferenciaMinutos = minutosFin - minutosInicio;
    return (diferenciaMinutos / 60).toFixed(2);
}

function formatearFecha(fechaIso) {
    const [anio, mes, dia] = fechaIso.split("-");
    return `${dia}-${mes}-${anio}`;
}

function registrarPago(profesorNombre, totalHoras, total) {
    if (!confirm(`Confirm payment of €${total} to ${profesorNombre}?`)) {
        return;
    }

    fetch("php/registrar_pago.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `profesor_nombre=${encodeURIComponent(profesorNombre)}&total_horas=${totalHoras}&total=${total}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarToast("Payment successfully recorded", "success");
                cargarPagos();
                cargarResumenFacturacion(); 
            } else {
                mostrarToast("Error: " + data.message, "danger");
            }
        })
        .catch(error => {
            console.error("Error recording payment:", error);
            mostrarToast("Error recording payment", "danger");
        });
}

function mostrarToast(mensaje, tipo = "success") {
    const toast = document.getElementById("toastGeneral");
    const toastBody = document.getElementById("toastMensaje");
    toastBody.textContent = mensaje;
    toast.className = "toast align-items-center text-white border-0 bg-" + tipo;
    const toastInstance = new bootstrap.Toast(toast);
    toastInstance.show();
}

jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
    "fecha-guion-pre": function (fecha) {
        const partes = fecha.split("-");
        return new Date(`${partes[2]}-${partes[1]}-${partes[0]}`).getTime();
    }
});

document.getElementById("btnDescargarPDF").addEventListener("click", function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    const profesor = document.querySelector("#modalPagoProfesor").textContent.trim();
    const horas = document.querySelector("#modalPagoHoras").textContent.trim();
    const total = document.querySelector("#modalPagoTotal").textContent.trim();
    const fechaPago = document.querySelector("#modalPagoFecha").textContent.trim();

    const hoy = new Date();
    const fechaHoyNombre = hoy.toISOString().split("T")[0].split("-").reverse().join("-");

    doc.setFontSize(16);
    doc.text(`Payment Receipt – ${profesor}`, 20, 20);
    doc.setFontSize(12);

    doc.text(`Instructor: ${profesor}`, 20, 40);
    doc.text(`Hours Paid: ${horas}`, 20, 48);
    doc.text(`Total: ${total}`, 20, 56);
    doc.text(`Payment Date: ${fechaPago}`, 20, 64);

    const headers = [["Date", "Participant", "Duration (hrs)", "Rate/hr (€)", "Amount (€)"]];
    const rows = [];

    document.querySelectorAll("#tbodyDetallePagoProfesor tr").forEach(row => {
        const celdas = row.querySelectorAll("td");
        if (celdas.length === 5) {
            rows.push([
                celdas[0].textContent.trim(),
                celdas[1].textContent.trim(),
                celdas[2].textContent.trim(),
                celdas[3].textContent.trim(),
                celdas[4].textContent.trim()
            ]);
        }
    });

    doc.autoTable({ head: headers, body: rows, startY: 75 });

    const finalY = doc.lastAutoTable.finalY || 85;
    doc.setFontSize(10);
    doc.text("Thank you for your work." + NOMBRE_ESCUELA, 20, finalY + 20);

    doc.save(`payment_receipt_${profesor}_${fechaHoyNombre}.pdf`);
});


// Muestra el modal y carga el detalle de clases pendientes por profesor
function verDetalleClasesPendientes(profesorId) {
    // Loader inicial
    $('#modalPendienteProfesor').text('Loading...');
    $('#modalPendienteHoras').text('—');
    $('#modalPendienteTotal').text('—');
    $('#tbodyDetalleClasesPendientes').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
    $('#btnRegisterPayment').prop('disabled', false).html('Register Payment');

    // Abrir modal
    $('#modalDetalleClasesPendientes').modal('show');

    // AJAX para traer los datos
    $.ajax({
        url: 'php/obtener_clases_pendientes.php',
        method: 'POST',
        data: { profesor_id: profesorId },
        dataType: 'json',
        success: function(resp) {
            if (resp.success) {
                $('#modalPendienteProfesor').text(resp.profesor_nombre);
                $('#modalPendienteHoras').text(resp.total_horas);
                $('#modalPendienteTotal').text(resp.total_pagar);

                let rows = '';
                resp.clases.forEach(cl => {
                    rows += `
                        <tr>
                            <td>${cl.fecha}</td>
                            <td>${cl.alumno_nombre}</td>
                            <td>${cl.duracion}</td>
                            <td>${cl.tarifa_hora}</td>
                            <td>${cl.importe}</td>
                        </tr>`;
                });
                $('#tbodyDetalleClasesPendientes').html(rows);

                // 💡 Destruir cualquier instancia previa
                if ($.fn.DataTable.isDataTable('#tablaDetalleClasesPendientes')) {
                    $('#tablaDetalleClasesPendientes').DataTable().destroy();
                }

                // 💡 Inicializar DataTable
                $('#tablaDetalleClasesPendientes').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    searching: false,
                    ordering: false,
                    info: true,
                    language: {
                        paginate: {
                            previous: "Previous",
                            next: "Next"
                        },
                        emptyTable: "No classes to display"
                    },
                    infoCallback: function(settings, start, end, max, total, pre) {
                        return `Showing ${end} classes of ${total} pending`;
                    }
                });

            } else {
                $('#tbodyDetalleClasesPendientes').html('<tr><td colspan="6" class="text-danger text-center">No data found</td></tr>');
            }
        },
        error: function() {
            $('#tbodyDetalleClasesPendientes').html('<tr><td colspan="6" class="text-danger text-center">Error loading data</td></tr>');
        }
    });

    // Guardar el profesorId actual para usar al registrar el pago
    $('#btnRegisterPayment').data('profesor-id', profesorId);
}

$(document).ready(function() {
    $('#btnRegisterPayment').off('click').on('click', function() {
        const btn = $(this);
        const profesorId = btn.data('profesor-id');

        btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering payment...'
        );

        // AJAX para registrar pago
        $.ajax({
            url: 'php/registrar_pago.php',
            method: 'POST',
            data: { profesor_id: profesorId },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#modalDetalleClasesPendientes').modal('hide');
                    mostrarToast('Payment registered successfully!');
                    // Refrescar tablas de pagos
                    cargarPagos();
                } else {
                    alert(resp.message || "Error registering payment.");
                }
            },
            error: function() {
                alert("Network or server error. Try again.");
            },
            complete: function() {
                btn.prop('disabled', false).html('Register Payment');
            }
        });
    });
});
