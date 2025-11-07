<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once '../includes/chatbot-prompt.php';
require_once '../php/db.php';

class ChatbotAPI {
    private $geminiApiKey = 'AIzaSyBzA5NvlU9kKv-kC_twfSd4AZ_dXH_Ycpw';
    private $geminiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private $knowledgeBasePath = '../docs/chatbot-knowledge.md';
    private $rateLimitFile = '../logs/chatbot_rate_limit.json';
    private $logFile = '../logs/chatbot.log';
    private $conversationFile = '../logs/chatbot_conversations.json';

    public function handleRequest() {
        try {
            // Rate limiting check
            if (!$this->checkRateLimit()) {
                throw new Exception('Demasiadas solicitudes. Por favor, espera un momento antes de enviar otro mensaje.');
            }

            $rawInput = file_get_contents('php://input');

            // Debug: Log raw input
            error_log('Raw input: ' . $rawInput);

            // Handle the specific malformed JSON format first: {message:value}
            if (preg_match('/^\s*\{\s*message\s*:\s*(.+?)\s*\}\s*$/', $rawInput, $matches)) {
                $message = trim($matches[1]);
                // Remove surrounding quotes if present
                $message = trim($message, '\'"');
                $input = ['message' => $message];
                error_log('Parsed malformed JSON, message: ' . $message);
            } else {
                // Try regular JSON decode
                $input = json_decode($rawInput, true);
                error_log('Regular JSON decode result: ' . print_r($input, true));

                // If still null, try to manually parse the malformed format
                if ($input === null && strpos($rawInput, 'message:') !== false) {
                    if (preg_match('/message\s*:\s*([^,}]+)/', $rawInput, $matches)) {
                        $message = trim($matches[1], '\'"');
                        $input = ['message' => $message];
                        error_log('Fallback parsed malformed JSON, message: ' . $message);
                    } elseif (preg_match('/message\s*:\s*(.+?)(?:\s*\}|\s*,|\s*$)/', $rawInput, $matches)) {
                        $message = trim($matches[1], '\'"');
                        $input = ['message' => $message];
                        error_log('Extended fallback parsed malformed JSON, message: ' . $message);
                    }
                }
            }

            error_log('Decoded input: ' . print_r($input, true));

            if (!$input || !isset($input['message'])) {
                throw new Exception('Mensaje no proporcionado. Raw input: ' . $rawInput);
            }

            $userMessage = trim($input['message']);

            if (empty($userMessage)) {
                throw new Exception('Mensaje vacío');
            }

            // Sanitize input
            $userMessage = $this->sanitizeInput($userMessage);

            // Get conversation context
            $conversationId = $input['conversation_id'] ?? $this->getClientIdentifier();
            $conversationHistory = $this->getConversationHistory($conversationId);

            // Log the request
            $this->logRequest($userMessage);

            // Validar si el tema es relevante
            if (!ChatbotPrompt::isValidTopic($userMessage)) {
                $response = ChatbotPrompt::getGenericResponse();
                $this->logResponse($response);
                $this->saveConversationMessage($conversationId, $userMessage, $response);
                return [
                    'response' => $response,
                    'conversation_id' => $conversationId,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Handle simple greetings with predefined responses
            $lowerMessage = strtolower(trim($userMessage));
            if ($this->isSimpleGreeting($lowerMessage)) {
                $response = $this->getGreetingResponse();
                $this->logResponse($response);
                $this->saveConversationMessage($conversationId, $userMessage, $response);
                return [
                    'response' => $response,
                    'conversation_id' => $conversationId,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Check for specific database queries first
             $dbResponse = $this->handleDatabaseQuery($userMessage, $conversationHistory);
             if ($dbResponse !== null) {
                 $this->logResponse($dbResponse);
                 $this->saveConversationMessage($conversationId, $userMessage, $dbResponse);
                 return [
                     'response' => $dbResponse,
                     'conversation_id' => $conversationId,
                     'timestamp' => date('Y-m-d H:i:s')
                 ];
             }

            // Extraer contexto relevante
            $knowledgeContext = $this->getKnowledgeContext($userMessage);

            // Crear contexto de conversación para Gemini
            $conversationContext = $this->buildConversationContext($conversationHistory, $userMessage);

            // Llamar a la API de Gemini con contexto de conversación
            $response = $this->callGeminiAPIWithContext($userMessage, $knowledgeContext, $conversationContext);

            // Log the response
            $this->logResponse($response);

            // Save conversation
            $this->saveConversationMessage($conversationId, $userMessage, $response);

            return [
                'response' => $response,
                'conversation_id' => $conversationId,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            $errorMessage = 'Lo siento, ha ocurrido un error. Por favor, intenta de nuevo más tarde.';
            error_log('Chatbot API Error: ' . $e->getMessage());
            $this->logError($e->getMessage());

            // Return detailed error for debugging
            return [
                'error' => $errorMessage,
                'debug_info' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function getKnowledgeContext($userMessage) {
        try {
            if (!file_exists($this->knowledgeBasePath)) {
                return '';
            }

            $knowledgeContent = file_get_contents($this->knowledgeBasePath);
            $keywords = ChatbotPrompt::extractKeywords($userMessage);

            if (empty($keywords)) {
                return '';
            }

            $sections = $this->extractRelevantSections($knowledgeContent, $keywords);

            // Limitar el contexto a un tamaño razonable
            if (strlen($sections) > 2000) {
                $sections = substr($sections, 0, 2000) . '...';
            }

            return $sections;

        } catch (Exception $e) {
            error_log('Error extracting knowledge context: ' . $e->getMessage());
            return '';
        }
    }

    private function extractRelevantSections($content, $keywords) {
        $lines = explode("\n", $content);
        $relevantSections = [];
        $currentSection = '';
        $inRelevantSection = false;

        foreach ($lines as $line) {
            // Detectar encabezados de sección
            if (preg_match('/^#{1,6}\s+(.+)/', $line, $matches)) {
                // Si estábamos en una sección relevante, guardarla
                if ($inRelevantSection && !empty($currentSection)) {
                    $relevantSections[] = $currentSection;
                }

                $currentSection = $line . "\n";
                $inRelevantSection = $this->isSectionRelevant($line, $keywords);
            } else {
                $currentSection .= $line . "\n";

                // También verificar si el contenido de la línea es relevante
                if (!$inRelevantSection && $this->isContentRelevant($line, $keywords)) {
                    $inRelevantSection = true;
                }
            }
        }

        // Agregar la última sección si es relevante
        if ($inRelevantSection && !empty($currentSection)) {
            $relevantSections[] = $currentSection;
        }

        return implode("\n", $relevantSections);
    }

    private function isSectionRelevant($sectionHeader, $keywords) {
        $sectionText = strtolower($sectionHeader);
        foreach ($keywords as $keyword) {
            if (strpos($sectionText, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function isContentRelevant($content, $keywords) {
        $contentText = strtolower($content);
        foreach ($keywords as $keyword) {
            if (strpos($contentText, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    private function callGeminiAPIWithContext($userMessage, $knowledgeContext, $conversationContext) {
        $fullPrompt = ChatbotPrompt::getSystemPrompt();

        if (!empty($conversationContext)) {
            $fullPrompt .= "\n\nHISTORIAL DE CONVERSACIÓN RECIENTE:\n" . $conversationContext;
        }

        if (!empty($knowledgeContext)) {
            $fullPrompt .= "\n\nCONTEXTO ADICIONAL DEL HOTEL:\n" . $knowledgeContext;
        }

        $fullPrompt .= "\n\nMENSAJE ACTUAL DEL USUARIO:\n" . $userMessage;

        $fullPrompt .= "\n\nINSTRUCCIONES ADICIONALES:\n";
        $fullPrompt .= "- Mantén la conversación natural y coherente\n";
        $fullPrompt .= "- Recuerda TODA la información previa mencionada por el usuario en esta conversación\n";
        $fullPrompt .= "- Si el usuario mencionó fechas específicas, número de personas, o cualquier detalle, mantén esa información y úsala en tu respuesta\n";
        $fullPrompt .= "- Continúa la conversación de manera natural, como si recordaras todo lo que se ha hablado\n";
        $fullPrompt .= "- Sé servicial y ofrece ayuda adicional cuando sea apropiado\n";
        $fullPrompt .= "- Responde en español de manera natural y conversacional";

        return $this->callGeminiAPIWithCustomPrompt($fullPrompt);
    }

    private function buildConversationContext($history, $currentMessage) {
        if (empty($history)) {
            return '';
        }

        $context = '';
        $recentMessages = array_slice($history, -10); // Last 10 messages for better context

        foreach ($recentMessages as $msg) {
            if ($msg['type'] === 'user') {
                $context .= "Usuario: {$msg['message']}\n";
            } else {
                $context .= "Asistente: {$msg['response']}\n";
            }
        }

        return $context;
    }

    private function callGeminiAPIWithCustomPrompt($fullPrompt) {
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $fullPrompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->geminiUrl . '?key=' . $this->geminiApiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Error de conexión con Gemini API: ' . $error);
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Error en Gemini API. Código HTTP: ' . $httpCode);
        }

        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Respuesta inválida de Gemini API');
        }

        $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];

        // Limpiar la respuesta (remover markdown si es necesario)
        $generatedText = $this->cleanResponse($generatedText);

        return $generatedText;
    }

    private function callGeminiAPI($userMessage, $knowledgeContext) {
        $fullPrompt = ChatbotPrompt::getFullPrompt($userMessage, $knowledgeContext);

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $fullPrompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->geminiUrl . '?key=' . $this->geminiApiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('Error de conexión con Gemini API: ' . $error);
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Error en Gemini API. Código HTTP: ' . $httpCode);
        }

        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Respuesta inválida de Gemini API');
        }

        $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];

        // Limpiar la respuesta (remover markdown si es necesario)
        $generatedText = $this->cleanResponse($generatedText);

        return $generatedText;
    }

    private function cleanResponse($text) {
        // Remover caracteres de escape innecesarios
        $text = stripslashes($text);

        // Remover markdown básico si existe
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text); // Negrita
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);     // Cursiva

        // Limpiar espacios extra
        $text = trim($text);

        return $text;
    }

    private function checkRateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentTime = time();

        // Create logs directory if it doesn't exist
        $logDir = dirname($this->rateLimitFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Load existing rate limit data
        $rateData = [];
        if (file_exists($this->rateLimitFile)) {
            $rateData = json_decode(file_get_contents($this->rateLimitFile), true) ?: [];
        }

        // Clean old entries (older than 1 minute)
        $rateData = array_filter($rateData, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 60;
        });

        // Count requests from this IP in the last minute
        $recentRequests = array_filter($rateData, function($timestamp) use ($ip, $currentTime) {
            return ($currentTime - $timestamp) < 60;
        });

        // Allow max 10 requests per minute per IP
        if (count($recentRequests) >= 10) {
            return false;
        }

        // Add current request
        $rateData[] = $currentTime;

        // Save updated data
        file_put_contents($this->rateLimitFile, json_encode($rateData));

        return true;
    }

    private function sanitizeInput($input) {
        // Remove potentially harmful characters
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        // Limit length
        if (strlen($input) > 1000) {
            $input = substr($input, 0, 1000);
        }

        return $input;
    }

    private function logRequest($message) {
        $this->writeLog('REQUEST', $message);
    }

    private function logResponse($response) {
        $this->writeLog('RESPONSE', substr($response, 0, 200) . (strlen($response) > 200 ? '...' : ''));
    }

    private function logError($error) {
        $this->writeLog('ERROR', $error);
    }

    private function writeLog($type, $message) {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logEntry = "[$timestamp] [$ip] [$type] $message" . PHP_EOL;

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    private function isSimpleGreeting($message) {
        $greetings = [
            'hola', 'buenos dias', 'buenos días', 'buenas tardes', 'buenas noches',
            'saludos', 'qué tal', 'que tal', 'cómo estás', 'como estas', 'cómo está',
            'como esta', 'qué pasa', 'que pasa', 'hey', 'hi', 'hello'
        ];

        foreach ($greetings as $greeting) {
            if (strpos($message, $greeting) !== false) {
                return true;
            }
        }
        return false;
    }

    private function getGreetingResponse() {
        $responses = [
            "¡Hola! ¿Cómo estás hoy? 😊 Me da mucho gusto saludarte. ¿En qué puedo ayudarte hoy con respecto al Hotel INDET o el Instituto de Deportes del Estado Trujillo? ¿Quizás quieres saber algo sobre nuestras instalaciones deportivas, las habitaciones del hotel, o alguna otra cosa? ¡Estoy a tu disposición!",
            "¡Hola! Qué gusto verte por aquí. Soy el asistente del Hotel INDET. ¿En qué puedo ayudarte? Puedo informarte sobre habitaciones, reservas, servicios e instalaciones deportivas.",
            "¡Buen día! Estoy aquí para ayudarte con todo lo relacionado al Hotel INDET. ¿Qué te gustaría saber sobre nuestras habitaciones, reservas o servicios?",
            "¡Hola! ¿Cómo estás? Soy el asistente virtual del Hotel INDET. Puedo ayudarte con información sobre reservas, habitaciones, servicios e instalaciones deportivas.",
            "¡Saludos! Estoy para servirte en todo lo relacionado con el Hotel INDET. ¿Tienes alguna pregunta sobre nuestras instalaciones deportivas o reservas?"
        ];
        return $responses[array_rand($responses)];
    }

    private function handleDatabaseQuery($message, $conversationHistory = []) {
        $lowerMessage = strtolower($message);

        // Check for room availability queries with dates
        if (preg_match('/(?:habitaciones?|cuartos?|disponibles?|disponibilidad).*?(?:\d{4}-\d{2}-\d{2}|\d{2}[\/-]\d{2}[\/-]\d{4})/', $lowerMessage, $matches)) {
            return $this->getRoomAvailabilityInfo();
        }

        // Check for room availability queries without dates (but with context)
    if (preg_match('/(?:habitaciones?|cuartos?|disponibles?|disponibilidad)/', $lowerMessage) && $this->hasReservationContext($conversationHistory)) {
        return $this->getRoomAvailabilityWithContext($conversationHistory);
    }

    // Check for general reservation/booking queries with context
    if (preg_match('/(?:reservar|reserva|quiero|necesito|busco|guiame|ayudame)/', $lowerMessage) && $this->hasReservationContext($conversationHistory)) {
        return $this->getReservationGuidanceWithContext($conversationHistory);
    }

        // Check for room count queries
        if (preg_match('/cu[aá]ntas?\s*habitaciones?|n[uú]mero\s*de\s*habitaciones?|total\s*habitaciones?/', $lowerMessage)) {
            return $this->getTotalRoomsInfo();
        }

        // Check for registration guide
        if (preg_match('/(?:c[oó]mo|r[eé]gistrarme?|paso\s*a\s*paso|gu[ií]a).*(?:registrar|registro|crear\s*cuenta)/', $lowerMessage)) {
            return $this->getRegistrationGuide();
        }

        // Check for reservation guide
        if (preg_match('/(?:c[oó]mo|paso\s*a\s*paso|gu[ií]a).*(?:reservar|reserva|reservaci[oó]n)/', $lowerMessage)) {
            return $this->getReservationGuide();
        }

        // Check for services info
        if (preg_match('/(?:qu[eé]|cu[aá]les?).*servicios?|instalaciones?|ofrecen?|tienen?/', $lowerMessage)) {
            return $this->getServicesInfo();
        }

        return null; // Not a database query
    }

    private function getRoomAvailabilityInfo() {
        global $conn;
        try {
            // Get total rooms
            $totalQuery = "SELECT COUNT(*) as total FROM rooms WHERE status = 'enabled'";
            $totalResult = $conn->query($totalQuery);
            $totalRooms = $totalResult->fetch_assoc()['total'];

            // Get rooms by type
            $typeQuery = "SELECT type, COUNT(*) as count FROM rooms WHERE status = 'enabled' GROUP BY type";
            $typeResult = $conn->query($typeQuery);

            $roomTypes = [];
            while ($row = $typeResult->fetch_assoc()) {
                $roomTypes[] = $row['type'] . ": " . $row['count'];
            }

            $response = "🏨 **Información de Habitaciones del Hotel INDET**\n\n";
            $response .= "📊 **Total de habitaciones disponibles:** $totalRooms\n\n";
            $response .= "🏠 **Tipos de habitaciones:**\n";
            foreach ($roomTypes as $type) {
                $response .= "• $type\n";
            }
            $response .= "\n💡 **Para verificar disponibilidad en fechas específicas:**\n";
            $response .= "• Ve a la página de Reservaciones\n";
            $response .= "• Selecciona las fechas deseadas\n";
            $response .= "• Elige el piso y número de personas\n";
            $response .= "• El sistema mostrará habitaciones disponibles automáticamente\n\n";
            $response .= "¿Te gustaría que te ayude con el proceso de reserva?";

            return $response;
        } catch (Exception $e) {
            return "Lo siento, tuve un problema al consultar la información de habitaciones. Por favor, intenta de nuevo.";
        }
    }

    private function getTotalRoomsInfo() {
        global $conn;
        try {
            $query = "SELECT COUNT(*) as total FROM rooms WHERE status = 'enabled'";
            $result = $conn->query($query);
            $total = $result->fetch_assoc()['total'];

            return "🏨 El Hotel INDET cuenta actualmente con **$total habitaciones** disponibles para reservas. Cada habitación está equipada con comodidades modernas y está diseñada pensando en el confort de nuestros atletas e invitados.";
        } catch (Exception $e) {
            return "Lo siento, no pude consultar el número total de habitaciones en este momento.";
        }
    }

    private function getRegistrationGuide() {
        $guide = "📝 **Guía para Registrarte en el Hotel INDET**\n\n";
        $guide .= "Sigue estos pasos para crear tu cuenta:\n\n";
        $guide .= "1️⃣ **Accede al sitio web**\n";
        $guide .= "   • Ve a la página principal del Hotel INDET\n\n";
        $guide .= "2️⃣ **Haz clic en 'Login'**\n";
        $guide .= "   • En la parte superior derecha encontrarás el botón\n\n";
        $guide .= "3️⃣ **Selecciona '¿No tienes cuenta?'**\n";
        $guide .= "   • Busca el enlace para registrarte\n\n";
        $guide .= "4️⃣ **Completa el formulario**\n";
        $guide .= "   • Nombre completo\n";
        $guide .= "   • Correo electrónico (debe ser único)\n";
        $guide .= "   • Contraseña segura\n";
        $guide .= "   • Cédula de identidad\n\n";
        $guide .= "5️⃣ **Verifica tu cuenta**\n";
        $guide .= "   • Revisa tu correo para confirmar la cuenta\n\n";
        $guide .= "6️⃣ **¡Listo!**\n";
        $guide .= "   • Ya puedes iniciar sesión y hacer reservas\n\n";
        $guide .= "💡 **Nota:** El registro es gratuito y solo toma unos minutos. ¿Necesitas ayuda con algún paso específico?";

        return $guide;
    }

    private function getReservationGuide() {
        $guide = "🏨 **Guía para Hacer una Reserva en el Hotel INDET**\n\n";
        $guide .= "Proceso paso a paso:\n\n";
        $guide .= "1️⃣ **Inicia sesión**\n";
        $guide .= "   • Debes tener una cuenta registrada\n\n";
        $guide .= "2️⃣ **Ve a 'Reservación'**\n";
        $guide .= "   • Desde el menú principal o la página de inicio\n\n";
        $guide .= "3️⃣ **Selecciona fechas**\n";
        $guide .= "   • Fecha de llegada (check-in)\n";
        $guide .= "   • Fecha de salida (check-out)\n\n";
        $guide .= "4️⃣ **Elige el piso**\n";
        $guide .= "   • Planta Baja (accesible para discapacitados)\n";
        $guide .= "   • Pisos superiores (habitaciones estándar)\n\n";
        $guide .= "5️⃣ **Indica el número de personas**\n";
        $guide .= "   • Adultos\n";
        $guide .= "   • Niños\n";
        $guide .= "   • Personas con discapacidades\n\n";
        $guide .= "6️⃣ **Selecciona habitaciones**\n";
        $guide .= "   • El sistema mostrará opciones disponibles\n";
        $guide .= "   • Elige las habitaciones que necesites\n\n";
        $guide .= "7️⃣ **Confirma la reserva**\n";
        $guide .= "   • Revisa todos los detalles\n";
        $guide .= "   • Haz clic en 'Reservar'\n\n";
        $guide .= "8️⃣ **Recibe confirmación**\n";
        $guide .= "   • Se enviará a tu perfil de usuario\n\n";
        $guide .= "💡 **Recordatorios importantes:**\n";
        $guide .= "• Check-in: A partir de las 15:00\n";
        $guide .= "• Check-out: Hasta las 12:00\n";
        $guide .= "• Cancelación gratuita hasta 24h antes\n\n";
        $guide .= "¿Te ayudo a iniciar el proceso de reserva ahora?";

        return $guide;
    }

    private function hasReservationContext($conversationHistory) {
        if (empty($conversationHistory)) {
            return false;
        }

        // Check recent messages for reservation-related keywords
        $recentMessages = array_slice($conversationHistory, -10); // Check more messages
        $reservationKeywords = [
            'noviembre', 'diciembre', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'llegar', 'irme', 'salir', 'personas',
            'habitaciones', 'reservar', 'disponibles', 'disponibilidad', 'fecha', 'fechas',
            'checkin', 'checkout', 'llegada', 'salida', 'hospedarme', 'hospedarse',
            'noches', 'dias', 'estadia', 'adultos', 'niños', 'bebes', 'familia', 'pareja',
            'solo', 'acompañado', 'juntos', 'grupo', 'equipo', 'deportistas', 'atletas',
            'entrenamiento', 'competencia', 'partido', 'evento', 'torneo', 'campeonato',
            'instalaciones', 'canchas', 'pista', 'gimnasio', 'piscina', 'spa', 'sauna',
            'jacuzzi', 'masaje', 'terapia', 'recuperacion', 'lesion', 'lesiones', 'rehabilitacion',
            'quiero', 'necesito', 'busco', 'buscando', 'gustaria', 'quisiera', 'podrias',
            'puedes', 'ayudame', 'guiame', 'explicame', 'dime', 'cuentame', 'muestrame',
            'ensename', 'si', 'no', 'claro', 'perfecto', 'excelente', 'genial', 'bueno'
        ];

        foreach ($recentMessages as $msg) {
            $message = strtolower($msg['message'] ?? '');
            foreach ($reservationKeywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getRoomAvailabilityWithContext($conversationHistory) {
        // Extract dates and number of people from conversation history
        $dates = $this->extractDatesFromHistory($conversationHistory);
        $peopleCount = $this->extractPeopleCountFromHistory($conversationHistory);

        $response = "¡Perfecto! Basándome en lo que me has contado anteriormente";

        if (!empty($dates)) {
            $response .= " sobre las fechas del " . implode(' al ', $dates);
        }

        if ($peopleCount > 0) {
            $response .= " para $peopleCount personas";
        }

        $response .= ", te ayudo con la disponibilidad de habitaciones.\n\n";

        // Get actual room availability from database
        global $conn;
        try {
            $totalQuery = "SELECT COUNT(*) as total FROM rooms WHERE status = 'enabled'";
            $totalResult = $conn->query($totalQuery);
            $totalRooms = $totalResult->fetch_assoc()['total'];

            $typeQuery = "SELECT type, COUNT(*) as count FROM rooms WHERE status = 'enabled' GROUP BY type";
            $typeResult = $conn->query($typeQuery);

            $roomTypes = [];
            while ($row = $typeResult->fetch_assoc()) {
                $roomTypes[] = $row['type'] . ": " . $row['count'];
            }

            $response .= "🏨 **Información de Habitaciones Disponibles**\n\n";
            $response .= "📊 **Total de habitaciones:** $totalRooms\n\n";
            $response .= "🏠 **Tipos de habitaciones:**\n";
            foreach ($roomTypes as $type) {
                $response .= "• $type\n";
            }
            $response .= "\n💡 **Para verificar disponibilidad específica:**\n";
            $response .= "• Ve a la página de Reservaciones\n";
            $response .= "• Selecciona las fechas que mencionaste\n";
            $response .= "• Elige el número de personas\n";
            $response .= "• El sistema mostrará habitaciones disponibles automáticamente\n\n";
            $response .= "¿Te gustaría que te guíe para hacer la reserva ahora?";

            return $response;
        } catch (Exception $e) {
            return $response . "🏨 Actualmente tenemos habitaciones disponibles. ¿Te gustaría proceder con la reserva usando la información que me proporcionaste?";
        }
    }

    private function extractDatesFromHistory($conversationHistory) {
        $dates = [];
        foreach ($conversationHistory as $msg) {
            $message = strtolower($msg['message'] ?? '');
            // Look for date patterns like "7 de noviembre", "9 de noviembre", etc.
            $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            foreach ($months as $month) {
                if (preg_match_all('/(\d{1,2})\s*de\s*' . $month . '/i', $message, $matches)) {
                    foreach ($matches[1] as $day) {
                        $dates[] = $day . ' de ' . $month;
                    }
                }
            }
        }
        return array_unique($dates);
    }

    private function extractPeopleCountFromHistory($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            $message = strtolower($msg['message'] ?? '');
            // Look for number patterns followed by "personas", "gente", "adultos", etc.
            if (preg_match('/(\d+)\s*(?:personas|gente|adultos|niños|bebes|miembros|integrantes)/i', $message, $matches)) {
                return (int)$matches[1];
            }
            // Also check for standalone numbers that might refer to people count
            if (preg_match('/somos\s*(\d+)/i', $message, $matches)) {
                return (int)$matches[1];
            }
        }
        return 0;
    }

    private function getReservationGuidanceWithContext($conversationHistory) {
        // Extract dates and number of people from conversation history
        $dates = $this->extractDatesFromHistory($conversationHistory);
        $peopleCount = $this->extractPeopleCountFromHistory($conversationHistory);

        $response = "¡Claro! Recuerdo que estamos hablando de tu reserva";

        if (!empty($dates)) {
            $response .= " para las fechas del " . implode(' al ', $dates);
        }

        if ($peopleCount > 0) {
            $response .= " para $peopleCount personas";
        }

        $response .= ".\n\n";

        $response .= "🏨 **Guía para Hacer tu Reserva**\n\n";
        $response .= "Sigue estos pasos:\n\n";
        $response .= "1️⃣ **Ve a la página de Reservaciones**\n";
        $response .= "   • Desde el menú principal o haz clic en 'Reservar'\n\n";
        $response .= "2️⃣ **Selecciona las fechas**\n";

        if (!empty($dates)) {
            $response .= "   • Ya tienes las fechas: " . implode(' - ', $dates) . "\n";
        } else {
            $response .= "   • Fecha de llegada (check-in)\n";
            $response .= "   • Fecha de salida (check-out)\n";
        }

        $response .= "\n3️⃣ **Elige el piso**\n";
        $response .= "   • Planta Baja (accesible para discapacitados)\n";
        $response .= "   • Pisos superiores (habitaciones estándar)\n\n";

        $response .= "4️⃣ **Indica el número de personas**\n";

        if ($peopleCount > 0) {
            $response .= "   • Ya mencionaste: $peopleCount personas\n";
        } else {
            $response .= "   • Adultos, niños, personas con discapacidad\n";
        }

        $response .= "\n5️⃣ **Selecciona habitaciones disponibles**\n";
        $response .= "   • El sistema te mostrará las opciones\n";
        $response .= "   • Elige las que necesites\n\n";
        $response .= "6️⃣ **Confirma y reserva**\n";
        $response .= "   • Revisa todos los detalles\n";
        $response .= "   • Haz clic en 'Reservar'\n\n";
        $response .= "💡 **Información importante:**\n";
        $response .= "• Check-in: A partir de las 15:00\n";
        $response .= "• Check-out: Hasta las 12:00\n";
        $response .= "• Cancelación gratuita hasta 24h antes\n\n";
        $response .= "¿Te ayudo con algún paso específico o tienes alguna pregunta?";

        return $response;
    }

    private function getServicesInfo() {
        global $conn;
        try {
            // Get active events
            $eventsQuery = "SELECT COUNT(*) as total FROM events WHERE date >= CURDATE()";
            $eventsResult = $conn->query($eventsQuery);
            $activeEvents = $eventsResult->fetch_assoc()['total'];

            $services = "🏨 **Servicios del Hotel INDET**\n\n";
            $services .= "🏊 **Instalaciones Deportivas:**\n";
            $services .= "• Piscina climatizada\n";
            $services .= "• Gimnasio completamente equipado\n";
            $services .= "• Canchas deportivas\n\n";

            $services .= "🍽️ **Servicios de Alimentación:**\n";
            $services .= "• Restaurante gourmet\n";
            $services .= "• Servicio a la habitación 24/7\n";
            $services .= "• Menús especiales para atletas\n\n";

            $services .= "🌐 **Servicios Generales:**\n";
            $services .= "• Wi-Fi de alta velocidad\n";
            $services .= "• Estacionamiento\n";
            $services .= "• Servicio de lavandería\n";
            $services .= "• Centro de negocios\n\n";

            $services .= "📅 **Eventos Deportivos:**\n";
            $services .= "Actualmente tenemos **$activeEvents eventos** programados.\n\n";

            $services .= "💼 **Horarios de Servicio:**\n";
            $services .= "• Recepción: 24 horas\n";
            $services .= "• Restaurante: 6:00 - 22:00\n";
            $services .= "• Gimnasio: 5:00 - 23:00\n\n";

            $services .= "¿Te gustaría más información sobre algún servicio específico?";

            return $services;
        } catch (Exception $e) {
            return "🏨 **Servicios del Hotel INDET**\n\nOfrecemos una amplia gama de servicios incluyendo:\n• Instalaciones deportivas completas\n• Restaurante y servicio a la habitación\n• Wi-Fi y estacionamiento\n• Gimnasio 24/7\n• Eventos deportivos\n\n¿Te gustaría más detalles sobre algún servicio?";
        }
    }

    private function getClientIdentifier() {
        // Use IP address and user agent as conversation identifier
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return md5($ip . $userAgent);
    }

    private function getConversationHistory($conversationId) {
        try {
            if (!file_exists($this->conversationFile)) {
                return [];
            }

            $conversations = json_decode(file_get_contents($this->conversationFile), true) ?: [];
            return $conversations[$conversationId] ?? [];
        } catch (Exception $e) {
            error_log('Error loading conversation history: ' . $e->getMessage());
            return [];
        }
    }

    private function saveConversationMessage($conversationId, $message, $response, $type = 'user') {
        try {
            // Create logs directory if it doesn't exist
            $logDir = dirname($this->conversationFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Load existing conversations
            $conversations = [];
            if (file_exists($this->conversationFile)) {
                $conversations = json_decode(file_get_contents($this->conversationFile), true) ?: [];
            }

            // Initialize conversation if it doesn't exist
            if (!isset($conversations[$conversationId])) {
                $conversations[$conversationId] = [];
            }

            // Add new message
            $conversations[$conversationId][] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'type' => $type,
                'message' => $message,
                'response' => $response
            ];

            // Keep only last 20 messages per conversation
            if (count($conversations[$conversationId]) > 20) {
                $conversations[$conversationId] = array_slice($conversations[$conversationId], -20);
            }

            // Save conversations
            file_put_contents($this->conversationFile, json_encode($conversations, JSON_PRETTY_PRINT));

        } catch (Exception $e) {
            error_log('Error saving conversation: ' . $e->getMessage());
        }
    }
}

$chatbot = new ChatbotAPI();
$result = $chatbot->handleRequest();
echo json_encode($result);
?>