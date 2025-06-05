function verificarSesion(callback) {
    fetch('verificar_sesion.php')
        .then(res => res.json())
        .then(data => {
            if (data.activa === true) {
                if (typeof callback === 'function') callback(true);
            } else {
                window.location.href = 'login.php?expirada=1';
            }
        })
        .catch(() => {
            // We don't redirect on network errors to avoid false session terminations
            alert("Connection error while verifying session. Check your internet or reload the page.");
        });
}

function verificarSesionYMostrar(seccion) {
    verificarSesion(() => mostrarSeccion(seccion));
}
