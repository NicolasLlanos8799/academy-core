// admin_clases.js
// Funciones para la gestión de clases en el panel admin

function actualizarBotonCompletado(clase) {
    const boton = document.getElementById("btnClaseCompletada");
    if (!boton) return;

    if (clase.estado === "completada") {
        boton.disabled = true;
        boton.textContent = "✅ Completed";
    } else {
        boton.disabled = false;
        boton.textContent = "Mark as Completed";
    }
}

document.addEventListener("DOMContentLoaded", function () {
    cargarClases()
    mostrarSeccion('clases');

    // ✅ Forzar hora 00:00 al abrir modales de clase
    const modalAsignarClase = document.getElementById('modalAsignarClase');
    if (modalAsignarClase) {
        modalAsignarClase.addEventListener('show.bs.modal', function () {
            document.getElementById('hora_inicio').value = "06:00";
            document.getElementById('hora_fin').value = "06:00";
        });
    }

    const modalEditarClase = document.getElementById('modalEditarClase');
    if (modalEditarClase) {
        modalEditarClase.addEventListener('show.bs.modal', function () {
            // Solo establecer si está vacío, para no sobreescribir valores reales
            const hInicio = document.getElementById('editar_hora_inicio');
            const hFin = document.getElementById('editar_hora_fin');
            if (!hInicio.value) hInicio.value = "06:00";
            if (!hFin.value) hFin.value = "06:00";
        });
    }
});


// Global variables for updating billing details
let anioActualDetalle = null;
let mesActualDetalle = null;

function mostrarFormularioClase() {
    const formulario = document.getElementById("formulario-clase");

    if (!formulario) {
        console.error("Error: Class form not found in the DOM.");
        return;
    }

    formulario.style.display = formulario.style.display === "none" ? "block" : "none";
    cargarListaProfesores();
}

function asignarClase() {
    const btn = document.getElementById("btnGuardarClase");
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-sm" role="status" aria-hidden="true"></span> Saving...`;

    const profesorId = document.getElementById("profesor").value;
    const tarifaHora = document.getElementById("tarifa_hora").value.trim();
    const fecha = document.getElementById("fecha").value;
    const horaInicio = document.getElementById("hora_inicio").value;
    const horaFin = document.getElementById("hora_fin").value;
    const alumno = document.getElementById("alumno").value.trim();
    const email = document.getElementById("email_alumno").value.trim();
    const telefono = document.getElementById("telefono_alumno").value.trim();
    const observaciones = document.getElementById("observaciones").value.trim();
    const pagoEfectivo = document.getElementById("pago_efectivo").value.trim();
    const pagoTarjeta = document.getElementById("pago_tarjeta").value.trim();

    if (!profesorId || !fecha || !horaInicio || !horaFin || !alumno) {
        alert("All required fields must be completed.");
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }

    fetch("php/agregar_clase.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `profesor_id=${profesorId}&fecha=${fecha}&hora_inicio=${horaInicio}&hora_fin=${horaFin}&alumno=${encodeURIComponent(alumno)}&email=${encodeURIComponent(email)}&telefono=${encodeURIComponent(telefono)}&observaciones=${encodeURIComponent(observaciones)}&tarifa_hora=${encodeURIComponent(tarifaHora)}&pago_efectivo=${encodeURIComponent(pagoEfectivo)}&pago_tarjeta=${encodeURIComponent(pagoTarjeta)}`
    })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAsignarClase'));
                if (modal) modal.hide();

                // Primero muestra la sección correcta
                if (typeof mostrarSeccion === "function") {
                    mostrarSeccion('clases');
                }

                // Luego carga los datos
                cargarClases();
                if (calendarInstancia?.refetchEvents) calendarInstancia.refetchEvents();
                if (typeof cargarPagos === "function") cargarPagos();

                mostrarToast("Class successfully assigned", "success");
            } else {
                mostrarToast("Error: " + data.message, "danger");
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            console.error("Error while assigning class:", error);
            mostrarToast("Error while assigning class", "danger");
        });
}


function generarColorDesdeTexto(texto) {
    let hash = 0;
    for (let i = 0; i < texto.length; i++) {
        hash = texto.charCodeAt(i) + ((hash << 5) - hash);
    }
    const color = '#' + ((hash >> 24) & 0xFF).toString(16).padStart(2, '0') +
        ((hash >> 16) & 0xFF).toString(16).padStart(2, '0') +
        ((hash >> 8) & 0xFF).toString(16).padStart(2, '0');
    return color.slice(0, 7);
}

function cargarClases() {
    fetch("php/listar_clases.php")
        .then(response => response.json())
        .then(data => {
            const tabla = document.getElementById("tablaClases");
            tabla.innerHTML = "";

            data.forEach(clase => {
                let fila = `
                <tr>
                    <td>${clase.id}</td>
                    <td>${clase.profesor_nombre}</td>
                    <td>${clase.fecha}</td>
                    <td>${clase.hora_inicio} - ${clase.hora_fin}</td>
                    <td>${clase.alumno_nombre}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editarClase(${clase.id}, '${clase.profesor_id}', '${clase.fecha}', '${clase.hora_inicio}', '${clase.hora_fin}', '${clase.alumno_nombre}')">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="eliminarClase(${clase.id})">Delete</button>
                    </td>
                </tr>`;
                tabla.innerHTML += fila;
            });
        })
        .catch(error => console.error("Error loading classes:", error));
}

function guardarEdicionClase() {
    const btn = document.getElementById("btnGuardarEdicion");
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-sm" role="status" aria-hidden="true"></span> Saving...`;

    const id = document.getElementById("clase_id").value;
    const profesorId = document.getElementById("editar_profesor").value;
    const tarifaHora = document.getElementById("editar_tarifa_hora").value.trim();
    const fecha = document.getElementById("editar_fecha").value;
    const horaInicio = document.getElementById("editar_hora_inicio").value;
    const horaFin = document.getElementById("editar_hora_fin").value;
    const alumno = document.getElementById("editar_alumno").value.trim();
    const email = document.getElementById("editar_email_alumno").value.trim();
    const telefono = document.getElementById("editar_telefono_alumno").value.trim();
    const observaciones = document.getElementById("editar_observaciones").value.trim();
    const pagoEfectivo = document.getElementById("editar_pago_efectivo").value.trim();
    const pagoTarjeta = document.getElementById("editar_pago_tarjeta").value.trim();

    if (!id || !profesorId || !fecha || !horaInicio || !horaFin || !alumno) {
        mostrarToast("All required fields must be completed", "warning");
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }

    fetch("php/editar_clase.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&profesor_id=${profesorId}&fecha=${fecha}&hora_inicio=${horaInicio}&hora_fin=${horaFin}&alumno=${encodeURIComponent(alumno)}&email=${encodeURIComponent(email)}&telefono=${encodeURIComponent(telefono)}&observaciones=${encodeURIComponent(observaciones)}&tarifa_hora=${encodeURIComponent(tarifaHora)}&pago_efectivo=${encodeURIComponent(pagoEfectivo)}&pago_tarjeta=${encodeURIComponent(pagoTarjeta)}`
    })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarClase'));
                if (modal) modal.hide();

                cargarClases();
                if (calendarInstancia?.refetchEvents) calendarInstancia.refetchEvents();
                if (typeof cargarPagos === "function") cargarPagos();
                if (typeof cargarFacturacionMensual === "function") cargarFacturacionMensual();
                if (anioActualDetalle && mesActualDetalle && typeof verDetalleMes === "function") {
                    verDetalleMes(anioActualDetalle, mesActualDetalle);
                }

                mostrarToast("Class successfully updated", "success");
            } else {
                mostrarToast("Error updating class", "danger");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            btn.disabled = false;
            btn.innerHTML = originalText;
            mostrarToast("Network error while saving changes", "danger");
        });
}

function eliminarClase(id, callback) {
    fetch("php/eliminar_clase.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast("Class successfully deleted.");

            const evento = calendarInstancia.getEventById(id.toString());
            if (evento) evento.remove();

            cargarClases();

            if (typeof cargarPagos === "function") cargarPagos();
            if (typeof cargarFacturacionMensual === "function") cargarFacturacionMensual();
            if (anioActualDetalle && mesActualDetalle && typeof verDetalleMes === "function") {
                verDetalleMes(anioActualDetalle, mesActualDetalle);
            }
            if (typeof callback === "function") callback();

        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error deleting class:", error);
    });
}


function cargarListaProfesores() {
    fetch("php/listar_profesores.php")
        .then(response => response.json())
        .then(data => {
            const selectProfesores = document.getElementById("profesor");
            if (!selectProfesores) {
                console.error("Error: Instructor select element not found in the DOM.");
                return;
            }

            selectProfesores.innerHTML = '<option value="">Select an instructor</option>';

            data.forEach(profesor => {
                let opcion = document.createElement("option");
                opcion.value = profesor.id;
                opcion.textContent = profesor.nombre;
                selectProfesores.appendChild(opcion);
            });
        })
        .catch(error => console.error("Error loading instructors into the form:", error));
}


function cargarListaProfesoresEdicion(profesorSeleccionado) {
    fetch("php/listar_profesores.php")
        .then(response => response.json())
        .then(data => {
            const selectProfesores = document.getElementById("editar_profesor");
            selectProfesores.innerHTML = '<option value="">Select an instructor</option>';

            data.forEach(profesor => {
                let opcion = document.createElement("option");
                opcion.value = profesor.id;
                opcion.textContent = profesor.nombre;
                if (profesor.id == profesorSeleccionado) {
                    opcion.selected = true;
                }
                selectProfesores.appendChild(opcion);
            });
        })
        .catch(error => console.error("Error loading instructors in edit form:", error));
}

function actualizarTotalEditado() {
    const cash = parseFloat(document.getElementById('editar_pago_efectivo').value) || 0;
    const card = parseFloat(document.getElementById('editar_pago_tarjeta').value) || 0;
    document.getElementById('editar_importe_pagado').value = (cash + card).toFixed(2);
}

function actualizarTotalPagado() {
    const cash = parseFloat(document.getElementById('pago_efectivo').value) || 0;
    const card = parseFloat(document.getElementById('pago_tarjeta').value) || 0;
    document.getElementById('importePagado').value = (cash + card).toFixed(2);
}

function mostrarLeyendaProfesores(profesores) {
    const container = document.getElementById('leyendaProfesores');
    container.innerHTML = ''; // clear previous content

    const assignedColors = {};

    profesores.forEach(name => {
        if (!assignedColors[name]) {
            const color = generarColorDesdeTexto(name);
            assignedColors[name] = color;

            const label = document.createElement('div');
            label.className = 'd-flex align-items-center gap-2';
            label.innerHTML = `
                <span style="width: 16px; height: 16px; background-color: ${color}; border-radius: 4px; display: inline-block;"></span>
                <span>${name}</span>
            `;
            container.appendChild(label);
        }
    });
}

function mostrarToast(message, type = "success") {
    const toast = document.getElementById("toastGeneral");
    const toastBody = document.getElementById("toastMensaje");

    toastBody.textContent = message;

    toast.className = "toast align-items-center text-white border-0 bg-" + type;

    const toastInstance = new bootstrap.Toast(toast);
    toastInstance.show();
}

function inicializarCalendario() {
    const calendarEl = document.getElementById('calendar');
    const isSmallScreen = window.innerWidth <= 400;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'en',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        dayMaxEvents: false,
        aspectRatio: isSmallScreen ? 0.75 : 1.35,
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        events: function (fetchInfo, successCallback, failureCallback) {
            fetch('php/listar_clases.php')
                .then(res => res.json())
                .then(data => {
                    const profesoresUnicos = [...new Set(data.map(c => c.profesor_nombre))];
                    mostrarLeyendaProfesores(profesoresUnicos);

                    const eventos = data.map(clase => ({
                        id: clase.id.toString(),
                        title: clase.alumno_nombre,
                        start: `${clase.fecha}T${clase.hora_inicio}`,
                        end: `${clase.fecha}T${clase.hora_fin}`,
                        backgroundColor: generarColorDesdeTexto(clase.profesor_nombre),
                        borderColor: generarColorDesdeTexto(clase.profesor_nombre),
                        textColor: "#fff",
                        extendedProps: {
                            profesor: clase.profesor_nombre,
                            observaciones: clase.observaciones,
                            email: clase.email,
                            telefono: clase.telefono,
                            estado: clase.estado,
                            importe_pagado: clase.importe_pagado,
                            tarifa_hora: clase.tarifa_hora,
                            pago_efectivo: clase.pago_efectivo,
                            pago_tarjeta: clase.pago_tarjeta
                        }
                    }));

                    successCallback(eventos);
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                    failureCallback(error);
                });
        },
        dateClick: function (info) {
            document.getElementById("fecha").value = info.dateStr;
            document.getElementById("hora_inicio").value = '';
            document.getElementById("hora_fin").value = '';
            document.getElementById("alumno").value = '';
            document.getElementById("email_alumno").value = '';
            document.getElementById("telefono_alumno").value = '';
            document.getElementById("importePagado").value = '';
            document.getElementById("tarifa_hora").value = '';
            document.getElementById("pago_efectivo").value = 0;
            document.getElementById("pago_tarjeta").value = 0;
            document.getElementById("observaciones").value = '';
            cargarListaProfesores();

            const modal = new bootstrap.Modal(document.getElementById('modalAsignarClase'));
            modal.show();
        },
        eventClick: function (info) {
            const evento = info.event;

            document.getElementById('detalleAlumno').textContent = evento.title;
            document.getElementById('detalleProfesor').textContent = evento.extendedProps.profesor;
            document.getElementById('detalleObservaciones').textContent = evento.extendedProps.observaciones?.trim() || 'No observations';
            document.getElementById('detalleEmail').textContent = evento.extendedProps.email || '—';
            document.getElementById('detalleTelefono').textContent = evento.extendedProps.telefono || '—';
            document.getElementById('detalleImportePagado').textContent = evento.extendedProps.importe_pagado || '—';
            document.getElementById('detallePagoEfectivo').textContent = evento.extendedProps.pago_efectivo || '—';
            document.getElementById('detallePagoTarjeta').textContent = evento.extendedProps.pago_tarjeta || '—';
            document.getElementById('detalleTarifaHora').textContent = parseFloat(evento.extendedProps.tarifa_hora).toFixed(2);

            const fechaObj = evento.start;
            const fecha = `${String(fechaObj.getDate()).padStart(2, '0')}-${String(fechaObj.getMonth() + 1).padStart(2, '0')}-${fechaObj.getFullYear()}`;
            const horaInicio = evento.start.toTimeString().slice(0, 5);
            const horaFin = evento.end ? evento.end.toTimeString().slice(0, 5) : "—";

            document.getElementById('detalleFecha').textContent = fecha;
            document.getElementById('detalleHorario').textContent = `${horaInicio} - ${horaFin}`;

            const btnCompletada = document.getElementById('btnClaseCompletada');
            actualizarBotonCompletado(evento.extendedProps);
            if (evento.extendedProps.estado !== 'completada') {
                btnCompletada.setAttribute('data-id', evento.id);
            }

            document.getElementById('btnEditarClase').onclick = function () {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetalleClase'));
                if (modal) modal.hide();
                setTimeout(() => abrirFormularioEdicion(evento.id), 200);
            };

            document.getElementById('btnEliminarClase').onclick = function () {
                const btn = this;
                const originalText = btn.innerHTML;
            
                // Pide confirmación primero
                if (!confirm("Are you sure you want to delete this class? This action cannot be undone.")) {
                    return; // Si cancela, no hace nada
                }
            
                // Solo si CONFIRMÓ, cambia el estado visual del botón:
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-sm" role="status" aria-hidden="true"></span> Deleting...`;
            
                eliminarClase(evento.id, function () {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetalleClase'));
                    if (modal) modal.hide();
            
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            };
            

            const modal = new bootstrap.Modal(document.getElementById('modalDetalleClase'));
            modal.show();
        },
        datesSet: function () {
            // reserved for potential future logic when view dates change
        }
    });

    calendar.render();
    const toolbar = document.querySelector('.fc-header-toolbar');

    if (toolbar) {
        const observer = new MutationObserver(() => {
            const prevBtn = document.querySelector('.fc-prev-button');
            const nextBtn = document.querySelector('.fc-next-button');

            if (prevBtn && !prevBtn.classList.contains('icon-replaced')) {
                prevBtn.innerHTML = '<span class="material-icons">chevron_left</span>';
                prevBtn.classList.add('icon-replaced');
            }

            if (nextBtn && !nextBtn.classList.contains('icon-replaced')) {
                nextBtn.innerHTML = '<span class="material-icons">chevron_right</span>';
                nextBtn.classList.add('icon-replaced');
            }
        });

        observer.observe(toolbar, { childList: true, subtree: true });
    }
    return calendar;
}




function abrirFormularioEdicion(id) {
    fetch('php/listar_clases.php')
        .then(response => response.json())
        .then(data => {
            const clase = data.find(c => c.id == id);
            if (!clase) {
                mostrarToast("Class not found", "danger");
                return;
            }

            // Fill in the form
            document.getElementById("clase_id").value = clase.id;
            document.getElementById("editar_fecha").value = clase.fecha;
            document.getElementById("editar_hora_inicio").value = clase.hora_inicio;
            document.getElementById("editar_hora_fin").value = clase.hora_fin;
            document.getElementById("editar_alumno").value = clase.alumno_nombre;
            document.getElementById("editar_email_alumno").value = clase.email;
            document.getElementById("editar_telefono_alumno").value = clase.telefono;
            document.getElementById("editar_observaciones").value = clase.observaciones || '';

            const efectivo = parseFloat((clase.pago_efectivo || '0').toString().replace(',', '.'));
            const tarjeta = parseFloat((clase.pago_tarjeta || '0').toString().replace(',', '.'));
            const tarifa = parseFloat((clase.tarifa_hora || '0').toString().replace(',', '.'));

            document.getElementById("editar_pago_efectivo").value = isNaN(efectivo) ? '' : efectivo.toFixed(2);
            document.getElementById("editar_pago_tarjeta").value = isNaN(tarjeta) ? '' : tarjeta.toFixed(2);

            document.getElementById("editar_importe_pagado").value = (efectivo + tarjeta).toFixed(2);
            document.getElementById("editar_tarifa_hora").value = isNaN(tarifa) ? '' : tarifa.toFixed(2);

            cargarListaProfesoresEdicion(clase.profesor_id);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalEditarClase'));
            modal.show();
        })
        .catch(error => {
            console.error("Error loading class:", error);
            mostrarToast("Error loading class details", "danger");
        });
}

// ✅ Mark class as completed
document.getElementById("btnClaseCompletada").addEventListener("click", function () {
    const claseId = this.getAttribute("data-id");

    if (!claseId) {
        mostrarToast("Unable to identify the class", "danger");
        return;
    }

    fetch("php/marcar_completada.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${claseId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById("modalDetalleClase"));
                if (modalInstance) modalInstance.hide();

                actualizarBotonCompletado({ estado: 'completada' });

                cargarClases();
                if (calendarInstancia?.refetchEvents) calendarInstancia.refetchEvents();
                if (typeof cargarPagos === "function") cargarPagos();
                if (typeof cargarFacturacionMensual === "function") cargarFacturacionMensual();

                mostrarToast("Class marked as completed", "success");
            } else {
                mostrarToast("Error: " + data.message, "danger");
            }
        })
        .catch(error => {
            console.error("Error while marking class as completed:", error);
            mostrarToast("Error while marking as completed", "danger");
        });
});
