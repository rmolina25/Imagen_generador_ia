<?php
session_start();
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

$db = new Database();
$auth = new Auth($db->getConnection());
$mensaje_error = '';
$mensaje_exito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    $pregunta_seguridad = $_POST['pregunta_seguridad'] ?? '';
    $respuesta_seguridad = $_POST['respuesta_seguridad'] ?? '';

    try {
        if ($password !== $confirmar_password) {
            throw new Exception("Las contraseñas no coinciden");
        }

        if (empty($pregunta_seguridad) || empty($respuesta_seguridad)) {
            throw new Exception("La pregunta y respuesta de seguridad son obligatorias");
        }

        if ($auth->registrar($nombre, $email, $password, $pregunta_seguridad, $respuesta_seguridad)) {
            $mensaje_exito = "Registro exitoso. Ya puedes iniciar sesión.";
        }
    } catch (Exception $e) {
        $mensaje_error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Generador de Diseños</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.5rem;
        }

        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
        }

        .register-link {
            color: var(--primary-color);
            text-decoration: none;
        }

        .register-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Registro
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($mensaje_error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($mensaje_exito)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($mensaje_exito); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" name="confirmar_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pregunta de Seguridad</label>
                            <select class="form-control" name="pregunta_seguridad" required>
                                <option value="">Selecciona una pregunta</option>
                                <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                                <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                                <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                                <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
                                <option value="¿Cuál fue el nombre de tu primera escuela?">¿Cuál fue el nombre de tu primera escuela?</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Respuesta de Seguridad</label>
                            <input type="text" class="form-control" name="respuesta_seguridad" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            Registrarse
                        </button>
                        <div class="text-center">
                            <span>¿Ya tienes cuenta?</span>
                            <a href="login.php" class="register-link">Inicia Sesión</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 