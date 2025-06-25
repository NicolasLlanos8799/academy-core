<?php
session_start(); // 🔑 Siempre primero
$config = include __DIR__ . '/php/school_config.php';
// ===============================
// 🕒 Control de inactividad
// ===============================
$tiempoInactividad = 129600; // 36 horas en segundos

if (isset($_SESSION['ULTIMA_ACTIVIDAD']) && (time() - $_SESSION['ULTIMA_ACTIVIDAD']) > $tiempoInactividad) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Elimina la sesión
    header("Location: login.php?expirada=1");
    exit;
}

$_SESSION['ULTIMA_ACTIVIDAD'] = time(); // Renueva el tiempo de actividad

// 🔒 Bloquear caché del navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 🔐 Validar sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'profesor') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Panel</title>

    <!-- Bootstrap for UI and functionality -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <!-- Navigation bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo $config['nombre_escuela']; ?> - Teacher</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" onclick="verificarSesionYMostrar('clases')">Classes</a>
                    </li>
                    <li class="nav-item"><a class="nav-link"
                            onclick="verificarSesionYMostrar('seccionPagos')">Payments</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

        <!-- Class Management Section -->
        <div id="clases" class="seccion" style="display: none;">
            <h3>Class Management</h3>

            <!-- Calendar -->
            <p class="text-muted small mb-2">📌 Click on a day in the calendar to schedule a new class</p>
            <div class="contenedor-calendario-scroll mb-4">
                <div id="calendar"></div>
            </div>

            <!-- Class Detail Modal -->

<div class="modal fade" id="modalDetalleClase" data-id-clase="" tabindex="-1" aria-labelledby="modalDetalleClaseLabel" aria-hidden="true">
    <div class="modal-dialog">
        <!-- Modal Content -->
        <div class="modal-content p-4">
            <h4 class="modal-title mb-4 d-flex align-items-center gap-2" id="modalDetalleClaseLabel">
                <i class="bi bi-info-circle-fill text-primary"></i> Class Details
            </h4>

            <!-- Participant & Instructor -->
            <div class="section mb-3">
                <p><strong><i class="bi bi-person-fill me-1"></i>Participant:</strong> <span id="detalleAlumno"></span></p>
                <p><strong><i class="bi bi-person-badge-fill me-1"></i>Instructor:</strong> <span id="detalleProfesor"></span></p>
            </div>

            <!-- Date & Time -->
            <div class="section mb-3">
                <p><strong><i class="bi bi-calendar-event me-1"></i>Date:</strong> <span id="detalleFecha"></span></p>
                <p><strong><i class="bi bi-clock-fill me-1"></i>Time:</strong> <span id="detalleHorario"></span></p>
            </div>

            <!-- Contact Info -->
            <div class="section mb-3">
                <p><strong><i class="bi bi-envelope-fill me-1"></i>Email:</strong> <span id="detalleEmail"></span></p>
                <p><strong><i class="bi bi-telephone-fill me-1"></i>Phone:</strong> <span id="detalleTelefono"></span></p>
            </div>

            <!-- Payments -->
            <div class="section mb-3">
                <p>
                    <strong><i class="bi bi-cash-coin me-1"></i>Cash Payment (€):</strong>
                    <span id="detallePagoEfectivo" class="badge bg-success-subtle text-success">—</span>
                </p>
                <p>
                    <strong><i class="bi bi-credit-card-2-front-fill me-1"></i>Card Payment (€):</strong>
                    <span id="detallePagoTarjeta" class="badge bg-primary-subtle text-primary">—</span>
                </p>
                <p>
                    <strong><i class="bi bi-receipt-cutoff me-1"></i>Total Amount (€):</strong>
                    <span id="detalleImportePagado" class="fw-bold">—</span>
                </p>
            </div>

            <!-- Instructor rate -->
            <div class="section mb-3">
                <p>
                    <strong><i class="bi bi-currency-euro me-1"></i>Instructor Hourly Rate (€):</strong> <span id="detalleTarifaHora">—</span>
                </p>
            </div>

            <!-- Observations -->
            <div class="section mb-4">
                <p><strong><i class="bi bi-chat-left-text me-1"></i>Observations:</strong> <span id="detalleObservaciones"></span></p>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between flex-nowrap gap-2 mt-4">
                <button id="btnClaseCompletada" class="btn btn-success d-flex align-items-center gap-1 px-3 py-2 flex-fill">
                    <i class="bi bi-check-circle"></i> Completed
                </button>
                <button id="btnEditarClase" class="btn btn-warning text-white d-flex align-items-center gap-1 px-3 py-2 flex-fill">
                    <i class="bi bi-pencil-square"></i> Edit
                </button>
                <button id="btnEliminarClase" class="btn btn-danger d-flex align-items-center gap-1 px-3 py-2 flex-fill">
                    <i class="bi bi-trash3-fill"></i> Delete
                </button>
                <button type="button" class="btn btn-secondary d-flex align-items-center gap-1 px-3 py-2 flex-fill" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>


            <!-- Modal to Assign Class -->
            <div class="modal fade" id="modalAsignarClase" tabindex="-1" aria-labelledby="modalAsignarClaseLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-light">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAsignarClaseLabel">Assign New Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formAsignarClase">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="fecha" class="form-label">Date</label>
                                        <input type="date" id="fecha" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hora_inicio" class="form-label">Start Time</label>
                                        <input type="time" id="hora_inicio" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="hora_fin" class="form-label">End Time</label>
                                        <input type="time" id="hora_fin" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="alumno" class="form-label">Participant Name</label>
                                        <input type="text" id="alumno" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email_alumno" class="form-label">Participant Email</label>
                                        <input type="email" id="email_alumno" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telefono_alumno" class="form-label">Participant Phone</label>
                                        <input type="text" id="telefono_alumno" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label for="observaciones" class="form-label">Observations</label>
                                        <textarea id="observaciones" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button id="btnGuardarClase" class="btn btn-primary" onclick="asignarClase()">Save
                                Class</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal to Edit Class -->
            <div class="modal fade" id="modalEditarClase" tabindex="-1" aria-labelledby="modalEditarClaseLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-light">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditarClaseLabel">Edit Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEditarClase">
                                <input type="hidden" id="clase_id">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="editar_fecha" class="form-label">Date</label>
                                        <input type="date" id="editar_fecha" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_hora_inicio" class="form-label">Start Time</label>
                                        <input type="time" id="editar_hora_inicio" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_hora_fin" class="form-label">End Time</label>
                                        <input type="time" id="editar_hora_fin" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_alumno" class="form-label">Participant Name</label>
                                        <input type="text" id="editar_alumno" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_email_alumno" class="form-label">Participant Email</label>
                                        <input type="email" id="editar_email_alumno" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="editar_telefono_alumno" class="form-label">Participant Phone</label>
                                        <input type="text" id="editar_telefono_alumno" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label for="editar_observaciones" class="form-label">Observations</label>
                                        <textarea id="editar_observaciones" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button id="btnGuardarEdicion" class="btn btn-primary"
                                onclick="guardarEdicionClaseProfesor()">Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <div id="seccionPagos" class="seccion" style="display: none;">
            <h4>Total to Collect for Completed Classes</h4>
            <div id="contenedorClasesCompletadas" class="mb-4"></div>

            <h4>Registered Payments</h4>
            <table id="tablaPagosRegistrados" class="table table-bordered table-hover">
                <thead class="table-white">
                    <tr>
                        <th>Payment Date</th>
                        <th>Hours Worked</th>
                        <th>Amount Received</th>
                        <th>Actions</th> <!-- Nueva columna -->
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
    </div>

    <!-- Modal: Completed Classes Details (Teacher) -->
    <div class="modal fade" id="modalDetalleClasesCompletadasProfesor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-light">
                <div class="modal-header">
                    <h5 class="modal-title">Pending Completed Classes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table" id="tablaDetalleClasesCompletadas">
                        <thead class="table-white">
                            <tr>
                                <th>Date</th>
                                <th>Participant</th>
                                <th>Duration</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal: Payment Details (Teacher) -->
    <div class="modal fade" id="modalDetallePagoProfesor" tabindex="-1" aria-labelledby="detallePagoLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-white text-dark border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="detallePagoLabel">Payment Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="tablaDetallePagoProfesorTable"
                            class="table table-white table-striped table-bordered text-center mb-0">
                            <thead class="table-primary text-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Participant</th>
                                    <th>Duration</th>
                                    <th>Hourly Rate</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetallePagoProfesor">
                                <!-- Dynamically inserted -->
                            </tbody>
                        </table>
                    </div>

                    <button id="btnDescargarComprobanteProfesor" class="btn btn-primary mt-3">Download Receipt</button>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reusable Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="toastGeneral" class="toast align-items-center text-white bg-success border-0" role="alert"
            aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMensaje">
                    System message.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>


    <!-- jQuery (debe ir antes que DataTables y tus scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <!-- Soporte orden por fecha europea (dd/mm/yyyy) -->
    <script>
    jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
        "fecha-euro-pre": function(fecha) {
            const partes = fecha.split("/");
            return new Date(partes[2], partes[1] - 1, partes[0]).getTime();
        }
    });
    </script>

    <!-- Scripts del sistema -->
    <script>
    let calendarInstancia = null;

    function mostrarSeccion(seccion) {
        document.querySelectorAll('.seccion').forEach(div => div.style.display = 'none');
        const target = document.getElementById(seccion);
        target.style.display = 'block';

        if (seccion === 'clases') {
            if (!calendarInstancia) {
                setTimeout(() => {
                    calendarInstancia = inicializarCalendario();
                }, 10);
            } else {
                setTimeout(() => {
                    calendarInstancia.updateSize();
                }, 10);
            }
        }
    }
    // Esto hace disponible el nombre para todo tu JavaScript
    const NOMBRE_ESCUELA = <?php echo json_encode($config['nombre_escuela']); ?>;
    </script>

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/index.global.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/locales-all.global.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS del sistema -->
    <script src="js/verificar_sesion.js"></script>
    <script src="js/profesor/profesor_pagos.js"></script>
    <script src="js/profesor/profesor_clases.js"></script>

    <!-- jsPDF para generar comprobantes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.23/jspdf.plugin.autotable.min.js"></script>

    <!-- Estilos -->
    <link rel="stylesheet" href="css/styles.css?v=2.0">



</body>

</html>