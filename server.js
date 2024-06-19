const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const path = require('path');
const cors = require('cors');
const mysql = require('mysql2');

// Initialize express app
const app = express();
const server = http.createServer(app);

// MySQL connection
const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'test2'
});

connection.connect(err => {
    if (err) {
        console.error('Error connecting to MySQL:', err.stack);
        return;
    }
    console.log('Connected to MySQL as id', connection.threadId);
});

// Define allowed origins
const allowedOrigins = [
    'http://192.168.1.10:81',
    'http://77.85.205.5:81',
    'http://localhost:81'
];

// Configure Socket.io with dynamic CORS origin
const io = socketIo(server, {
    cors: {
        origin: function (origin, callback) {
            if (!origin || allowedOrigins.includes(origin)) {
                return callback(null, true);
            } else {
                return callback(new Error('Not allowed by CORS'));
            }
        },
        methods: ["GET", "POST"]
    }
});

// Set the view engine to Twig
app.set('view engine', 'twig');
app.set('views', path.join(__dirname, 'views'));

// Middleware to serve static files
app.use(express.static(path.join(__dirname, 'public')));

// Track the number of connected users
let userCount = 0;

// Route to render chat page
app.get('/chat', (req, res) => {
    res.render('chat/chat.html.twig');
});

// Retrieve chat history
app.get('/chat/history/:room', (req, res) => {
    const room = req.params.room;
    connection.query('SELECT m.*, u.username FROM message m JOIN user u ON m.user_id = u.id WHERE m.room = ? ORDER BY m.timestamp ASC', [room], (err, results) => {
        if (err) {
            res.status(500).send(err.message);
            return;
        }
        res.json(results);
    });
});

// Socket.io connection handling
io.on('connection', (socket) => {
    userCount++;
    console.log(`A user connected. Total users: ${userCount}`);

    socket.on('join private chat', ({ room, username, profilePicturePath }) => {
        socket.join(room);
        console.log(`${username} joined room: ${room}`);

        // Send chat history to the user who joined
        connection.query('SELECT m.*, u.username FROM message m JOIN user u ON m.user_id = u.id WHERE m.room = ? ORDER BY m.timestamp ASC', [room], (err, results) => {
            if (err) {
                console.error('Error retrieving chat history:', err.message);
                return;
            }
            const messages = results.map(msg => ({
                user: msg.username,
                message: msg.message,
                timestamp: msg.timestamp,
                profilePicture: profilePicturePath // Use the provided profile picture path
            }));
            socket.emit('chat history', messages);
        });
    });

    socket.on('private message', (data) => {
        const { room, username, message, timestamp, profilePicturePath } = data;
        connection.query('SELECT id FROM user WHERE username = ?', [username], (err, results) => {
            if (err) {
                console.error('Error fetching user:', err.message);
                return;
            }
            if (results.length === 0) {
                console.error('User not found');
                return;
            }
            const userId = results[0].id;

            connection.query('INSERT INTO message (room, user_id, message, timestamp) VALUES (?, ?, ?, ?)', [room, userId, message, new Date(timestamp)], (err) => {
                if (err) {
                    console.error('Error saving message:', err.message);
                    return;
                }
                io.to(room).emit('chat message', { user: username, message, timestamp, profilePicture: profilePicturePath });
                console.log(`Message from ${username}: ${message}`);
            });
        });
    });

    socket.on('disconnect', () => {
        userCount--;
        console.log(`A user disconnected. Total users: ${userCount}`);
    });
});

// Start the server on port 3001
const PORT = process.env.PORT || 3001;
server.listen(PORT, '0.0.0.0', () => {
    console.log(`Server is running on port ${PORT}`);
});
