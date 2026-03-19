<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel WebSockets Chat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; display: flex; justify-content: center; padding: 2rem; }
        .container { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 600px; }
        .messages { height: 300px; max-height: 300px; overflow-y: auto; overflow-x: hidden; border: 1px solid #e5e7eb; padding: 1rem; border-radius: 4px; background: #f9fafb; margin-bottom: 1rem; word-wrap: break-word; }
        .message-box { background: #dbeafe; color: #1e3a8a; padding: 0.5rem 1rem; border-radius: 4px; margin-bottom: 0.5rem; word-break: break-all; }
        .input-group { display: flex; gap: 0.5rem; }
        input[type="text"] { flex: 1; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; }
        button { background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; }
        button:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="margin-top: 0;">Chat Público (Reverb)</h1>
        
        <div id="messages" class="messages">
            <div style="color: #6b7280; font-style: italic; font-size: 0.875rem;">Conectando al servidor WebSocket...</div>
        </div>

        <form id="chat-form" class="input-group">
            <input type="text" id="message-input" placeholder="Escribe un mensaje..." required>
            <button type="submit">Enviar</button>
        </form>
    </div>

    <script type="module">
        // Inicialización y escucha del Canal
        setTimeout(() => {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.innerHTML = '<div style="color: #10b981; font-size: 0.875rem;">✅ Conectado y escuchando mensajes de todos.</div>';

            Echo.channel('public-chat')
                .listen('.message.sent', (e) => {
                    console.log("Mensaje WS recibido:", e);
                    const msgElement = document.createElement('div');
                    msgElement.className = 'message-box';
                    msgElement.textContent = e.message;
                    messagesDiv.appendChild(msgElement);
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                });
        }, 1000);

        // Envío del mensaje al backend por HTTP
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const message = input.value;
            input.value = '';

            fetch('/chat/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => {
                if (!response.ok) {
                    console.error("Error al enviar", response);
                }
            });
        });
    </script>
</body>
</html>
