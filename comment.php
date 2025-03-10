<?php
require_once 'config/config.php';
require_once 'config/Database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea una petición POST y que el usuario esté autenticado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Verificar que sea un usuario con rol "member"
try {
    $db = new Database();
    $conn = $db->connect();

    $user = $db->query(
        "SELECT role FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    )->fetch();

    if (!$user || $user['role'] !== 'member') {
        header('Location: index.php');
        exit();
    }

    // Obtener y validar datos del comentario
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $content = trim($_POST['content'] ?? '');

    if (!$post_id || empty($content)) {
        throw new Exception('Datos de comentario inválidos');
    }

    // Verificar que la publicación exista y esté publicada
    $post = $db->query(
        "SELECT id FROM posts WHERE id = ? AND status = 'published'",
        [$post_id]
    )->fetch();

    if (!$post) {
        throw new Exception('Publicación no encontrada');
    }

    // Insertar comentario
    $db->query(
        "INSERT INTO comments (post_id, user_id, content, status) 
         VALUES (?, ?, ?, 'pending')",
        [$post_id, $_SESSION['user_id'], $content]
    );

    // Redirigir con mensaje de éxito
    header('Location: index.php?message=comment_sent');
    exit();

} catch (Exception $e) {
    // Redirigir con mensaje de error
    header('Location: index.php?error=comment_error');
    exit();
}
