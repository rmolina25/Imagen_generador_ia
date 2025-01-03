<?php
session_start();
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Auth.php';

$db = new Database();
$auth = new Auth($db->getConnection());
$mensaje = '';
$tipo_mensaje = '';
$mostrar_pregunta = false;
$mostrar_password = false;
$pregunta_seguridad = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['respuesta']) && !isset($_POST['password'])) {
        $email = $_POST['email'] ?? '';
        
        try {
            $usuario = $auth->obtenerPreguntaSeguridad($email);
            if ($usuario) {
                $mostrar_pregunta = true;
                $pregunta_seguridad = $usuario['pregunta_seguridad'];
                $_SESSION['reset_email'] = $email;
            } else {
                $tipo_mensaje = 'danger';
                $mensaje = "No existe una cuenta con ese correo electrónico.";
            }
        } catch (Exception $e) {
            $tipo_mensaje = 'danger';
            $mensaje = $e->getMessage();
        }
    } elseif (isset($_POST['respuesta']) && !isset($_POST['password'])) {
        $email = $_SESSION['reset_email'] ?? '';
        $respuesta = $_POST['respuesta'] ?? '';
        
        try {
            if ($auth->verificarRespuestaSeguridad($email, $respuesta)) {
                $mostrar_password = true;
                $mostrar_pregunta = false;
            } else {
                $tipo_mensaje = 'danger';
                $mensaje = "Respuesta incorrecta.";
                $mostrar_pregunta = true;
                $usuario = $auth->obtenerPreguntaSeguridad($email);
                $pregunta_seguridad = $usuario['pregunta_seguridad'];
            }
        } catch (Exception $e) {
            $tipo_mensaje = 'danger';
            $mensaje = $e->getMessage();
        }
    } elseif (isset($_POST['password']) && isset($_POST['password_confirm'])) {
        $email = $_SESSION['reset_email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $password_confirm) {
            $tipo_mensaje = 'danger';
            $mensaje = "Las contraseñas no coinciden.";
            $mostrar_password = true;
        } elseif (strlen($password) < 6) {
            $tipo_mensaje = 'danger';
            $mensaje = "La contraseña debe tener al menos 6 caracteres.";
            $mostrar_password = true;
        } else {
            try {
                if ($auth->actualizarPassword($email, $password)) {
                    $tipo_mensaje = 'success';
                    $mensaje = "Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.";
                    unset($_SESSION['reset_email']);
                }
            } catch (Exception $e) {
                $tipo_mensaje = 'danger';
                $mensaje = $e->getMessage();
                $mostrar_password = true;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Generador de Diseños</title>
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

        .recovery-container {
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

        .login-link {
            color: var(--primary-color);
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="recovery-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Recuperar Contraseña
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$mostrar_pregunta && !$mostrar_password): ?>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required>
                                <small class="form-text text-muted">
                                    Ingresa el email asociado a tu cuenta.
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Continuar
                            </button>
                            <div class="text-center">
                                <a href="login.php" class="login-link">
                                    Volver al inicio de sesión
                                </a>
                            </div>
                        </form>
                    <?php elseif ($mostrar_pregunta): ?>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label">Pregunta de Seguridad:</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($pregunta_seguridad); ?></p>
                                <input type="text" class="form-control" name="respuesta" required 
                                       placeholder="Tu respuesta">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Verificar Respuesta
                            </button>
                            <div class="text-center">
                                <a href="login.php" class="login-link">
                                    Volver al inicio de sesión
                                </a>
                            </div>
                        </form>
                    <?php elseif ($mostrar_password): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control" name="password" 
                                       required minlength="6">
                                <small class="form-text text-muted">
                                    Mínimo 6 caracteres
                                </small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="password_confirm" 
                                       required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Actualizar Contraseña
                            </button>
                            <div class="text-center">
                                <a href="login.php" class="login-link">
                                    Volver al inicio de sesión
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 