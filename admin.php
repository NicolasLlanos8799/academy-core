<?php
$config = include __DIR__ . '/php/school_config.php';
require_once __DIR__ . '/php/validar_sesion_admin.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>

    <!-- Preconexión para Google Fonts e iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Frameworks CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/index.global.min.css" rel="stylesheet" />

    <!-- Google Fonts + Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- TU CSS PERSONALIZADO SIEMPRE ÚLTIMO -->
    <link rel="stylesheet" href="css/styles.css?v=2.0">
    <link rel="stylesheet" href="css/modal-detalle.css">

    <!-- FOUC Fix -->
    <style>
    body {
        display: none;
    }
    </style>
</head>


<body>
    <div id="loader"
        style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;display:flex;align-items:center;justify-content:center;background:#f7f8fa;">
        <span class="material-icons" style="font-size:2.2rem; color:#2563EB;">hourglass_top</span>
    </div>

    <!-- Navigation bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo $config['nombre_escuela']; ?> - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" onclick="verificarSesionYMostrar('profesores')">Instructor
                            Management</a></li>
                    <li class="nav-item"><a class="nav-link" onclick="verificarSesionYMostrar('clases')">Class
                            Management</a></li>
                    <li class="nav-item"><a class="nav-link" onclick="verificarSesionYMostrar('pagos')">Payment
                            Management</a></li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="verificarSesionYMostrar('seccionFacturacion')">Billing</a>
                    </li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Log Out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Instructor Management Section -->
        <div id="profesores" class="seccion">
            <h3 class="mb-2 text-center text-md-start">Instructor Management</h3>
            <div class="d-flex justify-content-md-end justify-content-center mb-3">
                <button class="btn btn-primary d-flex align-items-center gap-2 boton-add-instructor"
                    data-bs-toggle="modal" data-bs-target="#modalAgregarProfesor">
                    <span class="material-icons" style="font-size:18px;"></span>
                    Add Instructor
                </button>
            </div>


            <!-- Add Instructor Modal -->
            <div class="modal fade" id="modalAgregarProfesor" tabindex="-1" aria-labelledby="modalAgregarProfesorLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-light">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAgregarProfesorLabel">Add Instructor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formAgregarProfesor">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Name</label>
                                    <input type="text" id="nombre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Phone</label>
                                    <input type="text" id="telefono" class="form-control">
                                </div>
                                <div class="modal-footer d-flex gap-2">
                                    <button type="button" class="btn btn-secondary flex-fill"
                                        data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary flex-fill"
                                        onclick="agregarProfesor()">Save Instructor</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Instructor Modal -->
            <div class="modal fade" id="modalEditarProfesor" tabindex="-1" aria-labelledby="modalEditarProfesorLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content bg-light">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditarProfesorLabel">Edit Instructor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEditarProfesor">
                                <input type="hidden" id="profesor_id">
                                <div class="mb-3">
                                    <label for="editar_nombre" class="form-label">Name</label>
                                    <input type="text" id="editar_nombre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_email" class="form-label">Email</label>
                                    <input type="email" id="editar_email" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editar_telefono" class="form-label">Phone</label>
                                    <input type="text" id="editar_telefono" class="form-control">
                                </div>
                                <div class="modal-footer d-flex gap-2">
                                    <button type="button" class="btn btn-success flex-fill"
                                        onclick="guardarEdicionProfesor()">Save Changes</button>
                                    <button type="button" class="btn btn-secondary flex-fill"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructor Table -->
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tablaProfesores">
                        <!-- Instructors will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Class Management Section -->
        <div id="clases" class="seccion" style="display: none;">
            <h3>Class Management</h3>

            <!-- Calendar -->
            <p class="text-muted small mb-2">📌 Click on a day in the calendar to schedule a new class</p>
            <div class="contenedor-calendario-scroll mb-4">
                <div id="calendar"></div>
            </div>

            <!-- Instructor legend -->
            <div id="leyendaProfesores" class="d-flex flex-wrap gap-2 mb-4"></div>

            <!-- Class Detail Modal -->
            <div class="modal fade" id="modalDetalleClase" data-id-clase="" tabindex="-1"
                aria-labelledby="modalDetalleClaseLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-detalle-clase">
                    <!-- Modal Content -->
                    <div class="modal-content compacto">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <h4 class="modal-title mb-4 d-flex align-items-center gap-2" id="modalDetalleClaseLabel">
                            <i class="bi bi-info-circle-fill text-primary"></i> Class Details
                        </h4>

                        <!-- Participant & Instructor -->
                        <div class="section">
                            <p><strong><i class="bi bi-person-fill"></i>Participant:</strong> <span
                                    id="detalleAlumno"></span></p>
                            <p><strong><i class="bi bi-person-badge-fill"></i>Instructor:</strong> <span
                                    id="detalleProfesor"></span></p>
                        </div>

                        <!-- Date & Time -->
                        <div class="section">
                            <p><strong><i class="bi bi-calendar-event"></i>Date:</strong> <span
                                    id="detalleFecha"></span></p>
                            <p><strong><i class="bi bi-clock-fill"></i>Time:</strong> <span
                                    id="detalleHorario"></span></p>
                        </div>

                        <!-- Contact Info -->
                        <div class="section">
                            <p><strong><i class="bi bi-envelope-fill"></i>Email:</strong> <span
                                    id="detalleEmail"></span></p>
                            <p><strong><i class="bi bi-telephone-fill"></i>Phone:</strong> <span
                                    id="detalleTelefono"></span></p>
                        </div>

                        <!-- Payments -->
                        <div class="section">
                            <p>
                                <strong><i class="bi bi-cash-coin"></i>Cash Payment (€):</strong>
                                <span id="detallePagoEfectivo" class="badge bg-success-subtle text-success">—</span>
                            </p>
                            <p>
                                <strong><i class="bi bi-credit-card-2-front-fill"></i>Card Payment (€):</strong>
                                <span id="detallePagoTarjeta" class="badge bg-primary-subtle text-primary">—</span>
                            </p>
                            <p>
                                <strong><i class="bi bi-receipt-cutoff"></i>Total Amount (€):</strong>
                                <span id="detalleImportePagado" class="fw-bold">—</span>
                            </p>
                        </div>

                        <!-- Instructor rate -->
                        <div class="section">
                            <p>
                                <strong><i class="bi bi-currency-euro"></i>Instructor Hourly Rate (€):</strong>
                                <span id="detalleTarifaHora">—</span>
                            </p>
                        </div>

                        <!-- Additional Info -->
                        <div class="section">
                            <p><strong><i class="bi bi-chat-left-text"></i>Additional Info:</strong> <span
                                    id="detalleObservaciones"></span></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="modal-acciones">
                            <button id="btnClaseCompletada" class="btn btn-success d-flex align-items-center gap-1 justify-content-center px-3 py-2" title="Mark as Completed">
                                <i class="bi bi-check2-circle"></i>
                                <span class="d-none d-sm-inline">Mark as Completed</span>
                            </button>
                            <button id="btnEditarClase" class="btn btn-warning text-white" title="Edit Class">
                                <i class="bi bi-pencil-square"></i>
                                <span class="texto-responsive">Edit</span>
                            </button>
                            <button id="btnEliminarClase" class="btn btn-danger" title="Delete Class">
                                <i class="bi bi-trash3-fill"></i>
                                <span class="texto-responsive">Delete</span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>


        <!-- Modal to Assign Class -->
        <div class="modal fade" id="modalAsignarClase" tabindex="-1" aria-labelledby="modalAsignarClaseLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Assign New Class
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="formAsignarClase">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="instructor" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-chalkboard-teacher text-primary"></i>Instructor 
                                    </label>
                                    <select id="instructor" class="form-select" required name="profesor"></select>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-calendar-alt text-primary"></i>Date 
                                    </label>
                                    <input type="date" id="fecha" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="hora_inicio" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-clock text-secondary"></i>Start Time 
                                    </label>
                                    <input type="time" id="hora_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="hora_fin" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-clock text-secondary"></i>End Time 
                                    </label>
                                    <input type="time" id="hora_fin" class="form-control" required>
                                </div>
                            </div>
                            <hr class="section-divider">
                            <h6 class="section-title">Participant Information</h6>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="alumno_nombre" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-user text-muted"></i>Name 
                                    </label>
                                    <input type="text" id="alumno_nombre" class="form-control" required name="alumno">
                                </div>
                                <div class="col-md-4">
                                    <label for="alumno_telefono" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-phone text-success"></i>Phone
                                    </label>
                                    <input type="tel" id="alumno_telefono" class="form-control" name="telefono_alumno">
                                </div>
                                <div class="col-md-4">
                                    <label for="alumno_email" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-envelope text-primary"></i>Email
                                    </label>
                                    <input type="email" id="alumno_email" class="form-control" name="email_alumno">
                            </div>
                                </div>
                            <hr class="section-divider">
                            <h6 class="section-title">Payment Details</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="pago_efectivo" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-money-bill-wave text-success"></i>Cash
                                    </label>
                                    <input type="number" id="pago_efectivo" class="form-control" name="pago_efectivo" min="0" step="0.01" value="0" oninput="actualizarTotalPagado()">
                                </div>
                                <div class="col-md-3">
                                    <label for="pago_tarjeta" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-credit-card text-danger"></i>Card
                                    </label>
                                    <input type="number" id="pago_tarjeta" class="form-control" name="pago_tarjeta" min="0" step="0.01" value="0" oninput="actualizarTotalPagado()">
                                </div>
                                <div class="col-md-3">
                                    <label for="total_pagado" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-dollar-sign text-info"></i>Total Paid
                                    </label>
                                    <input type="number" id="total_pagado" class="form-control" name="importePagado" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label for="tarifa_profesor" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-receipt text-secondary"></i>Instructor Fee 
                                    </label>
                                    <input type="number" id="tarifa_profesor" class="form-control" name="tarifa_hora" step="0.01" min="0" required>
                                </div>
                            </div>
                              <hr class="section-divider">
                              <h6 class="section-title">Additional Info</h6>
                              <div class="mb-3">
                                
                                <textarea id="observaciones" class="form-control" rows="3" name="observaciones"></textarea>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="btnGuardarClase" type="button" class="btn btn-primary" onclick="asignarClase()">Save Class</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal to Edit Class -->
        <div class="modal fade" id="modalEditarClase" tabindex="-1" aria-labelledby="modalEditarClaseLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary">
                            <i class="fas fa-edit me-2"></i>Edit Class
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form id="formEditarClase">
                            <input type="hidden" id="clase_id">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="edit_profesor" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-chalkboard-teacher text-primary"></i>Instructor 
                                    </label>
                                    <select id="edit_profesor" class="form-select" required name="editar_profesor"></select>
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_fecha" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-calendar-alt text-primary"></i>Date 
                                    </label>
                                    <input type="date" id="edit_fecha" class="form-control" required name="editar_fecha">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="edit_hora_inicio" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-clock text-secondary"></i>Start Time 
                                    </label>
                                    <input type="time" id="edit_hora_inicio" class="form-control" required name="editar_hora_inicio">
                                </div>
                                <div class="col-md-6">
                                    <label for="edit_hora_fin" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-clock text-secondary"></i>End Time 
                                    </label>
                                    <input type="time" id="edit_hora_fin" class="form-control" required name="editar_hora_fin">
                                </div>
                            </div>
                            <hr class="section-divider">
<h6 class="section-title">Participant Information</h6>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="edit_alumno_nombre" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-user text-muted"></i>Name 
                                    </label>
                                    <input type="text" id="edit_alumno_nombre" class="form-control" required name="editar_alumno">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_alumno_telefono" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-phone text-success"></i>Phone
                                    </label>
                                    <input type="text" id="edit_alumno_telefono" class="form-control" name="editar_telefono_alumno">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_alumno_email" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-envelope text-primary"></i>Email
                                    </label>
                                    <input type="email" id="edit_alumno_email" class="form-control" name="editar_email_alumno">
                                </div>
                            </div>
                            <hr class="section-divider">
<h6 class="section-title">Payment Details</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="edit_pago_efectivo" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-money-bill-wave text-success"></i>Cash
                                    </label>
                                    <input type="number" id="edit_pago_efectivo" class="form-control" name="editar_pago_efectivo" min="0" step="0.01" value="0" oninput="actualizarTotalEditado()">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_pago_tarjeta" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-credit-card text-danger"></i>Card
                                    </label>
                                    <input type="number" id="edit_pago_tarjeta" class="form-control" name="editar_pago_tarjeta" min="0" step="0.01" value="0" oninput="actualizarTotalEditado()">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_total_pagado" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-dollar-sign text-info"></i>Total Paid
                                    </label>
                                    <input type="number" id="edit_total_pagado" class="form-control" name="editar_importe_pagado" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_tarifa_profesor" class="form-label d-flex align-items-center gap-1">
                                        <i class="fas fa-receipt text-secondary"></i>Instructor Fee 
                                    </label>
                                    <input type="number" id="edit_tarifa_profesor" class="form-control" name="editar_tarifa_hora" step="0.01" min="0" required>
                                </div>
                            </div>

                            <hr class="section-divider">
<h6 class="section-title">Additional Info</h6>
                            <div class="mb-3">
                                <textarea id="edit_observaciones" class="form-control" rows="3" name="editar_observaciones"></textarea>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button id="btnGuardarEdicion" type="button" class="btn btn-primary" onclick="guardarEdicionClase()">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>






        <!-- Payments Management Section -->
        <div id="pagos" class="seccion" style="display: none;">
            <h3>Payments Management</h3>

            <!-- Table of Pending Payments -->
            <h4 class="mt-4">Completed Classes (Total to Pay)</h4>
            <div class="table-responsive">
                <table id="tablaPagosPendientes" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Instructor</th>
                            <th>Scheduled Hours</th>
                            <th>Estimated Amount (€)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaPagosPendientes">
                        <!-- Pending payments will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Table of Registered Payments -->
            <h4 class="mt-4">Registered Payments</h4>
            <div class="table-responsive">
                <table id="tablaPagosRealizados" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Payment Date</th>
                            <th>Instructor</th>
                            <th>Worked Hours</th>
                            <th>Paid Amount (€)</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaPagosRealizados">
                        <!-- Registered payments will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Payment Detail Modal -->
            <div class="modal fade" id="modalDetallePago" tabindex="-1" aria-labelledby="modalDetallePagoLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content bg-white text-dark">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDetallePagoLabel">Payment Detail</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p><strong>Instructor:</strong> <span id="modalPagoProfesor" class="profesor"></span></p>
                            <p><strong>Paid Hours:</strong> <span id="modalPagoHoras" class="horas"></span></p>
                            <p><strong>Total (€):</strong> <span id="modalPagoTotal" class="total"></span></p>
                            <p><strong>Payment Date:</strong> <span id="modalPagoFecha" class="fecha"></span></p>

                            <hr>
                            <h6>Included Classes</h6>
                            <div class="table-responsive">
                                <table id="tablaDetallePagoProfesor" class="table table-striped table-white">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Participant</th>
                                            <th>Duration (hrs)</th>
                                            <th>Rate/hour (€)</th>
                                            <th>Amount (€)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyDetallePagoProfesor">
                                        <!-- Dynamically inserted -->
                                    </tbody>
                                </table>
                            </div>
                            <button id="btnDescargarPDF" class="btn btn-primary mt-3">Download Receipt</button>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Pending Payment Detail Modal -->
        <div class="modal fade" id="modalDetalleClasesPendientes" tabindex="-1"
            aria-labelledby="modalDetalleClasesPendientesLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-white text-dark">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalDetalleClasesPendientesLabel">Payment Preview</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Instructor:</strong> <span id="modalPendienteProfesor"></span></p>
                        <p><strong>Total Hours:</strong> <span id="modalPendienteHoras"></span></p>
                        <p><strong>Total to Pay (€):</strong> <span id="modalPendienteTotal"></span></p>
                        <hr>
                        <h6>Included Classes</h6>
                        <div class="table-responsive">
                            <table id="tablaDetalleClasesPendientes" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Participant</th>
                                        <th>Duration (hrs)</th>
                                        <th>Rate/hour (€)</th>
                                        <th>Amount (€)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDetalleClasesPendientes">
                                    <!-- Dynamic -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="btnRegisterPayment" class="btn btn-success">
                            Register Payment
                        </button>
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>



        <!-- 🟩 Monthly Summary Table -->
        <section id="seccionFacturacion" class="seccion container mt-4" style="display: none;">
            <h3>Monthly Billing</h3>
            <table class="table table-striped" id="tablaFacturacion">
                <thead>
                    <tr>
                        <th>Month / Year</th>
                        <th>Total Billed</th>
                        <th>View Details</th>
                    </tr>
                </thead>
                <tbody id="tbodyFacturacion">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </section>


        <!-- 🟦 Modal Detalle del Mes -->
        <div class="modal fade" id="modalDetalleFacturacion" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tituloDetalleFacturacion">Detalle de Facturación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <!-- 🔎 Filtro -->
                        <div class="mb-3">
                            <input type="text" class="form-control" id="filtroDetalle"
                                placeholder="Filter by name, date or amount">
                        </div>

                        <!-- 📋 Detail Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tablaDetalleMes">
                                <thead class="table-primary text-center align-middle">
                                    <tr>
                                        <th>Date</th>
                                        <th>Instructor</th>
                                        <th>Participant</th>
                                        <th>Amount to Instructor (€)</th>
                                        <th>Cash Payment (€)</th>
                                        <th>Card Payment (€)</th>
                                        <th>Total Billed (€)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyDetalleMes">
                                    <!-- Loaded via JS -->
                                </tbody>
                            </table>
                        </div>



                        <div class="mt-4 border-top pt-3 text-end">
                            <p><strong>Total Billed:</strong> € <span id="totalFacturadoMes">—</span></p>
                            <p><strong>Total in Cash:</strong> € <span id="totalEfectivoMes">—</span></p>
                            <p><strong>Total by Card:</strong> € <span id="totalTarjetaMes">—</span></p>
                            <p><strong>Amount to Instructors:</strong> € <span id="totalProfesoresMes">—</span></p>
                            <h5 class="text-success"><strong>Net Profit:</strong> € <span id="gananciaMes">—</span></h5>
                        </div>

                        <!-- 📤 Export -->
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                            <button class="btn btn-success px-4" id="btnExportExcel">📗 Export to Excel</button>
                            <button class="btn btn-danger px-4" id="btnExportPDF">📕 Export to PDF</button>
                        </div>


                    </div>
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



    <!-- Scripts -->
    <script>
    let calendarInstancia = null;

    function mostrarSeccion(seccion) {
        // Oculta todas las secciones
        document.querySelectorAll('.seccion').forEach(div => div.style.display = 'none');

        // Muestra el loader local
        var loader = document.getElementById('loader');
        if (loader) loader.style.display = 'flex';

        // Referencia al target
        const target = document.getElementById(seccion);

        if (seccion === 'clases') {
            // Espera a que FullCalendar esté listo antes de mostrar la sección
            setTimeout(() => {
                if (!calendarInstancia) {
                    calendarInstancia = inicializarCalendario();
                } else {
                    calendarInstancia.updateSize();
                }
                if (target) target.style.display = 'block';
                calendarInstancia.updateSize();

                if (loader) loader.style.display = 'none';
            }, 120); // Ajustá este valor según la velocidad real de tu inicialización
        } else if (seccion === 'pagos') {
            // Carga pagos y espera un toque antes de mostrar
            cargarPagos();
            setTimeout(() => {
                if (target) target.style.display = 'block';
                if (loader) loader.style.display = 'none';
            }, 180); // Ajustá este valor si es necesario
        } else {
            // Para las demás secciones, cambio inmediato (con loader un toque)
            setTimeout(() => {
                if (target) target.style.display = 'block';
                if (loader) loader.style.display = 'none';
            }, 100);
        }
    }
    </script>


    <!-- jQuery (debe ir antes que DataTables y tus scripts) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
    jQuery.extend(jQuery.fn.dataTable.ext.type.order, {
        "fecha-euro-pre": function(fecha) {
            const partes = fecha.split("-");
            return new Date(partes[2], partes[1] - 1, partes[0]).getTime();
        }
    });
    </script>



    <!-- ✅ FullCalendar versión estable que FUNCIONA -->
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/index.global.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/list@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.4/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.4/locales-all.global.min.js"></script>


    <!-- Bootstrap + JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/verificar_sesion.js"></script>
    <script src="js/admin_profesores.js?v=2.0"></script>
    <script src="js/admin_clases.js?v=2.0"></script>
    <script src="js/admin_pagos.js?v=2.0"></script>
    <script src="js/admin_facturacion.js?v=1.0"></script>

    <script>
    $(document).ready(function() {
        $('#modalDetalleFacturacion').on('shown.bs.modal', function() {
            if (!$.fn.DataTable.isDataTable('#tablaDetalleMes')) {
                $('#tablaDetalleMes').DataTable({
                    pageLength: 10,
                    lengthChange: false,
                    ordering: true,
                    order: [
                        [0, 'desc']
                    ],
                    columnDefs: [{
                        type: 'fecha-euro',
                        targets: 0
                    }],
                    searching: false,
                    language: {
                        paginate: {
                            previous: "Previous",
                            next: "Next"
                        },
                        emptyTable: "No data available in the table"
                    },
                    infoCallback: function(settings, start, end, max, total, pre) {
                        return `Showing ${end} classes of ${total}`;
                    }

                });
            }
        });

        // ✅ Destruir DataTable al cerrar el modal (limpio para recargar luego)
        $('#modalDetalleFacturacion').on('hidden.bs.modal', function() {
            if ($.fn.DataTable.isDataTable('#tablaDetalleMes')) {
                $('#tablaDetalleMes').DataTable().destroy();
            }
        });
    });
    // Esto hace disponible el nombre para todo tu JavaScript
    const NOMBRE_ESCUELA = <?php echo json_encode($config['nombre_escuela']); ?>;
    </script>



    <!-- SheetJS para Excel -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- jsPDF y AutoTable para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>



    <script>
    const entries = performance.getEntriesByType("navigation");
    if (entries.length && entries[0].type === "back_forward") {
        location.reload(); // fuerza recarga real
    }
    </script>

    <script>
    window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('loader').style.display = 'none';
        document.body.style.display = 'block';
    });
    </script>


</body>

</html>