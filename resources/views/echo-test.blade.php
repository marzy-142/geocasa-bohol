<!DOCTYPE html>
<html>
<head>
    <title>Echo Test - Conversation 5</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Echo Connection Test</h1>
    <div id="status">Connecting...</div>
    <div id="messages"></div>

    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    
    <script>
        const log = (msg, color = 'black') => {
            const div = document.createElement('div');
            div.style.color = color;
            div.textContent = `[${new Date().toLocaleTimeString()}] ${msg}`;
            document.getElementById('messages').appendChild(div);
            console.log(msg);
        };

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        log('CSRF Token: ' + (csrfToken ? 'Found ✓' : 'Missing ✗'), csrfToken ? 'green' : 'red');

        try {
            window.Pusher = Pusher;
            
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: '{{ config('broadcasting.connections.reverb.key') }}',
                wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
                wsPort: {{ config('broadcasting.connections.reverb.options.port') }},
                wssPort: {{ config('broadcasting.connections.reverb.options.port') }},
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
                authHost: window.location.origin,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    withCredentials: true,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                },
                disableStats: true,
                enableLogging: true
            });

            log('Echo initialized', 'green');
            document.getElementById('status').textContent = 'Echo Initialized';
            document.getElementById('status').style.color = 'green';

            // Connection events
            window.Echo.connector.pusher.connection.bind('connected', () => {
                log('✅ WebSocket Connected!', 'green');
                document.getElementById('status').textContent = 'Connected to Reverb';
            });

            window.Echo.connector.pusher.connection.bind('error', (error) => {
                log('❌ Connection Error: ' + JSON.stringify(error), 'red');
            });

            // Subscribe to conversation.5
            log('Subscribing to private-conversation.5...', 'blue');
            
            window.Echo.private('conversation.5')
                .listen('MessageSent', (e) => {
                    log('📨 NEW MESSAGE RECEIVED!', 'purple');
                    log('Message: ' + JSON.stringify(e, null, 2), 'purple');
                    
                    // Show in UI
                    const msgDiv = document.createElement('div');
                    msgDiv.style.padding = '10px';
                    msgDiv.style.margin = '10px 0';
                    msgDiv.style.background = '#e7f5ff';
                    msgDiv.style.border = '2px solid #1971c2';
                    msgDiv.innerHTML = `
                        <strong>${e.message.sender?.name || 'Unknown'}</strong><br>
                        ${e.message.content}<br>
                        <small>${new Date().toLocaleTimeString()}</small>
                    `;
                    document.getElementById('messages').appendChild(msgDiv);
                })
                .error((error) => {
                    log('❌ Channel Error: ' + JSON.stringify(error), 'red');
                });

            log('Listener registered. Waiting for messages...', 'blue');

        } catch (error) {
            log('❌ Setup Error: ' + error.message, 'red');
            console.error(error);
        }
    </script>
</body>
</html>
