<?php
session_start();
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/ImagenManager.php';
require_once 'includes/Auth.php';

$db = new Database();
$imagenManager = new ImagenManager($db->getConnection());
$auth = new Auth($db->getConnection());
$mensaje_error = '';

// Verificar si el usuario está autenticado
if (!$auth->estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// Definir las opciones para el select de descripción
$opciones_descripcion = [
    '' => 'Seleccione un estilo promocional',
    // Estilos Modernos
    'neon_promo' => 'Promoción Neón Brillante',
    'super_sale' => 'Super Oferta Dinámica',
    'modern_flash' => 'Flash Promocional Moderno',
    'digital_event' => 'Evento Digital Vibrante',
    
    // Estilos Comerciales
    'mega_discount' => 'Mega Descuento Explosivo',
    'special_offer' => 'Oferta Especial Premium',
    'limited_time' => 'Tiempo Limitado Urgente',
    'exclusive_deal' => 'Oferta Exclusiva VIP',
    
    // Estilos Tecnológicos
    'cyber_deal' => 'Oferta Cyber Style',
    'tech_promo' => 'Promoción Tech Futurista',
    'digital_wave' => 'Onda Digital Moderna',
    'smart_deal' => 'Smart Deal Tecnológico',
    
    // Estilos Urbanos
    'urban_promo' => 'Promoción Urbana Street',
    'graffiti_style' => 'Estilo Graffiti Urbano',
    'street_art' => 'Arte Callejero Promocional',
    'urban_vibe' => 'Vibra Urbana Moderna',
    
    // Estilos Minimalistas
    'minimal_clean' => 'Minimalista Limpio',
    'simple_elegant' => 'Simple y Elegante',
    'clean_design' => 'Diseño Limpio Moderno',
    'minimal_pro' => 'Profesional Minimalista',
    
    // Estilos con Gradientes
    'gradient_flash' => 'Flash con Gradientes',
    'gradient_flow' => 'Flujo de Gradientes',
    'color_blend' => 'Mezcla de Colores Suaves',
    'rainbow_promo' => 'Promoción Arcoíris',
    
    // Estilos Festivos
    'black_friday' => 'Estilo Black Friday',
    'cyber_monday' => 'Cyber Monday Special',
    'holiday_promo' => 'Promoción Festiva',
    'season_sale' => 'Rebajas de Temporada',
    
    // Estilos Corporativos
    'business_pro' => 'Profesional Corporativo',
    'corporate_clean' => 'Corporativo Elegante',
    'executive_style' => 'Estilo Ejecutivo',
    'business_modern' => 'Negocio Moderno',
    
    // Estilos Creativos
    'artistic_promo' => 'Promoción Artística',
    'creative_splash' => 'Splash Creativo',
    'abstract_art' => 'Arte Abstracto Promo',
    'brush_style' => 'Estilo Pincelada',
    
    // Estilos Retro
    'retro_vintage' => 'Retro Vintage',
    'old_school' => 'Old School Classic',
    'vintage_modern' => 'Vintage Modernizado',
    'retro_wave' => 'Retro Wave',
    
    // Estilos 3D
    '3d_modern' => '3D Moderno',
    '3d_geometric' => 'Geométrico 3D',
    '3d_abstract' => 'Abstracto 3D',
    'depth_design' => 'Diseño con Profundidad',
    
    // Estilos Temáticos
    'gaming_promo' => 'Gaming y E-Sports',
    'music_event' => 'Evento Musical',
    'food_promo' => 'Promoción Gastronómica',
    'fashion_style' => 'Estilo Moda',
    
    // Estilos Experimentales
    'glitch_effect' => 'Efecto Glitch',
    'liquid_design' => 'Diseño Líquido',
    'neon_splash' => 'Splash Neón',
    'cosmic_style' => 'Estilo Cósmico'
];

// Procesar eliminación
if (isset($_POST['eliminar']) && isset($_POST['id'])) {
    try {
        if ($imagenManager->eliminarCaratula($_POST['id'])) {
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $mensaje_error = $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $descripcion = isset($_POST['descripcion']) ? $opciones_descripcion[$_POST['descripcion']] : '';
    
    if (!empty($nombre)) {
        try {
            if ($imagenManager->generarCaratula($nombre, $descripcion, $_SESSION['usuario_id'])) {
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {
            $mensaje_error = $e->getMessage();
        }
    } else {
        $mensaje_error = "Por favor ingrese un nombre para la carátula";
    }
}

$caratulas = $imagenManager->obtenerTodasLasCaratulas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Diseños Promocionales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --dark-color: #1f2937;
            --light-color: #f3f4f6;
        }

        body {
            background: linear-gradient(135deg, #f6f8ff 0%, #e9ecef 100%);
            min-height: 100vh;
            position: relative;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            border-color: #4f46e5;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.3);
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 1rem 0;
        }

        .promo-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .promo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .promo-image-container {
            position: relative;
            width: 100%;
            padding-top: 100%;
        }

        .promo-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .promo-info {
            background: linear-gradient(0deg, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.7) 70%, transparent 100%);
            padding: 1.5rem;
        }

        .promo-title {
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: white;
        }

        .promo-description {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.9);
        }

        .promo-date {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            backdrop-filter: blur(4px);
        }

        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 3px;
        }

        /* Estilos para el select */
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234f46e5' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px;
            padding-right: 2.5rem;
        }

        /* Mejoras en la barra de progreso */
        .progress-container {
            backdrop-filter: blur(8px);
        }

        .progress-content {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }

        .progress {
            height: 0.8rem;
            border-radius: 1rem;
            background-color: #e5e7eb;
        }

        .progress-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }

        .step {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
        }

        .step i {
            font-size: 0.8rem;
        }

        .step.completed i {
            color: #10b981;
        }

        .step.active i {
            color: var(--primary-color);
        }

        .promo-actions {
            position: absolute;
            top: 1rem;
            left: 1rem;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .promo-card:hover .promo-actions {
            opacity: 1;
        }

        .action-btn {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn.download {
            background: rgba(79, 70, 229, 0.9);
        }

        .action-btn.delete {
            background: rgba(220, 38, 38, 0.9);
        }

        /* Estilos para el modal de progreso */
        #progressModal .modal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        }

        #progressModal .progress {
            background-color: #e5e7eb;
            border-radius: 1rem;
        }

        #progressModal .progress-bar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 1rem;
        }

        #progressModal .step {
            padding: 0.75rem 0;
            color: #6b7280;
            display: flex;
            align-items: center;
        }

        #progressModal .step.active {
            color: var(--primary-color);
            font-weight: 500;
        }

        #progressModal .step.completed {
            color: #10b981;
        }

        #progressModal .step i {
            font-size: 0.75rem;
            margin-right: 0.5rem;
        }

        /* Agregar un patrón de fondo sutil */
        .background-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25px 25px, rgba(0,0,0,0.02) 2%, transparent 0%),
                radial-gradient(circle at 75px 75px, rgba(0,0,0,0.02) 2%, transparent 0%);
            background-size: 100px 100px;
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="background-pattern"></div>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-paint-brush me-2"></i>
                Generador de Diseños
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <span class="nav-link text-white">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </span>
                    </li>
                    <li class="nav-item ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-2"></i>
                                Opciones
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="perfil.php">
                                        <i class="fas fa-user-circle me-2"></i>
                                        Mi Perfil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Formulario -->
        <div class="form-container">
            <h2 class="section-title">Crear Nuevo Diseño</h2>
            
            <?php if (!empty($mensaje_error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($mensaje_error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="generatorForm" onsubmit="return showProgress()">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Título de la Promoción</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required
                           placeholder="Ingresa el título de tu promoción">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Estilo de Diseño</label>
                    <select class="form-control" id="descripcion" name="descripcion">
                        <?php foreach ($opciones_descripcion as $valor => $texto): ?>
                            <option value="<?php echo htmlspecialchars($valor); ?>">
                                <?php echo htmlspecialchars($texto); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-magic me-2"></i>
                    Generar Diseño
                </button>
            </form>
        </div>

        <!-- Modal de Progreso -->
        <div class="modal fade" id="progressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <h4 class="text-center mb-4">Generando tu diseño</h4>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%"
                                 id="progressBar">0%</div>
                        </div>
                        <div class="progress-steps">
                            <div class="step" id="step1">
                                <i class="fas fa-circle me-2"></i> Iniciando generación
                            </div>
                            <div class="step" id="step2">
                                <i class="fas fa-circle me-2"></i> Procesando diseño
                            </div>
                            <div class="step" id="step3">
                                <i class="fas fa-circle me-2"></i> Aplicando estilos
                            </div>
                            <div class="step" id="step4">
                                <i class="fas fa-circle me-2"></i> Finalizando
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Galería de diseños -->
        <h2 class="section-title">Diseños Generados</h2>
        <div class="grid-container">
            <?php if (!empty($caratulas)): ?>
                <?php foreach ($caratulas as $caratula): ?>
                    <div class="promo-card">
                        <div class="promo-image-container">
                            <img src="<?php echo htmlspecialchars($caratula['url_imagen']); ?>" 
                                 class="promo-image" 
                                 alt="<?php echo htmlspecialchars($caratula['nombre']); ?>">
                            
                            <!-- Botones de acción -->
                            <div class="promo-actions">
                                <a href="<?php echo htmlspecialchars($caratula['url_imagen']); ?>" 
                                   class="action-btn download" 
                                   download="diseño_<?php echo $caratula['id']; ?>.png"
                                   title="Descargar">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" 
                                        class="action-btn delete" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal<?php echo $caratula['id']; ?>"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="promo-date">
                                <i class="far fa-calendar-alt me-2"></i>
                                <?php echo date('d M Y', strtotime($caratula['fecha_creacion'])); ?>
                            </div>
                            <div class="promo-info">
                                <div class="promo-title">
                                    <?php echo htmlspecialchars($caratula['nombre']); ?>
                                </div>
                                <?php if (!empty($caratula['descripcion'])): ?>
                                    <div class="promo-description">
                                        <?php echo htmlspecialchars($caratula['descripcion']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de confirmación de eliminación -->
                    <div class="modal fade" id="deleteModal<?php echo $caratula['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirmar eliminación</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>¿Estás seguro de que deseas eliminar este diseño?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo $caratula['id']; ?>">
                                        <button type="submit" name="eliminar" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="fas fa-images fa-3x mb-3"></i>
                    <p>No hay diseños generados aún.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Agregar el script de progreso -->
    <script>
        function showProgress() {
            const progressModal = new bootstrap.Modal(document.getElementById('progressModal'), {
                backdrop: 'static',
                keyboard: false
            });
            const progressBar = document.getElementById('progressBar');
            let currentProgress = 0;
            
            // Mostrar el modal
            progressModal.show();
            
            function updateProgress() {
                if (currentProgress < 100) {
                    currentProgress += 1;
                    progressBar.style.width = currentProgress + '%';
                    progressBar.textContent = currentProgress + '%';
                    
                    if (currentProgress >= 25) {
                        document.getElementById('step1').classList.add('completed');
                        document.getElementById('step2').classList.add('active');
                    }
                    if (currentProgress >= 50) {
                        document.getElementById('step2').classList.add('completed');
                        document.getElementById('step3').classList.add('active');
                    }
                    if (currentProgress >= 75) {
                        document.getElementById('step3').classList.add('completed');
                        document.getElementById('step4').classList.add('active');
                    }
                    if (currentProgress >= 100) {
                        document.getElementById('step4').classList.add('completed');
                        // Opcional: cerrar el modal cuando termine
                        // setTimeout(() => progressModal.hide(), 500);
                    }
                }
            }
            
            document.getElementById('step1').classList.add('active');
            
            const interval = setInterval(() => {
                updateProgress();
                if (currentProgress >= 100) {
                    clearInterval(interval);
                }
            }, 600);
            
            return true;
        }
    </script>
</body>
</html> 