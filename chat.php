<?php
session_start();
require 'db.php';
require 'openai.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener preferencias de diseño o crear valores por defecto
$stmt = $pdo->prepare("SELECT tema, color_preferido FROM preferencias_disenio WHERE usuario_id = ? LIMIT 1");
$stmt->execute([$usuario_id]);
$pref = $stmt->fetch();
if (!$pref) {
    $pref = ['tema' => 'dark', 'color_preferido' => '#D4AF37'];
    $stmt = $pdo->prepare("INSERT INTO preferencias_disenio (usuario_id, tema, color_preferido) VALUES (?, ?, ?)");
    $stmt->execute([$usuario_id, $pref['tema'], $pref['color_preferido']]);
}

// Actualizar preferencias si se envían por formulario
if (isset($_POST['tema']) && isset($_POST['color'])) {
    $pref['tema'] = $_POST['tema'];
    $pref['color_preferido'] = $_POST['color'];
    $stmt = $pdo->prepare("UPDATE preferencias_disenio SET tema = ?, color_preferido = ? WHERE usuario_id = ?");
    $stmt->execute([$pref['tema'], $pref['color_preferido'], $usuario_id]);
}

// Buscar o crear conversación
$stmt = $pdo->prepare("SELECT id FROM conversaciones WHERE usuario_id = ? LIMIT 1");
$stmt->execute([$usuario_id]);
$conver = $stmt->fetch();
if (!$conver) {
    $stmt = $pdo->prepare("INSERT INTO conversaciones (usuario_id) VALUES (?)");
    $stmt->execute([$usuario_id]);
    $conver_id = $pdo->lastInsertId();
} else {
    $conver_id = $conver['id'];
}

// Borrar mensaje si el usuario lo solicita
if (isset($_GET['del_msg'])) {
    $delId = (int)$_GET['del_msg'];
    $stmt = $pdo->prepare('SELECT m.id FROM mensajes m JOIN conversaciones c ON m.conversacion_id = c.id WHERE m.id = ? AND c.usuario_id = ? LIMIT 1');
    $stmt->execute([$delId, $usuario_id]);
    if ($stmt->fetch()) {
        $del = $pdo->prepare('DELETE FROM mensajes WHERE id = ?');
        $del->execute([$delId]);
    }
    header('Location: chat.php');
    exit;
}

// Enviar mensaje
if (isset($_POST['mensaje']) && trim($_POST['mensaje']) !== '') {
    $mensaje = trim($_POST['mensaje']);
    $stmt = $pdo->prepare("INSERT INTO mensajes (conversacion_id, emisor, texto) VALUES (?, 'usuario', ?)");
    $stmt->execute([$conver_id, $mensaje]);

    // Detectar preferencias de color o tema en el mensaje
    $mensajeLower = strtolower($mensaje);
    $colorMap = [
        'rojo' => '#ff0000',
        'red' => '#ff0000',
        'verde' => '#008000',
        'green' => '#008000',
        'azul' => '#0000ff',
        'blue' => '#0000ff',
        'amarillo' => '#ffff00',
        'yellow' => '#ffff00',
        'naranja' => '#ffa500',
        'orange' => '#ffa500',
        'violeta' => '#800080',
        'morado' => '#800080',
        'purple' => '#800080',
        'negro' => '#000000',
        'black' => '#000000',
        'blanco' => '#ffffff',
        'white' => '#ffffff',
        'gris' => '#808080',
        'gray' => '#808080',
        'grey' => '#808080',
        'rosa' => '#ff69b4',
        'pink' => '#ff69b4',
        'dorado' => '#D4AF37',
        'gold' => '#D4AF37'
    ];
    $newColor = null;
    if (preg_match('/#([0-9a-f]{3,6})/i', $mensajeLower, $m)) {
        $newColor = '#' . $m[1];
    } elseif (isset($colorMap[$mensajeLower])) {
        $newColor = $colorMap[$mensajeLower];
    }
    if ($newColor) {
        $pref['color_preferido'] = $newColor;
        $stmt = $pdo->prepare('UPDATE preferencias_disenio SET color_preferido = ? WHERE usuario_id = ?');
        $stmt->execute([$newColor, $usuario_id]);
    }
    if (strpos($mensajeLower, 'oscuro') !== false || strpos($mensajeLower, 'dark') !== false) {
        $pref['tema'] = 'dark';
        $stmt = $pdo->prepare('UPDATE preferencias_disenio SET tema = ? WHERE usuario_id = ?');
        $stmt->execute(['dark', $usuario_id]);
    }
    if (strpos($mensajeLower, 'claro') !== false || strpos($mensajeLower, 'light') !== false) {
        $pref['tema'] = 'light';
        $stmt = $pdo->prepare('UPDATE preferencias_disenio SET tema = ? WHERE usuario_id = ?');
        $stmt->execute(['light', $usuario_id]);
    }

    // Construir historial para la API
    $stmt = $pdo->prepare("SELECT emisor, texto FROM mensajes WHERE conversacion_id = ? ORDER BY id");
    $stmt->execute([$conver_id]);
    $historial = $stmt->fetchAll();
    $messages = [];
    foreach ($historial as $m) {
        $messages[] = ['role' => $m['emisor'] === 'usuario' ? 'user' : 'assistant', 'content' => $m['texto']];
    }

    // Prompt inicial y preguntas base si es el primer mensaje
    if (count($messages) === 1) {
        $setStmt = $pdo->prepare('SELECT prompt_set_id FROM usuarios WHERE id = ?');
        $setStmt->execute([$usuario_id]);
        $setId = $setStmt->fetchColumn();
        if ($setId) {
            $pstmt = $pdo->prepare('SELECT role, content FROM prompt_lines WHERE set_id = ? ORDER BY orden');
            $pstmt->execute([$setId]);
            $basePrompts = [];
            foreach ($pstmt->fetchAll() as $p) {
                $basePrompts[] = ['role' => $p['role'], 'content' => $p['content']];
            }
            $messages = array_merge($basePrompts, $messages);
        }
    }

    $respuesta = call_openai_api($messages);

    $stmt = $pdo->prepare("INSERT INTO mensajes (conversacion_id, emisor, texto) VALUES (?, 'asistente', ?)");
    $stmt->execute([$conver_id, $respuesta]);
}

// Obtener mensajes para mostrar
$stmt = $pdo->prepare("SELECT id, emisor, texto, fecha_envio FROM mensajes WHERE conversacion_id = ? ORDER BY id");
$stmt->execute([$conver_id]);
$mensajes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo htmlspecialchars($pref['tema']); ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>✨ Celestial Chat</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --user-color: <?php echo htmlspecialchars($pref['color_preferido']); ?>;
    --primary-gold: var(--user-color);
    --secondary-gold: var(--user-color);
    --deep-blue: #0B1426;
    --navy-blue: #1A2332;
    --light-blue: #2C3E50;
    --accent-blue: #34495E;
    --text-light: #ECF0F1;
    --text-muted: #BDC3C7;
    --shadow-gold: rgba(212, 175, 55, 0.3);
    --gradient-bg: linear-gradient(135deg, #0B1426 0%, var(--user-color) 100%);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gradient-bg);
    color: var(--text-light);
    min-height: 100vh;
    overflow-x: hidden;
}

body.light {
    --deep-blue: #F8F9FA;
    --navy-blue: #E9ECEF;
    --light-blue: #DEE2E6;
    --accent-blue: #CED4DA;
    --text-light: #212529;
    --text-muted: #6C757D;
    --gradient-bg: linear-gradient(135deg, #F8F9FA 0%, var(--user-color) 100%);
}

/* Header */
.header {
    background: rgba(11, 20, 38, 0.9);
    backdrop-filter: blur(10px);
    padding: 1rem 2rem;
    border-bottom: 2px solid var(--primary-gold);
    position: sticky;
    top: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 20px var(--shadow-gold);
}

.header h1 {
    color: var(--secondary-gold);
    font-size: 1.8rem;
    font-weight: 300;
    letter-spacing: 2px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.header .star-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.settings-btn {
    background: transparent;
    border: 2px solid var(--primary-gold);
    color: var(--primary-gold);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.settings-btn:hover {
    background: var(--primary-gold);
    color: var(--deep-blue);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px var(--shadow-gold);
}

/* Settings Panel */
.settings-panel {
    background: rgba(26, 35, 50, 0.95);
    backdrop-filter: blur(15px);
    border: 2px solid var(--primary-gold);
    border-radius: 15px;
    padding: 1.5rem;
    margin: 1rem 2rem;
    box-shadow: 0 8px 32px var(--shadow-gold);
    display: none;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.settings-row {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.settings-row label {
    color: var(--secondary-gold);
    font-weight: 500;
    min-width: 80px;
}

.settings-row select, .settings-row input[type="color"] {
    background: var(--accent-blue);
    border: 2px solid var(--primary-gold);
    color: var(--text-light);
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.settings-row select:focus, .settings-row input[type="color"]:focus {
    outline: none;
    box-shadow: 0 0 10px var(--shadow-gold);
    transform: scale(1.05);
}

/* Chat Container */
.chat-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 120px);
}

.chat-window {
    flex: 1;
    background: rgba(26, 35, 50, 0.6);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 2px solid var(--primary-gold);
    padding: 1.5rem;
    margin-bottom: 1rem;
    overflow-y: auto;
    box-shadow: inset 0 4px 20px rgba(0, 0, 0, 0.3);
    scroll-behavior: smooth;
}

.chat-window::-webkit-scrollbar {
    width: 8px;
}

.chat-window::-webkit-scrollbar-track {
    background: rgba(11, 20, 38, 0.5);
    border-radius: 10px;
}

.chat-window::-webkit-scrollbar-thumb {
    background: var(--primary-gold);
    border-radius: 10px;
}

/* Messages */
.message-container {
    margin: 1rem 0;
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.message-container.user {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.message-container.user .message-avatar {
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    color: var(--deep-blue);
}

.message-container.assistant .message-avatar {
    background: linear-gradient(135deg, var(--light-blue), var(--accent-blue));
    color: var(--secondary-gold);
}

.message-content {
    max-width: 70%;
    position: relative;
}

.message {
    padding: 1rem 1.5rem;
    border-radius: 18px;
    position: relative;
    line-height: 1.6;
    word-wrap: break-word;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.message-container.user .message {
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    color: var(--deep-blue);
    border-bottom-right-radius: 5px;
}

.message-container.assistant .message {
    background: rgba(52, 73, 94, 0.8);
    backdrop-filter: blur(10px);
    color: var(--text-light);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-bottom-left-radius: 5px;
}

.message-actions {
    position: absolute;
    top: -10px;
    right: -10px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.message-container:hover .message-actions {
    opacity: 1;
}

.delete-btn {
    background: rgba(231, 76, 60, 0.9);
    border: none;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.delete-btn:hover {
    background: #E74C3C;
    transform: scale(1.1);
}

.message-time {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.3rem;
    opacity: 0.7;
}

/* Input Area */
.input-area {
    background: rgba(26, 35, 50, 0.9);
    backdrop-filter: blur(15px);
    border-radius: 25px;
    border: 2px solid var(--primary-gold);
    padding: 1rem;
    box-shadow: 0 -4px 20px var(--shadow-gold);
}

.input-form {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.message-input {
    flex: 1;
    background: rgba(52, 73, 94, 0.5);
    border: 2px solid rgba(212, 175, 55, 0.3);
    color: var(--text-light);
    padding: 1rem 1.5rem;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.message-input:focus {
    border-color: var(--primary-gold);
    box-shadow: 0 0 15px var(--shadow-gold);
    background: rgba(52, 73, 94, 0.8);
}

.message-input::placeholder {
    color: var(--text-muted);
}

.send-btn {
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    border: none;
    color: var(--deep-blue);
    padding: 1rem 1.5rem;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.send-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--shadow-gold);
}

.send-btn:active {
    transform: translateY(0);
}

/* Navigation */
.navigation {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(11, 20, 38, 0.95);
    backdrop-filter: blur(15px);
    border-top: 2px solid var(--primary-gold);
    padding: 1rem 2rem;
    display: flex;
    justify-content: center;
    gap: 2rem;
    z-index: 50;
}

.nav-link {
    color: var(--text-muted);
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 15px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-link:hover {
    color: var(--primary-gold);
    background: rgba(212, 175, 55, 0.1);
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .header {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
    }
    
    .settings-panel {
        margin: 1rem;
    }
    
    .chat-container {
        padding: 1rem;
        height: calc(100vh - 160px);
    }
    
    .message-content {
        max-width: 85%;
    }
    
    .navigation {
        padding: 1rem;
        gap: 1rem;
    }
    
    .nav-link span {
        display: none;
    }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 3rem;
    color: var(--primary-gold);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: var(--secondary-gold);
    margin-bottom: 0.5rem;
}
</style>
</head>
<body class="<?php echo htmlspecialchars($pref['tema']); ?>">

<!-- Header -->
<header class="header">
    <h1>
        <i class="fas fa-star star-icon"></i>
        Celestial Chat
    </h1>
    <div class="header-actions">
        <button class="settings-btn" onclick="toggleSettings()">
            <i class="fas fa-cog"></i> Configuración
        </button>
    </div>
</header>

<!-- Settings Panel -->
<div id="settings-panel" class="settings-panel">
    <form method="post">
        <div class="settings-row">
            <label><i class="fas fa-palette"></i> Tema:</label>
            <select name="tema">
                <option value="dark" <?php if($pref['tema']==='dark') echo 'selected'; ?>>🌙 Oscuro</option>
                <option value="light" <?php if($pref['tema']==='light') echo 'selected'; ?>>☀️ Claro</option>
            </select>
        </div>
        <div class="settings-row">
            <label><i class="fas fa-paint-brush"></i> Color:</label>
            <input type="color" name="color" value="<?php echo htmlspecialchars($pref['color_preferido']); ?>">
            <button type="submit" class="settings-btn">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
</div>

<!-- Chat Container -->
<div class="chat-container">
    <div class="chat-window" id="chat-window">
        <?php if (empty($mensajes)): ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>¡Bienvenido al Chat Celestial!</h3>
                <p>Comienza una conversación escribiendo tu primer mensaje.</p>
            </div>
        <?php else: ?>
            <?php foreach ($mensajes as $m): ?>
                <div class="message-container <?php echo $m['emisor']; ?>">
                    <div class="message-avatar">
                        <?php if ($m['emisor'] === 'usuario'): ?>
                            <i class="fas fa-user"></i>
                        <?php else: ?>
                            <i class="fas fa-robot"></i>
                        <?php endif; ?>
                    </div>
                    <div class="message-content">
                        <div class="message">
                            <?php echo nl2br(htmlspecialchars($m['texto'])); ?>
                            <div class="message-actions">
                                <button class="delete-btn" onclick="deleteMessage(<?php echo $m['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="message-time">
                            <?php echo date('H:i', strtotime($m['fecha_envio'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Input Area -->
    <div class="input-area">
        <form method="post" class="input-form">
            <input 
                name="mensaje" 
                class="message-input"
                placeholder="✨ Escribe tu mensaje mágico aquí..." 
                type="text"
                autocomplete="off"
                required
            >
            <button type="submit" class="send-btn">
                <i class="fas fa-paper-plane"></i>
                <span>Enviar</span>
            </button>
        </form>
    </div>
</div>

<!-- Navigation -->
<nav class="navigation">
    <a href="profile.php" class="nav-link">
        <i class="fas fa-user-circle"></i>
        <span>Mi Perfil</span>
    </a>
    <a href="logout.php" class="nav-link">
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
    </a>
</nav>

<script>
// Toggle Settings Panel
function toggleSettings() {
    const panel = document.getElementById('settings-panel');
    panel.style.display = panel.style.display === 'none' || panel.style.display === '' ? 'block' : 'none';
}

// Delete Message
function deleteMessage(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este mensaje?')) {
        window.location.href = '?del_msg=' + id;
    }
}

// Auto-scroll to bottom
function scrollToBottom() {
    const chatWindow = document.getElementById('chat-window');
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.querySelector('.message-input');
    messageInput.focus();
    scrollToBottom();
});

document.querySelector('.message-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.closest('form').submit();
    }
});

document.addEventListener('click', function(e) {
    const panel = document.getElementById('settings-panel');
    const settingsBtn = document.querySelector('.settings-btn');
    
    if (!panel.contains(e.target) && !settingsBtn.contains(e.target)) {
        panel.style.display = 'none';
    }
});

let typingDots = 0;
setInterval(() => {
    const input = document.querySelector('.message-input');
    if (document.activeElement === input && input.value === '') {
        typingDots = (typingDots + 1) % 4;
        const dots = '.'.repeat(typingDots);
        input.placeholder = `✨ Escribe tu mensaje mágico aquí${dots}`;
    }
}, 500);
</script>

</body>
</html>
