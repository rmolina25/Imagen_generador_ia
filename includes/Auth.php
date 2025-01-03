<?php
class Auth {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function registrar($nombre, $email, $password, $pregunta_seguridad, $respuesta_seguridad) {
        try {
            // Verificar si el email ya existe
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                throw new Exception("El email ya está registrado");
            }

            // Hash de la contraseña y la respuesta de seguridad
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $respuesta_hash = password_hash(strtolower(trim($respuesta_seguridad)), PASSWORD_DEFAULT);

            // Insertar el nuevo usuario
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, pregunta_seguridad, respuesta_seguridad) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$nombre, $email, $password_hash, $pregunta_seguridad, $respuesta_hash])) {
                return true;
            }
            
            throw new Exception("Error al registrar el usuario");
        } catch (PDOException $e) {
            throw new Exception("Error en el registro: " . $e->getMessage());
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en el login: " . $e->getMessage());
        }
    }

    public function logout() {
        session_destroy();
    }

    public function estaAutenticado() {
        return isset($_SESSION['usuario_id']);
    }

    public function emailExists($email) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result !== false;
    }

    public function saveResetToken($email, $token, $expiry) {
        $sql = "UPDATE usuarios SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token, $expiry, $email]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPreguntaSeguridad($email) {
        $sql = "SELECT pregunta_seguridad FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verificarRespuestaSeguridad($email, $respuesta) {
        $sql = "SELECT respuesta_seguridad FROM usuarios WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            return password_verify(strtolower(trim($respuesta)), $usuario['respuesta_seguridad']);
        }
        return false;
    }

    public function actualizarPassword($email, $nueva_password) {
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET password = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$password_hash, $email]);
    }
} 