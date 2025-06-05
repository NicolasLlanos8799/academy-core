<?php
// ===========================================================
// Archivo: db.php
// Funcionalidad: Configura la conexión a la base de datos MySQL 
// utilizando PDO para una gestión segura y eficiente.
// ===========================================================

// Configuración de conexión a la base de datos
$host = 'localhost';      // Servidor de la base de datos (normalmente 'localhost')
$dbname = 'surf_school';  // Nombre de la base de datos
$username = 'root';       // Usuario de la base de datos (cambiar si es diferente)
$password = '';           // Contraseña del usuario de la base de datos (dejar en blanco si no tiene)

// Intentar establecer la conexión usando PDO
try {
    // Crear una instancia de la conexión PDO con UTF-8 habilitado
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si hay un error en la conexión, se detiene la ejecución y se muestra un mensaje
    die("Error en la conexión: " . $e->getMessage());
}
?>
