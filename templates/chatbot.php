<!-- Chatbot Component -->
<div id="chatbot-container" class="fixed bottom-6 right-6 z-50" style="display: block !important;">
    <!-- Chatbot Button -->
    <button id="chatbot-button" type="button" class="w-16 h-16 bg-gradient-to-r from-green-500 to-red-500 rounded-full cursor-pointer flex items-center justify-center text-white shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 border-0 outline-none focus:outline-none active:scale-95">
        <i class="fas fa-robot text-2xl pointer-events-none"></i>
    </button>

    <!-- Chatbot Window -->
    <div id="chatbot-window" class="hidden absolute bottom-24 right-0 w-80 h-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden" style="display: none !important;">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-red-600 text-white p-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-robot text-xl"></i>
                    <div>
                        <h3 class="text-lg font-bold">Asistente INDET</h3>
                        <p class="text-sm opacity-90">Tu guía del hotel deportivo</p>
                    </div>
                </div>
                <button id="chatbot-close" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Messages Container -->
        <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto bg-gray-50" style="height: calc(100% - 140px);">
            <!-- Welcome Message -->
            <div class="message-wrapper mb-4">
                <div class="message bot-message bg-white text-gray-800 p-3 rounded-lg shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center space-x-2 mb-2">
                        <i class="fas fa-robot text-green-500"></i>
                        <span class="text-sm font-semibold text-green-600">INDET Bot</span>
                    </div>
                    <p class="text-sm">¡Hola! Soy el asistente virtual del Hotel INDET. ¿En qué puedo ayudarte hoy? Puedo informarte sobre habitaciones, reservas, servicios e instalaciones deportivas.</p>
                    <span class="text-xs text-gray-500 mt-2 block"><?php echo date('H:i'); ?></span>
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="typing-indicator" class="hidden px-4 py-2">
            <div class="flex items-center space-x-2">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <span class="text-sm text-gray-500">INDET Bot está escribiendo...</span>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-gray-200 bg-white">
            <div class="flex space-x-2">
                <input type="text"
                       id="chatbot-input"
                       placeholder="Escribe tu mensaje..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm text-gray-900 placeholder-gray-500 bg-white"
                       maxlength="500">
                <button id="chatbot-send"
                        class="bg-gradient-to-r from-green-500 to-red-500 text-white p-2 rounded-full hover:shadow-lg transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </div>
            <div class="text-xs text-gray-500 mt-2 text-center">
                Solo preguntas sobre el hotel y deportes
            </div>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for messages */
#chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

#chatbot-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#chatbot-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

#chatbot-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Message animations */
.message-wrapper {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 640px) {
    #chatbot-container {
        bottom: 1rem;
        right: 1rem;
    }

    #chatbot-window {
        width: calc(100vw - 2rem);
        height: 80vh;
        bottom: 5rem;
        right: -1rem;
    }
}
</style>