<?php
require_once 'Database.php';

class ImagenManager {
    private $db;
    private $api_key;
    
    public function __construct($db) {
        $this->db = $db;
        $this->api_key = OPENAI_API_KEY;
        set_time_limit(120);
    }

    public function generarCaratula($nombre, $descripcion, $usuario_id = null) {
        try {
            if (empty($this->api_key)) {
                throw new Exception("API key no configurada");
            }

            // Prompt mejorado con énfasis en la precisión del texto
            $prompt = "Crea un diseño promocional para redes sociales con estas instrucciones ESTRICTAS:\n";
            $prompt .= "1. TEXTO PRINCIPAL: Usar ÚNICAMENTE el texto '{$nombre}' - NO AGREGAR NI MODIFICAR ESTE TEXTO.\n";
            $prompt .= "2. TIPOGRAFÍA: Usar una fuente moderna, grande y 100% legible.\n";
            $prompt .= "3. RESTRICCIONES:\n";
            $prompt .= "   - NO añadir palabras adicionales\n";
            $prompt .= "   - NO añadir texto decorativo\n";
            $prompt .= "   - NO modificar el texto principal\n";
            $prompt .= "   - NO usar texto en inglés\n";
            
            // Personalizar el estilo manteniendo el texto exacto
            switch ($descripcion) {
                case 'Promoción Neón Brillante':
                    $prompt .= "ESTILO: Diseño con efectos de neón brillante, fondo oscuro.\n";
                    $prompt .= "El texto '{$nombre}' debe brillar en neón, manteniendo su legibilidad.\n";
                    break;
                case 'Super Oferta Dinámica':
                    $prompt .= "ESTILO: Diseño dinámico y energético.\n";
                    $prompt .= "El texto '{$nombre}' debe destacar con tipografía bold sobre elementos dinámicos.\n";
                    break;
                case 'Mega Descuento Explosivo':
                    $prompt .= "ESTILO: Diseño impactante con elementos explosivos.\n";
                    $prompt .= "El texto '{$nombre}' debe ser el centro de atención, con tipografía impactante.\n";
                    break;
                // ... otros casos de estilo ...
                default:
                    $prompt .= "ESTILO: Diseño moderno y profesional.\n";
                    $prompt .= "El texto '{$nombre}' debe ser el elemento principal con tipografía clara.\n";
            }

            // Instrucciones finales críticas
            $prompt .= "\nINSTRUCCIONES FINALES OBLIGATORIAS:\n";
            $prompt .= "1. El texto debe ser EXACTAMENTE: '{$nombre}'\n";
            $prompt .= "2. NO MODIFICAR NI UNA LETRA del texto principal\n";
            $prompt .= "3. NO AGREGAR ningún texto adicional\n";
            $prompt .= "4. La imagen debe llenar todo el espacio sin bordes blancos\n";
            $prompt .= "5. El texto debe ser perfectamente legible\n";
            $prompt .= "6. Mantener el diseño en español\n";
            $prompt .= "7. El texto debe estar integrado en el diseño pero siempre visible y claro\n";

            // Configuración de la API ajustada para mejor precisión
            $data = [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => '1024x1024',
                'quality' => 'standard',
                'style' => 'natural',
                'response_format' => 'url'
            ];

            $response = $this->llamarAPI($prompt);
            
            if ($response && isset($response['data'][0]['url'])) {
                $url_imagen = $response['data'][0]['url'];
                
                $sql = "INSERT INTO imagenes (nombre, descripcion, url_imagen, usuario_id) 
                       VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                if ($stmt->execute([$nombre, $descripcion, $url_imagen, $usuario_id])) {
                    return true;
                } else {
                    throw new Exception("Error al guardar en la base de datos");
                }
            } else {
                throw new Exception("La API no devolvió una URL de imagen válida");
            }
        } catch (Exception $e) {
            error_log("Error en generarCaratula: " . $e->getMessage());
            throw new Exception("Error al generar la carátula: " . $e->getMessage());
        }
    }

    private function llamarAPI($prompt) {
        // Primero intentamos con cURL
        if (function_exists('curl_init')) {
            return $this->llamarAPIConCurl($prompt);
        }
        
        // Si cURL no está disponible, usamos file_get_contents como alternativa
        return $this->llamarAPIConFileGetContents($prompt);
    }

    private function llamarAPIConCurl($prompt) {
        $url = 'https://api.openai.com/v1/images/generations';
        
        $data = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'hd',
            'style' => 'vivid'
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->api_key
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error en la llamada a la API: $error");
        }

        if ($httpCode !== 200) {
            $responseData = json_decode($response, true);
            $errorMessage = isset($responseData['error']['message']) 
                ? $responseData['error']['message'] 
                : "HTTP Error: $httpCode";
            throw new Exception("Error de la API: " . $errorMessage);
        }

        return json_decode($response, true);
    }

    private function llamarAPIConFileGetContents($prompt) {
        $url = 'https://api.openai.com/v1/images/generations';
        
        $data = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'hd',
            'style' => 'vivid'
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->api_key
                ],
                'content' => json_encode($data),
                'timeout' => 60
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $context = stream_context_create($options);
        
        try {
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new Exception("Error al realizar la petición HTTP");
            }

            // Obtener los headers de respuesta para verificar el código HTTP
            $responseHeaders = $http_response_header ?? [];
            $statusLine = $responseHeaders[0] ?? '';
            
            if (strpos($statusLine, '200') === false) {
                throw new Exception("Error HTTP: " . $statusLine);
            }

            return json_decode($response, true);
        } catch (Exception $e) {
            throw new Exception("Error en la llamada a la API: " . $e->getMessage());
        }
    }

    public function obtenerTodasLasCaratulas() {
        try {
            if (!isset($_SESSION['usuario_id'])) {
                return [];
            }
            
            // Primero verifica si la columna usuario_id existe
            $checkColumn = $this->db->query("SHOW COLUMNS FROM imagenes LIKE 'usuario_id'");
            if ($checkColumn->rowCount() == 0) {
                // Si la columna no existe, retorna todas las imágenes
                $sql = "SELECT * FROM imagenes ORDER BY fecha_creacion DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
            } else {
                // Si la columna existe, filtra por usuario_id
                $sql = "SELECT * FROM imagenes WHERE usuario_id = ? OR usuario_id IS NULL ORDER BY fecha_creacion DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$_SESSION['usuario_id']]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerTodasLasCaratulas: " . $e->getMessage());
            return [];
        }
    }

    public function eliminarCaratula($id) {
        try {
            // Asegurarse de que el usuario solo pueda eliminar sus propias imágenes
            $sql = "DELETE FROM imagenes WHERE id = ? AND usuario_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id, $_SESSION['usuario_id']]);
        } catch (Exception $e) {
            error_log("Error al eliminar carátula: " . $e->getMessage());
            throw new Exception("Error al eliminar el diseño");
        }
    }
} 