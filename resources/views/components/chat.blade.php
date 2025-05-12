@props(['projeto_id'])

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
</head>

<div id="chat-container">
    <div id="chat-header">
        Chat do Projeto
        <span id="chat-notif">!</span>
    </div>

    <div id="chat-body">
        <div id="chat-messages"></div>

        <div id="chat-footer">
            <textarea id="chat-input" placeholder="Digite sua mensagem..."></textarea>
            <button id="chat-send">Enviar</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let chatOpen = false;

        const chatHeader = document.getElementById('chat-header');
        const chatBody = document.getElementById('chat-body');
        const chatNotif = document.getElementById('chat-notif');
        const chatSend = document.getElementById('chat-send');
        const chatInput = document.getElementById('chat-input');
        const chatMessages = document.getElementById('chat-messages');
        const projetoId = {{ $projeto_id ?? 1 }};


        chatNotif.style.display = 'none';

        chatHeader.addEventListener('click', function () {
            chatOpen = !chatOpen;

            if (chatOpen) {
                chatBody.classList.add('open');
                chatNotif.style.display = 'none';
                carregarMensagens();
            } else {
                chatBody.classList.remove('open');
            }
        });


        function carregarMensagens() {
            fetch(`/chat/${projetoId}/messages`)
                .then(resp => resp.json())
                .then(data => {
                    chatMessages.innerHTML = '';
                    let lastDate = null;

                    data.forEach(msg => {
                        const msgDate = new Date(msg.created_at);
                        const dateStr = msgDate.toLocaleDateString();

                        if (dateStr !== lastDate) {
                            lastDate = dateStr;
                            const dateDiv = document.createElement('div');
                            dateDiv.classList.add('chat-date-group');
                            dateDiv.textContent = dateStr;
                            chatMessages.appendChild(dateDiv);
                        }

                        const msgDiv = document.createElement('div');
                        msgDiv.classList.add('chat-message');
                        const time = msgDate.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit', hour12: false });
                        msgDiv.innerHTML = `<strong>${msg.usuario}</strong> (${time}): ${msg.mensagem}`;
                        chatMessages.appendChild(msgDiv);
                    });

                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        chatSend.addEventListener('click', function () {
            const mensagem = chatInput.value.trim();
            if (!mensagem) return;

            fetch(`/chat/${projetoId}/messages`, {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ mensagem })
            }).then(response => {
                if (!response.ok) throw new Error();
                chatInput.value = '';
                carregarMensagens();
            }).catch(error => {
                console.error('Erro ao enviar mensagem:', error);
            });
        });

        carregarMensagens();

        setInterval(() => {
            if (!chatOpen) {
                fetch(`/chat/${projetoId}/messages`)
                    .then(resp => resp.json())
                    .then(data => {
                        if (data.length > 0) {
                            chatNotif.style.display = 'inline';
                        }
                    });
            } else {
                carregarMensagens();
            }
        }, 5000);
    });
</script>
