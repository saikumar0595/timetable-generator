(function() {
    // Inject Styles
    const style = document.createElement('style');
    style.innerHTML = `
        #chronogen-chat-widget {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            font-family: 'Inter', sans-serif;
        }
        #chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        #chat-button:hover {
            transform: scale(1.1) rotate(10deg);
        }
        #chat-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 450px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px) scale(0.95);
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #chat-window.active {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: all;
        }
        #chat-header {
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: white;
            padding: 16px 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        #chat-header .status-dot {
            width: 8px;
            height: 8px;
            background-color: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 8px #4ade80;
            animation: pulse-opacity 1.5s infinite;
        }
        #chat-messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .chat-msg {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.4;
            animation: slide-up 0.3s ease-out;
        }
        .msg-bot {
            background: #f1f5f9;
            color: #334155;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }
        .msg-user {
            background: #6366f1;
            color: white;
            border-bottom-right-radius: 4px;
            align-self: flex-end;
        }
        #chat-input-area {
            padding: 16px;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            gap: 8px;
            background: white;
        }
        #chat-input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 10px 16px;
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        #chat-input:focus { border-color: #6366f1; }
        #chat-send {
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        #chat-send:hover { background: #4f46e5; }
        .typing-indicator { display: flex; gap: 4px; padding: 12px 16px; background: #f1f5f9; border-radius: 16px; border-bottom-left-radius: 4px; width: fit-content; align-self: flex-start; display: none; }
        .typing-dot { width: 6px; height: 6px; background: #94a3b8; border-radius: 50%; animation: typing 1.4s infinite ease-in-out both; }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.6); } 70% { box-shadow: 0 0 0 15px rgba(99, 102, 241, 0); } 100% { box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); } }
        @keyframes pulse-opacity { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        @keyframes slide-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes typing { 0%, 80%, 100% { transform: scale(0); } 40% { transform: scale(1); } }
    `;
    document.head.appendChild(style);

    // Inject HTML
    const container = document.createElement('div');
    container.id = 'chronogen-chat-widget';
    container.innerHTML = `
        <div id="chat-window">
            <div id="chat-header">
                <div class="status-dot"></div>
                <div>
                    <div style="font-size: 15px; margin-bottom: 2px;">ChronoGen AI</div>
                    <div style="font-size: 10px; font-weight: normal; opacity: 0.8;">Online | Assisting with System Data</div>
                </div>
            </div>
            <div id="chat-messages">
                <div class="chat-msg msg-bot">Hello! I am your AI assistant. Ask me about the timetable, teachers, or classrooms.</div>
                <div class="typing-indicator" id="typing-indicator">
                    <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
                </div>
            </div>
            <div id="chat-input-area">
                <input type="text" id="chat-input" placeholder="Ask something..." autocomplete="off">
                <button id="chat-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
        <div id="chat-button">
            <i class="fas fa-robot"></i>
        </div>
    `;
    document.body.appendChild(container);

    // Logic
    const chatBtn = document.getElementById('chat-button');
    const chatWindow = document.getElementById('chat-window');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const chatMessages = document.getElementById('chat-messages');
    const typingIndicator = document.getElementById('typing-indicator');

    let isOpen = false;

    chatBtn.addEventListener('click', () => {
        isOpen = !isOpen;
        chatWindow.classList.toggle('active', isOpen);
        if (isOpen) chatInput.focus();
    });

    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chat-msg msg-\${sender}`;
        msgDiv.textContent = text;
        chatMessages.insertBefore(msgDiv, typingIndicator);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;
        
        addMessage(text, 'user');
        chatInput.value = '';
        typingIndicator.style.display = 'flex';
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const formData = new FormData();
            formData.append('message', text);
            
            const response = await fetch('chatbot_api.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            typingIndicator.style.display = 'none';
            addMessage(data.reply, 'bot');
            
            if(data.reply.includes("legacy")) {
                setTimeout(() => window.location.href='legacy_admin.php', 2000);
            }
        } catch (e) {
            typingIndicator.style.display = 'none';
            addMessage("I'm having trouble connecting to the mainframe. Please try again.", 'bot');
        }
    }

    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
})();
