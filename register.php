<?php
require_once 'config/config.php';
require_once 'config/Database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Error de validación CSRF');
    }

    try {
        $db = new Database();
        $conn = $db->connect();

        // Validar campos
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (empty($full_name)) $errors[] = "El nombre completo es requerido";
        if (empty($username)) $errors[] = "El nombre de usuario es requerido";
        if (empty($email)) $errors[] = "El correo electrónico es requerido";
        if (empty($password)) $errors[] = "La contraseña es requerida";
        if ($password !== $password_confirm) $errors[] = "Las contraseñas no coinciden";
        if (strlen($password) < 8) $errors[] = "La contraseña debe tener al menos 8 caracteres";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El correo electrónico no es válido";

        // Verificar si el usuario ya existe
        $existing_user = $db->query(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$username, $email]
        )->fetch();

        if ($existing_user) {
            $errors[] = "El usuario o correo electrónico ya está registrado";
        }

        // Si no hay errores, crear el usuario
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $db->query(
                "INSERT INTO users (username, password, email, full_name, role) 
                 VALUES (?, ?, ?, ?, 'member')",
                [$username, $hashed_password, $email, $full_name]
            );

            $success = true;
        }
    } catch (Exception $e) {
        $errors[] = "Error al procesar el registro. Por favor, intente más tarde.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    <i class="fas fa-user-plus text-indigo-600 text-4xl mb-4"></i>
                    <div class="mt-2">Registro de Usuario</div>
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    <?php echo SITE_NAME; ?>
                </p>
            </div>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <p>¡Registro exitoso! Ahora puedes <a href="login.php" class="font-bold underline">iniciar sesión</a>.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form class="mt-8 space-y-6" method="POST" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input id="full_name" name="full_name" type="text" required
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="Juan Pérez">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Usuario</label>
                        <input id="username" name="username" type="text" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="usuario123">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                        <input id="email" name="email" type="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="correo@ejemplo.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input id="password" name="password" type="password" required
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="••••••••">
                        <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                        <input id="password_confirm" name="password_confirm" type="password" required
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="••••••••">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        Registrarse
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="/" class="text-sm text-indigo-600 hover:text-indigo-500">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
                </a>
                <?php if (!$success): ?>
                <span class="mx-2 text-gray-500">|</span>
                <a href="login.php" class="text-sm text-indigo-600 hover:text-indigo-500">
                    ¿Ya tienes cuenta? Inicia sesión <i class="fas fa-arrow-right ml-1"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const passwordConfirm = document.getElementById('password_confirm').value;
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return false;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 8 caracteres');
            return false;
        }
    });
    </script>
</body>
</html>
