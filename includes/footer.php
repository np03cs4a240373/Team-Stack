<!-- ====== END OF MAIN CONTENT ====== -->
</main>

<!-- ====== FOOTER ====== -->
<footer class="footer">
    <div class="footer-bottom" style="border-top:none; padding:1.5rem;">
        <p>&copy; 2026 KaamKhoji — Made for Nepal</p>
    </div>
</footer>

<!-- ====== CHATBOT ====== -->
<div id="chatbotWindow" class="chatbot-window" style="display:none;">
    <div class="chatbot-header">
        <div style="display:flex;align-items:center;gap:0.6rem;">
            <div class="chatbot-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div>
                <div style="font-weight:700;font-size:0.88rem;line-height:1.2;">KaamBot</div>
                <div style="font-size:0.72rem;opacity:0.8;">Your KaamKhoji Assistant</div>
            </div>
        </div>
        <button id="chatbotClose" class="chatbot-close" aria-label="Close chat">✕</button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages">
        <div class="chatbot-msg bot">
            <span>Hi! I'm KaamBot 👋 I can help you find jobs, write cover letters, or answer anything about KaamKhoji. How can I help?</span>
        </div>
    </div>
    <div class="chatbot-input-row">
        <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Ask me anything..." autocomplete="off" maxlength="500">
        <button id="chatbotSend" class="chatbot-send" aria-label="Send">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>
</div>

<button id="chatbotFab" class="chatbot-fab" title="Chat with KaamBot" aria-label="Open chatbot">
    <svg class="fab-icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        <circle cx="9"  cy="10" r="1" fill="currentColor" stroke="none"/>
        <circle cx="12" cy="10" r="1" fill="currentColor" stroke="none"/>
        <circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/>
    </svg>
    <svg class="fab-icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
</button>

<style>
/* FAB */
.chatbot-fab {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9999;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00b4d8, #0096b3);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(0,180,216,0.45);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.chatbot-fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 28px rgba(0,180,216,0.6);
}
.chatbot-fab svg { width:24px;height:24px; }

/* Window */
.chatbot-window {
    position: fixed;
    bottom: 96px;
    right: 28px;
    z-index: 9998;
    width: 340px;
    height: 500px;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    overflow: hidden;
    animation: chatSlideUp 0.22s ease;
}
@keyframes chatSlideUp {
    from { opacity:0; transform:translateY(18px); }
    to   { opacity:1; transform:translateY(0); }
}

/* Header */
.chatbot-header {
    background: linear-gradient(135deg, #00b4d8, #0096b3);
    color: #fff;
    padding: 0.85rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.chatbot-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.chatbot-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    opacity: 0.8;
    line-height: 1;
    padding: 2px 4px;
}
.chatbot-close:hover { opacity:1; }

/* Messages */
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    background: #f8fafc;
}
.chatbot-msg {
    display: flex;
    max-width: 88%;
}
.chatbot-msg.bot  { align-self: flex-start; }
.chatbot-msg.user { align-self: flex-end; }
.chatbot-msg span {
    padding: 0.55rem 0.85rem;
    border-radius: 14px;
    font-size: 0.83rem;
    line-height: 1.5;
    white-space: pre-wrap;
    word-break: break-word;
}
.chatbot-msg.bot  span { background:#fff; color:#1e293b; border:1px solid #e2e8f0; border-bottom-left-radius:4px; }
.chatbot-msg.user span { background: linear-gradient(135deg,#00b4d8,#0096b3); color:#fff; border-bottom-right-radius:4px; }

/* Typing dots */
.chatbot-typing span {
    display: inline-flex;
    gap: 3px;
    align-items: center;
    padding: 0.55rem 0.85rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    border-bottom-left-radius: 4px;
}
.chatbot-typing span::before,
.chatbot-typing span::after,
.chatbot-typing span b {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #94a3b8;
    animation: dotBounce 1.2s infinite ease-in-out;
}
.chatbot-typing span::before { animation-delay: 0s; }
.chatbot-typing span b       { animation-delay: 0.2s; font-style:normal; font-size:0; line-height:0; width:6px; height:6px; border-radius:50%; background:#94a3b8; display:inline-block; }
.chatbot-typing span::after  { animation-delay: 0.4s; }
@keyframes dotBounce {
    0%,80%,100% { transform:translateY(0); }
    40%         { transform:translateY(-5px); }
}

/* Input row */
.chatbot-input-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 0.75rem;
    border-top: 1px solid #e2e8f0;
    background: #fff;
    flex-shrink: 0;
}
.chatbot-input {
    flex: 1;
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: 0.5rem 0.9rem;
    font-size: 0.83rem;
    outline: none;
    transition: border-color 0.2s;
    font-family: inherit;
}
.chatbot-input:focus { border-color: #00b4d8; }
.chatbot-send {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg,#00b4d8,#0096b3);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.15s;
}
.chatbot-send:hover  { transform: scale(1.08); }
.chatbot-send:disabled { opacity:0.5; cursor:default; transform:none; }

@media (max-width: 400px) {
    .chatbot-window { width: calc(100vw - 24px); right: 12px; }
    .chatbot-fab    { right: 16px; bottom: 20px; }
}
</style>

<script>
(function () {
    const fab      = document.getElementById('chatbotFab');
    const win      = document.getElementById('chatbotWindow');
    const closeBtn = document.getElementById('chatbotClose');
    const input    = document.getElementById('chatbotInput');
    const sendBtn  = document.getElementById('chatbotSend');
    const msgBox   = document.getElementById('chatbotMessages');
    const iconOpen = fab.querySelector('.fab-icon-open');
    const iconX    = fab.querySelector('.fab-icon-close');

    let history = [];
    let open    = false;

    function toggleChat() {
        open = !open;
        win.style.display   = open ? 'flex' : 'none';
        iconOpen.style.display = open ? 'none'  : '';
        iconX.style.display    = open ? ''      : 'none';
        if (open) input.focus();
    }

    fab.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    function appendMsg(role, text) {
        const div  = document.createElement('div');
        div.className = 'chatbot-msg ' + role;
        const span = document.createElement('span');
        span.textContent = text;
        div.appendChild(span);
        msgBox.appendChild(div);
        msgBox.scrollTop = msgBox.scrollHeight;
        return div;
    }

    function showTyping() {
        const div  = document.createElement('div');
        div.className = 'chatbot-msg bot chatbot-typing';
        div.innerHTML = '<span><b></b></span>';
        msgBox.appendChild(div);
        msgBox.scrollTop = msgBox.scrollHeight;
        return div;
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        input.value   = '';
        sendBtn.disabled = true;

        appendMsg('user', text);
        const typing = showTyping();

        try {
            const res  = await fetch(BASE_URL + '/api/chatbot.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ message: text, history }),
            });
            const data = await res.json();
            typing.remove();

            const reply = data.reply || 'KaamBot is temporarily unavailable. Please try again shortly.';
            appendMsg('bot', reply);

            history.push({ role: 'user',      content: text  });
            history.push({ role: 'assistant', content: reply });
        } catch (e) {
            typing.remove();
            appendMsg('bot', 'KaamBot is temporarily unavailable. Please check your connection and try again.');
        }

        sendBtn.disabled = false;
        input.focus();
    }

    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
})();
</script>

<!-- JavaScript -->
<script src="<?= BASE_URL ?>/js/utils.js"></script>
<script src="<?= BASE_URL ?>/js/nav.js"></script>
<script src="<?= BASE_URL ?>/js/flash.js"></script>
<script src="<?= BASE_URL ?>/js/form-validation.js"></script>
<script src="<?= BASE_URL ?>/js/jobs.js"></script>
<script src="<?= BASE_URL ?>/js/apply-job.js"></script>
<?php if (!empty($pageJs)): ?>
<script src="<?= BASE_URL ?>/js/<?= htmlspecialchars($pageJs) ?>.js"></script>
<?php endif; ?>

</body>
</html>
