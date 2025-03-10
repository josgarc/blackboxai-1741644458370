<?php
require_once 'config/config.php';
require_once 'config/Database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea una petición POST y que el usuario esté autenticado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
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
        http_response_code(403);
        echo json_encode(['error' => 'No autorizado']);
        exit();
    }

    // Obtener y validar el ID de la publicación
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    if (!$post_id) {
        throw new Exception('ID de publicación inválido');
    }

    // Verificar que la publicación exista y esté publicada
    $post = $db->query(
        "SELECT id FROM posts WHERE id = ? AND status = 'published'",
        [$post_id]
    )->fetch();

    if (!$post) {
        throw new Exception('Publicación no encontrada');
    }

    // Verificar si el usuario ya dio like
    $existing_like = $db->query(
        "SELECT id FROM likes WHERE post_id = ? AND user_id = ?",
        [$post_id, $_SESSION['user_id']]
    )->fetch();

    // Comenzar transacción
    $conn->beginTransaction();

    if ($existing_like) {
        // Si ya existe el like, eliminarlo (unlike)
        $db->query(
            "DELETE FROM likes WHERE id = ?",
            [$existing_like['id']]
        );
        $action = 'unliked';
    } else {
        // Si no existe, crear el like
        $db->query(
            "INSERT INTO likes (post_id, user_id) VALUES (?, ?)",
            [$post_id, $_SESSION['user_id']]
        );
        $action = 'liked';
    }

    // Obtener el nuevo conteo de likes
    $likes_count = $db->query(
        "SELECT COUNT(*) as count FROM likes WHERE post_id = ?",
        [$post_id]
    )->fetch()['count'];

    // Confirmar transacción
    $conn->commit();

    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes_count' => $likes_count
    ]);

} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($conn)) {
        $conn->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'error' => 'Error al procesar la acción',
        'message' => $e->getMessage()
    ]);
}
