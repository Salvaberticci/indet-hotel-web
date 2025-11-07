<?php
class ChatbotPrompt {
    private static $systemPrompt = "Eres un asistente virtual amigable especializado en el Hotel INDET y el Instituto de Deportes del Estado Trujillo.

INSTRUCCIONES IMPORTANTES:
- Sé amable, conversacional y servicial, como un amigo cercano
- Responde de manera natural y humana, no como un robot
- Puedes saludar, preguntar cómo estás, y mantener conversaciones casuales
- Responde SOLO preguntas relacionadas con el Hotel INDET, sus servicios, reservas, instalaciones deportivas y el Instituto de Deportes
- Si la pregunta no está relacionada con estos temas, responde cortésmente que solo puedes ayudar con temas del hotel y deportes, pero de manera amable
- Mantén un tono profesional pero cercano y amigable
- Responde en español, ya que el sistema está en español
- Proporciona información precisa basada en el contexto proporcionado
- Si no tienes información específica, sugiere contactar a la administración
- Para saludos simples como 'hola', 'buenos días', responde de manera natural y pregunta en qué puedes ayudar

Pregunta del usuario:";

    private static $validKeywords = [
        'hotel', 'indet', 'habitacion', 'reserva', 'reservacion', 'checkin', 'checkout',
        'servicio', 'instalacion', 'piscina', 'gimnasio', 'restaurante', 'wifi',
        'precio', 'tarifa', 'disponibilidad', 'disponible', 'piso', 'planta',
        'deporte', 'atleta', 'instituto', 'trujillo', 'contacto', 'telefono',
        'horario', 'politica', 'cancelacion', 'pago', 'factura', 'faq',
        'administrador', 'usuario', 'login', 'registro', 'perfil', 'mantenimiento',
        'evento', 'actividad', 'deportiva', 'niño', 'infantil', 'discapacitado',
        'accesibilidad', 'mascota', 'fumador', 'estacionamiento', 'parking',
        'hola', 'buenos', 'dias', 'tardes', 'noches', 'gracias', 'por', 'favor',
        'ayuda', 'informacion', 'consulta', 'pregunta', 'duda', 'como', 'estas',
        'si', 'no', 'quiero', 'puedo', 'hay', 'estan', 'tengo', 'vamos', 'hagamos',
        'necesito', 'busco', 'buscando', 'me gustaria', 'quisiera', 'podrias',
        'puedes', 'ayudame', 'guiame', 'explicame', 'dime', 'cuentame', 'muestrame',
        'ensename', 'llegar', 'irme', 'salir', 'personas', 'gente', 'adultos',
        'niños', 'bebes', 'familia', 'pareja', 'solo', 'acompañado', 'juntos',
        'grupo', 'equipo', 'deportistas', 'atletas', 'entrenamiento', 'competencia',
        'partido', 'evento', 'torneo', 'campeonato', 'instalaciones', 'canchas',
        'pista', 'gimnasio', 'piscina', 'spa', 'sauna', 'jacuzzi', 'masaje',
        'terapia', 'recuperacion', 'lesion', 'lesiones', 'rehabilitacion'
    ];

    public static function getSystemPrompt() {
        return self::$systemPrompt;
    }

    public static function getFullPrompt($userMessage, $knowledgeContext = '') {
        $prompt = self::$systemPrompt;
        if (!empty($knowledgeContext)) {
            $prompt .= "\n\nCONTEXTO ADICIONAL:\n" . $knowledgeContext;
        }
        $prompt .= "\n\n" . $userMessage;
        return $prompt;
    }

    public static function isValidTopic($message) {
        $message = strtolower($message);

        // Always allow basic greetings and conversational messages
        $greetingWords = ['hola', 'buenos', 'dias', 'tardes', 'noches', 'gracias', 'por', 'favor', 'ayuda', 'como', 'estas', 'estoy', 'bien', 'muy', 'mucho'];
        foreach ($greetingWords as $word) {
            if (strpos($message, $word) !== false) {
                return true;
            }
        }

        // Check for hotel/sports related keywords
        foreach (self::$validKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function extractKeywords($message) {
        $message = strtolower($message);
        $foundKeywords = [];

        foreach (self::$validKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                $foundKeywords[] = $keyword;
            }
        }

        return array_unique($foundKeywords);
    }

    public static function getGenericResponse() {
        $responses = [
            "¡Hola! Soy el asistente del Hotel INDET. ¿En qué puedo ayudarte hoy? Puedo informarte sobre habitaciones, reservas, servicios e instalaciones deportivas.",
            "¡Buen día! Estoy aquí para ayudarte con todo lo relacionado al Hotel INDET. ¿Qué te gustaría saber sobre nuestras habitaciones, reservas o servicios?",
            "¡Hola! ¿Cómo estás? Soy el asistente virtual del Hotel INDET. Puedo ayudarte con información sobre reservas, habitaciones, servicios e instalaciones deportivas.",
            "¡Saludos! Estoy para servirte en todo lo relacionado con el Hotel INDET. ¿Tienes alguna pregunta sobre nuestras instalaciones deportivas o reservas?",
            "¡Hola! Qué gusto verte por aquí. Soy el asistente del Hotel INDET y puedo ayudarte con información sobre habitaciones, reservas y servicios deportivos."
        ];
        return $responses[array_rand($responses)];
    }
}
?>