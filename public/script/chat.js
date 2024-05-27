document.getElementById('chat-form').addEventListener('submit', (event) => {
    event.preventDefault();

    const username = document.querySelector('input[name="username"]').value;
    const message = document.querySelector('textarea[name="message"]').value;

    fetch('/message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({ username, message })
    }).then(response => {
        if (response.ok) {
            document.querySelector('textarea[name="message"]').value = '';
        }

        document.querySelector('textarea[name="message"]').focus();
    }).catch(error => console.error('Error:', error));
});

const textarea = document.querySelector('textarea[name="message"]');

textarea.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('chat-form').requestSubmit();
    }
});

const eventSource = new EventSource('/subscribe');

eventSource.addEventListener('message', (event) => {
    console.log('received message: ' + event.data);

    const data = JSON.parse(event.data);
    const chatContainer = document.getElementById('chat-container');
    const messageElement = document.createElement('div');
    if (data.username === 'system') {
        messageElement.className = 'uk-alert-danger';
    } else if (data.username === username) {
        messageElement.className = 'uk-alert-primary';
    } else {
        messageElement.className = 'uk-alert-secondary';
    }

    messageElement.setAttribute('uk-alert', '');
    messageElement.innerHTML = `<p><strong>${data.username}:</strong> ${data.message}</p>`;
    const messageContainer = document.createElement('div');
    messageContainer.append(messageElement);
    chatContainer.prepend(messageContainer);

    requestAnimationFrame(() => {
        chatContainer.scrollTop = 0;
    });
});
