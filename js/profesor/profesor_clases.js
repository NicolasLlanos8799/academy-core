// profesor_clases.js

document.addEventListener("DOMContentLoaded", function () {
    mostrarSeccion('clases');

    // ✅ Force time 06:00 when opening class modals
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
            // Only set if empty, to avoid overwriting real values
            const hInicio = document.getElementById('editar_hora_inicio');
            const hFin = document.getElementById('editar_hora_fin');
            if (!hInicio.value) hInicio.value = "06:00";
            if (!hFin.value) hFin.value = "06:00";
        });
    }
});

function inicializarCalendario() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'en',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,dayGridMonth,listWeek'
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        events: {
            url: 'php/profesor/obtener_clases_profesor.php',
            method: 'POST'
        },
        eventClick: function (info) {
            abrirModalDetalleClase(info.event);
        },
        datesSet: function (arg) {
            const titulo = arg.view.title;
            const capitalizado = titulo.charAt(0).toUpperCase() + titulo.slice(1);
            const tituloEl = document.querySelector('.fc-toolbar-title');
            if (tituloEl) {
                tituloEl.textContent = capitalizado;
            }
        },
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


function abrirModalDetalleClase(evento) {
    const datos = evento.extendedProps;
    const fecha = evento.start;
    const fechaFormateada = `${String(fecha.getDate()).padStart(2, '0')}-${String(fecha.getMonth() + 1).padStart(2, '0')}-${fecha.getFullYear()}`;

    document.getElementById('detalleAlumno').innerText = datos.alumno;
    document.getElementById('detalleFecha').innerText = fechaFormateada;
    document.getElementById('detalleHorario').innerText = `${evento.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${evento.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    document.getElementById('detalleEmail').innerText = datos.email || '-';
    document.getElementById('detalleTelefono').innerText = datos.telefono || '-';
    document.getElementById('detalleTarifaHora').innerText = datos.tarifa_hora ? `${datos.tarifa_hora} ` : 'Not available';
    document.getElementById('detalleObservaciones').innerText = datos.observaciones || 'No observations';

    const modal = new bootstrap.Modal(document.getElementById('modalDetalleClase'));
    document.getElementById('modalDetalleClase').dataset.idClase = evento.id;

    const btnCompletada = document.getElementById('btnClaseCompletada');

    // Visual state of the button depending on class status
    if (datos.estado === 'completada') {
        btnCompletada.classList.remove("btn-success");
        btnCompletada.classList.add("btn-success", "opacity-50");
        btnCompletada.disabled = true;
        btnCompletada.textContent = "✅ Class completed";
    } else {
        btnCompletada.disabled = false;
        btnCompletada.textContent = "Mark as Completed";
        btnCompletada.classList.remove("btn-secondary", "opacity-50");
        btnCompletada.classList.add("btn-success");
        btnCompletada.onclick = marcarClaseComoCompletada;
    }

    document.getElementById('btnEditarClase').onclick = abrirFormularioEdicionClaseProfesor;
    document.getElementById('btnEliminarClase').onclick = eliminarClaseProfesor;

    modal.show();
}

function marcarClaseComoCompletada() {
    const btn = document.getElementById("btnClaseCompletada");
    const claseId = document.getElementById('modalDetalleClase').dataset.idClase;

    if (!claseId) return mostrarToast("Class not identified", "danger");

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-sm" role="status"></span> Saving...`;

    fetch("php/marcar_completada.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${claseId}`
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalDetalleClase")).hide();
            if (calendarInstancia?.refetchEvents) calendarInstancia.refetchEvents();
            if (typeof cargarPagosProfesor === "function") cargarPagosProfesor();
            mostrarToast("Class successfully marked as completed", "success");
        } else {
            mostrarToast("Error: " + data.message, "danger");
        }
    })
    .catch(err => {
        console.error("Error:", err);
        btn.disabled = false;
        btn.innerHTML = originalText;
        mostrarToast("Error while marking as completed", "danger");
    });
}

function abrirFormularioEdicionClaseProfesor() {
    const claseId = document.getElementById('modalDetalleClase').dataset.idClase;

    fetch('php/listar_clases.php')
        .then(res => res.json())
        .then(data => {
            const clase = data.find(c => c.id == claseId);
            if (!clase) return mostrarToast("Class not found", "danger");

            document.getElementById("clase_id").value = clase.id;
            document.getElementById("editar_fecha").value = clase.fecha;
            document.getElementById("editar_hora_inicio").value = clase.hora_inicio;
            document.getElementById("editar_hora_fin").value = clase.hora_fin;
            document.getElementById("editar_alumno").value = clase.alumno_nombre;
            document.getElementById("editar_email_alumno").value = clase.email;
            document.getElementById("editar_telefono_alumno").value = clase.telefono;
            document.getElementById("editar_observaciones").value = clase.observaciones || '';

            bootstrap.Modal.getInstance(document.getElementById("modalDetalleClase")).hide();

            const modal = new bootstrap.Modal(document.getElementById('modalEditarClase'));
            modal.show();
        });
}

function guardarEdicionClaseProfesor() {
    const btn = document.getElementById("btnGuardarEdicion");
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-sm" role="status"></span> Saving...`;

    const id = document.getElementById("clase_id").value;
    const fecha = document.getElementById("editar_fecha").value;
    const horaInicio = document.getElementById("editar_hora_inicio").value;
    const horaFin = document.getElementById("editar_hora_fin").value;
    const alumno = document.getElementById("editar_alumno").value.trim();
    const email = document.getElementById("editar_email_alumno").value.trim();
    const telefono = document.getElementById("editar_telefono_alumno").value.trim();
    const observaciones = document.getElementById("editar_observaciones").value.trim();

    if (!id || !fecha || !horaInicio || !horaFin || !alumno) {
        mostrarToast("All required fields must be filled out", "warning");
        btn.disabled = false;
        btn.innerHTML = originalText;
        return;
    }

    fetch("php/profesor/editar_clase_profesor.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&fecha=${fecha}&hora_inicio=${horaInicio}&hora_fin=${horaFin}&alumno=${encodeURIComponent(alumno)}&email=${encodeURIComponent(email)}&telefono=${encodeURIComponent(telefono)}&observaciones=${encodeURIComponent(observaciones)}`
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalEditarClase")).hide();
            if (calendarInstancia?.refetchEvents) calendarInstancia.refetchEvents();
            mostrarToast("Class successfully updated", "success");
        } else {
            mostrarToast("Error updating class", "danger");
        }
    })
    .catch(err => {
        console.error("Error:", err);
        btn.disabled = false;
        btn.innerHTML = originalText;
        mostrarToast("Network error", "danger");
    });
}

function eliminarClaseProfesor() {
    const claseId = document.getElementById('modalDetalleClase').dataset.idClase;
    const btn = document.getElementById("btnEliminarClase");

    if (!confirm("Delete this class?")) return;

    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<span class="spinner-border spinner-sm" role="status"></span> Deleting...`;

    fetch("php/eliminar_clase.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${claseId}`
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("modalDetalleClase")).hide();
            if (calendarInstancia?.refetchEvents) calendarInstancia.refetchEvents();
            mostrarToast("Class successfully deleted", "success");
        } else {
            mostrarToast("Error deleting class", "danger");
        }
    })
    .catch(err => {
        console.error("Error:", err);
        btn.disabled = false;
        btn.innerHTML = originalText;
        mostrarToast("Network error while deleting", "danger");
    });
}

function mostrarToast(message, type = "success") {
    const toast = document.getElementById("toastGeneral");
    const toastBody = document.getElementById("toastMensaje");

    toastBody.textContent = message;
    toast.className = "toast align-items-center text-white bg-" + type + " border-0";

    new bootstrap.Toast(toast).show();
}
