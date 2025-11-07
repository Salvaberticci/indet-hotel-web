class ChatbotManager {
    constructor() {
        this.apiUrl = 'http://localhost:8000/api/chatbot.php';
        this.isOpen = false;
        this.isTyping = false;
        this.conversationId = this.generateConversationId();
        this.init();
    }

    init() {
        // Wait for DOM to be fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeElements());
        } else {
            this.initializeElements();
        }
    }

    initializeElements() {
        this.chatbotButton = document.getElementById('chatbot-button');
        this.chatbotWindow = document.getElementById('chatbot-window');
        this.chatbotClose = document.getElementById('chatbot-close');
        this.chatbotInput = document.getElementById('chatbot-input');
        this.chatbotSend = document.getElementById('chatbot-send');
        this.chatbotMessages = document.getElementById('chatbot-messages');
        this.typingIndicator = document.getElementById('typing-indicator');

        // Ensure container is visible
        const container = document.getElementById('chatbot-container');
        if (container) {
            container.style.display = 'block';
        }

        this.bindEvents();
    }

    bindEvents() {
        // Toggle chatbot - ensure button is properly bound
        if (this.chatbotButton) {
            this.chatbotButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Chatbot button clicked');
                this.toggleChat();
            });

            // Make sure button is focusable and clickable
            this.chatbotButton.style.cursor = 'pointer';
            this.chatbotButton.setAttribute('tabindex', '0');

            // Ensure the button is visible and has proper event handling
            this.chatbotButton.style.userSelect = 'none';
            this.chatbotButton.style.webkitUserSelect = 'none';
            this.chatbotButton.style.mozUserSelect = 'none';
            this.chatbotButton.style.msUserSelect = 'none';
        }

        // Close chatbot
        if (this.chatbotClose) {
            this.chatbotClose.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeChat();
            });
        }

        // Send message events
        if (this.chatbotSend) {
            this.chatbotSend.addEventListener('click', () => this.sendMessage());
        }

        if (this.chatbotInput) {
            this.chatbotInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Input validation
            this.chatbotInput.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length > 500) {
                    e.target.value = value.substring(0, 500);
                }
                this.updateSendButton();
            });
        }

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (this.chatbotWindow && this.chatbotButton &&
                !this.chatbotWindow.contains(e.target) &&
                !this.chatbotButton.contains(e.target) &&
                this.isOpen) {
                this.closeChat();
            }
        });

        // Debug: Log when elements are found
        console.log('Chatbot elements initialized:', {
            button: !!this.chatbotButton,
            window: !!this.chatbotWindow,
            close: !!this.chatbotClose,
            input: !!this.chatbotInput,
            send: !!this.chatbotSend,
            messages: !!this.chatbotMessages,
            typing: !!this.typingIndicator
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.openChat();
        } else {
            this.closeChat();
        }
    }

    openChat() {
        console.log('Opening chat window');
        this.chatbotWindow.classList.remove('hidden');
        this.chatbotWindow.style.display = 'block';
        this.chatbotWindow.classList.add('animate-fadeIn');
        this.chatbotButton.innerHTML = '<i class="fas fa-times text-2xl"></i>';
        this.chatbotInput.focus();
        this.scrollToBottom();
    }

    closeChat() {
        console.log('Closing chat window');
        this.isOpen = false;
        this.chatbotWindow.classList.add('hidden');
        this.chatbotWindow.style.display = 'none';
        this.chatbotButton.innerHTML = '<i class="fas fa-robot text-2xl"></i>';
    }

    updateSendButton() {
        const message = this.chatbotInput.value.trim();
        const isValid = message.length > 0 && message.length <= 500 && !this.isTyping;
        this.chatbotSend.disabled = !isValid;
    }

    async sendMessage() {
        const message = this.chatbotInput.value.trim();

        if (!message || this.isTyping) {
            return;
        }

        // Add user message
        this.addMessage(message, 'user');
        this.chatbotInput.value = '';

        // Show typing indicator
        this.showTyping();

        try {
            const response = await this.callAPI(message);

            // Hide typing indicator
            this.hideTyping();

            // Add bot response
            if (response.error) {
                this.addMessage(response.error, 'bot', true);
                // Log debug info if available
                if (response.debug_info) {
                    console.error('Chatbot Debug Info:', response.debug_info);
                }
            } else {
                this.addMessage(response.response, 'bot');
            }

        } catch (error) {
            console.error('Error:', error);
            this.hideTyping();
            this.addMessage('Lo siento, ha ocurrido un error. Por favor, intenta de nuevo más tarde.', 'bot', true);
        }

        this.updateSendButton();
    }

    async callAPI(message) {
        console.log('Sending message to API:', message);
        const requestData = {
            message: message,
            conversation_id: this.conversationId,
            timestamp: new Date().toISOString()
        };
        console.log('Request data:', requestData);

        const response = await fetch(this.apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(requestData)
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error text:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, body: ${errorText}`);
        }

        const data = await response.json();
        console.log('Response data:', data);
        return data;
    }

    addMessage(text, sender, isError = false) {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = 'message-wrapper mb-4';

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message ${isError ? 'error-message' : ''}`;

        if (sender === 'bot') {
            messageDiv.innerHTML = `
                <div class="flex items-center space-x-2 mb-2">
                    <i class="fas fa-robot ${isError ? 'text-red-500' : 'text-green-500'}"></i>
                    <span class="text-sm font-semibold ${isError ? 'text-red-600' : 'text-green-600'}">INDET Bot</span>
                </div>
                <p class="text-sm whitespace-pre-wrap">${this.escapeHtml(text)}</p>
                <span class="text-xs text-gray-500 mt-2 block">${new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</span>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="flex items-center justify-end space-x-2 mb-2">
                    <span class="text-sm font-semibold text-blue-600">Tú</span>
                    <i class="fas fa-user text-blue-500"></i>
                </div>
                <p class="text-sm whitespace-pre-wrap text-right">${this.escapeHtml(text)}</p>
                <span class="text-xs text-gray-500 mt-2 block text-right">${new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</span>
            `;
        }

        messageWrapper.appendChild(messageDiv);
        this.chatbotMessages.appendChild(messageWrapper);
        this.scrollToBottom();
    }

    showTyping() {
        this.isTyping = true;
        this.typingIndicator.classList.remove('hidden');
        this.updateSendButton();
        this.scrollToBottom();
    }

    hideTyping() {
        this.isTyping = false;
        this.typingIndicator.classList.add('hidden');
        this.updateSendButton();
    }

    scrollToBottom() {
        setTimeout(() => {
            this.chatbotMessages.scrollTop = this.chatbotMessages.scrollHeight;
        }, 100);
    }

    generateConversationId() {
        // Generate a unique conversation ID based on session/timestamp
        const timestamp = Date.now();
        const random = Math.random().toString(36).substring(2, 15);
        return `conv_${timestamp}_${random}`;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize chatbot when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing chatbot...');
    new ChatbotManager();
});

// Also try to initialize immediately if DOM is already ready
if (document.readyState !== 'loading') {
    console.log('DOM already ready, initializing chatbot...');
    new ChatbotManager();
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .animate-fadeIn {
        animation: fadeIn 0.2s ease-out;
    }

    .user-message {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
        padding: 12px;
        border-radius: 18px 18px 4px 18px;
        margin-left: auto;
        max-width: 80%;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .bot-message {
        background: white;
        color: #374151;
        padding: 12px;
        border-radius: 18px 18px 18px 4px;
        margin-right: auto;
        max-width: 80%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    .error-message {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
    }

    .error-message .text-red-600 {
        color: #dc2626;
    }

    /* Mobile optimizations */
    @media (max-width: 640px) {
        .user-message, .bot-message {
            max-width: 90%;
        }
    }
`;
document.head.appendChild(style);