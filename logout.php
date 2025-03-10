<?php
require_once 'config/config.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir la sesión
session_destroy();

// Redirigir a la página principal
header('Location: index.php');
exit();
