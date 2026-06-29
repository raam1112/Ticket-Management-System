<div id="etms-chatbot" style="position: fixed; bottom: 24px; right: 24px; z-index: 1050; font-family: sans-serif;">
    <!-- Chat Toggle Button -->
    <button id="chatbot-toggle" class="btn btn-primary rounded-circle shadow" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; transition: transform 0.2s;">
        <i class="fas fa-comment-dots fa-2x"></i>
    </button>

    <!-- Chat Window -->
    <div id="chatbot-window" class="card shadow-lg d-none" style="position: absolute; bottom: 70px; right: 0; width: 320px; border-radius: 12px; overflow: hidden; border: 1px solid rgba(0,0,0,0.1);">
        <!-- Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
            <h5 class="mb-0" style="font-size: 1rem; font-weight: 600;">Support Assistant</h5>
            <button id="chatbot-close" class="btn-close btn-close-white" aria-label="Close" style="font-size: 0.8rem;"></button>
        </div>

        <!-- Chat Area -->
        <div id="chatbot-messages" class="card-body bg-light" style="height: 300px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding: 15px;">
            <div class="bg-white text-dark rounded p-2 shadow-sm align-self-start" style="max-width: 85%; font-size: 0.9rem; border: 1px solid #eee;">
                Hi there! 👋 I am your Support Assistant. You can ask me about SLAs, Ticket Priorities, Statuses, Roles, or check your open tickets!
            </div>
        </div>

        <!-- Input Area -->
        <div class="card-footer bg-white p-2">
            <form id="chatbot-form" class="d-flex gap-2 m-0">
                <input type="text" id="chatbot-input" class="form-control form-control-sm" placeholder="Type your question..." autocomplete="off" style="border-radius: 20px;">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                    Send
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('chatbot-toggle');
        const closeBtn = document.getElementById('chatbot-close');
        const chatWindow = document.getElementById('chatbot-window');
        const chatForm = document.getElementById('chatbot-form');
        const chatInput = document.getElementById('chatbot-input');
        const messagesDiv = document.getElementById('chatbot-messages');

        function toggleChat() {
            chatWindow.classList.toggle('d-none');
            if(!chatWindow.classList.contains('d-none')) {
                chatInput.focus();
            }
        }

        toggleBtn.addEventListener('click', toggleChat);
        closeBtn.addEventListener('click', toggleChat);

        function appendMessage(text, isUser = false) {
            const msgDiv = document.createElement('div');
            msgDiv.className = isUser 
                ? 'bg-primary text-white rounded p-2 shadow-sm align-self-end'
                : 'bg-white text-dark rounded p-2 shadow-sm align-self-start';
            msgDiv.style.maxWidth = '85%';
            msgDiv.style.fontSize = '0.9rem';
            if (!isUser) msgDiv.style.border = '1px solid #eee';
            if (isUser) {
                msgDiv.innerText = text;
            } else {
                msgDiv.innerHTML = text;
            }
            messagesDiv.appendChild(msgDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            // Display user message
            appendMessage(message, true);
            chatInput.value = '';

            // Add loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'bg-white text-muted rounded p-2 shadow-sm align-self-start';
            loadingDiv.style.maxWidth = '85%';
            loadingDiv.style.fontSize = '0.9rem';
            loadingDiv.style.border = '1px solid #eee';
            loadingDiv.innerText = 'Thinking...';
            loadingDiv.id = 'chatbot-loading';
            messagesDiv.appendChild(loadingDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;

            try {
                const response = await fetch('{{ route("chatbot.ask") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message })
                });
                
                const data = await response.json();
                
                // Remove loading
                const loader = document.getElementById('chatbot-loading');
                if (loader) loader.remove();
                
                if (data.reply) {
                    appendMessage(data.reply, false);
                } else {
                    appendMessage("Error processing your request.", false);
                }
            } catch (error) {
                const loader = document.getElementById('chatbot-loading');
                if (loader) loader.remove();
                appendMessage("Sorry, the assistant is currently unavailable.", false);
            }
        });
    });
</script>
