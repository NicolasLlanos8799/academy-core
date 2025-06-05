// admin_profesores.js
// Funciones para la gestión de profesores en el panel admin

document.addEventListener("DOMContentLoaded", function () {
    cargarProfesores();
});

function mostrarFormularioProfesor() {
    const formulario = document.getElementById("formulario-profesor");
    formulario.style.display = formulario.style.display === "none" ? "block" : "none";
}

function agregarProfesor() {
    const nombre = document.getElementById("nombre").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const telefono = document.getElementById("telefono").value.trim();

    if (!nombre || !email || !password) {
        mostrarToast("All required fields must be filled out", "warning");
        return;
    }

    fetch("php/agregar_profesor.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `nombre=${encodeURIComponent(nombre)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&telefono=${encodeURIComponent(telefono)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('modalAgregarProfesor');
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modalInstance.hide();

                document.getElementById("formAgregarProfesor").reset();
                mostrarToast("Instructor successfully added", "success");
                cargarProfesores();
            } else {
                mostrarToast("Error: " + data.message, "danger");
            }
        })
        .catch(error => {
            console.error("Error while adding instructor:", error);
            mostrarToast("Network error while adding instructor", "danger");
        });
}

function cargarProfesores() {
    fetch("php/listar_profesores.php")
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                console.error("Unexpected response:", data);
                return;
            }

            const tabla = document.getElementById("tablaProfesores");
            tabla.innerHTML = "";

            data.forEach(profesor => {
                const fila = document.createElement("tr");
                fila.innerHTML = `
                    <td>${profesor.id}</td>
                    <td>${profesor.nombre}</td>
                    <td>${profesor.email}</td>
                    <td>${profesor.telefono ? profesor.telefono : 'Not provided'}</td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick='editarProfesor(
                            ${profesor.id},
                            ${JSON.stringify(profesor.nombre)},
                            ${JSON.stringify(profesor.email)},
                            ${JSON.stringify(profesor.telefono || '')}
                        )'>Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="eliminarProfesor(${profesor.id})">Delete</button>
                    </td>
                `;
                tabla.appendChild(fila);
            });
        })
        .catch(error => console.error("Error loading instructors:", error));
}

function eliminarProfesor(id) {
    if (!confirm("Are you sure you want to delete this instructor? This action cannot be undone.")) {
        return;
    }

    fetch("php/eliminar_profesor.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Instructor successfully deleted");
                cargarProfesores();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error deleting instructor:", error));
}


function editarProfesor(id, nombre, email, telefono) {
    document.getElementById("profesor_id").value = id;
    document.getElementById("editar_nombre").value = nombre;
    document.getElementById("editar_email").value = email;
    document.getElementById("editar_telefono").value = telefono;

    const modal = new bootstrap.Modal(document.getElementById('modalEditarProfesor'));
    modal.show();
}

function guardarEdicionProfesor() {
    const id = document.getElementById("profesor_id").value;
    const nombre = document.getElementById("editar_nombre").value.trim();
    const email = document.getElementById("editar_email").value.trim();
    const telefono = document.getElementById("editar_telefono").value.trim();

    if (!nombre || !email) {
        mostrarToast("Please complete the required fields", "warning");
        return;
    }

    fetch("php/editar_profesor.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&nombre=${encodeURIComponent(nombre)}&email=${encodeURIComponent(email)}&telefono=${encodeURIComponent(telefono)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalElement = document.getElementById('modalEditarProfesor');
                const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modalInstance.hide();

                mostrarToast("Instructor successfully updated", "success");
                cargarProfesores();
            } else {
                mostrarToast("Error: " + data.message, "danger");
            }
        })
        .catch(error => {
            console.error("Error updating instructor:", error);
            mostrarToast("Network error while updating instructor", "danger");
        });
}
