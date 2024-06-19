document.addEventListener('DOMContentLoaded', function () {
    console.log('Document loaded.');

    const messageHistoryElement = document.getElementById('message-history');
    if (messageHistoryElement) {
        console.log('Found #message-history element.');
        messageHistoryElement.addEventListener('click', function(e) {
            console.log('Clicked within message history:', e.target);
            if (e.target.tagName === 'LI') {
                const chatWindow = document.getElementById('chat-window');
                if (chatWindow) {
                    console.log('Found #chat-window element, updating content.');
                    chatWindow.innerHTML = ''; // Clear existing messages
                    const msgDiv = document.createElement('div');
                    msgDiv.classList.add('chat-message', 'bot');
                    const messageContent = document.createElement('div');
                    messageContent.classList.add('message');
                    messageContent.textContent = e.target.textContent;
                    msgDiv.appendChild(messageContent);
                    chatWindow.appendChild(msgDiv);
                } else {
                    console.log('Error: #chat-window element not found.');
                }
            }
        });
    } else {
        console.log('Error: #message-history element not found.');
    }
});