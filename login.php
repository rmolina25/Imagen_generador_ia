<?php
session_start();
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

$db = new Database();
$auth = new Auth($db->getConnection());
$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        if ($auth->login($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $mensaje_error = "Credenciales inválidas";
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
    <title>Login - Generador de Diseños</title>
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
                        <i class="fas fa-paint-brush me-2"></i>
                        Generador de Diseños
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($mensaje_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($mensaje_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                            <div class="mt-1">
                                <a href="recuperar-password.php" class="register-link small">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            Iniciar Sesión
                        </button>
                        <div class="text-center">
                            <span>¿No tienes cuenta?</span>
                            <a href="registro.php" class="register-link">Regístrate</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 