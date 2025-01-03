-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS ia_image_generator;
USE ia_image_generator;

-- Tabla de usuarios
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    api_credits INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Tabla de imágenes generadas
CREATE TABLE generated_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    prompt_text TEXT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    size VARCHAR(20) NOT NULL,
    style VARCHAR(50),
    generation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'deleted', 'archived') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de historial de prompts
CREATE TABLE prompt_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    prompt_text TEXT NOT NULL,
    success_rate FLOAT DEFAULT 0,
    use_count INT DEFAULT 1,
    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de configuraciones de usuario
CREATE TABLE user_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    default_size VARCHAR(20) DEFAULT '512x512',
    default_style VARCHAR(50) DEFAULT 'natural',
    save_history BOOLEAN DEFAULT TRUE,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de tokens de recuperación de contraseña
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de registro de uso de API
CREATE TABLE api_usage_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    endpoint VARCHAR(100) NOT NULL,
    credits_used INT NOT NULL,
    response_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de estilos disponibles
CREATE TABLE available_styles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    style_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    credits_cost INT DEFAULT 1
);

-- Insertar estilos predeterminados
INSERT INTO available_styles (style_name, description, credits_cost) VALUES
('natural', 'Estilo fotográfico natural', 1),
('artistic', 'Estilo artístico y creativo', 1),
('cartoon', 'Estilo de dibujo animado', 1),
('oil-painting', 'Estilo de pintura al óleo', 2),
('sketch', 'Estilo de boceto a lápiz', 1),
('watercolor', 'Estilo de acuarela', 2),
('digital-art', 'Arte digital moderno', 2),
('anime', 'Estilo anime japonés', 1),
('3d-render', 'Renderizado 3D realista', 3),
('minimalist', 'Estilo minimalista', 1);

-- Índices para optimización
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_image_user ON generated_images(user_id);
CREATE INDEX idx_prompt_user ON prompt_history(user_id);
CREATE INDEX idx_password_reset_token ON password_resets(token);
CREATE INDEX idx_api_usage_user ON api_usage_log(user_id);

-- Trigger para actualizar créditos de usuario
DELIMITER //
CREATE TRIGGER after_image_generation
AFTER INSERT ON generated_images
FOR EACH ROW
BEGIN
    UPDATE users 
    SET api_credits = api_credits - 1 
    WHERE id = NEW.user_id AND api_credits > 0;
END//
DELIMITER ;

-- Vista para estadísticas de usuario
CREATE VIEW user_statistics AS
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(gi.id) as total_images,
    u.api_credits as remaining_credits,
    MAX(gi.generation_date) as last_generation
FROM users u
LEFT JOIN generated_images gi ON u.id = gi.user_id
GROUP BY u.id;
