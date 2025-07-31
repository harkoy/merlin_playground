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
<link rel="stylesheet" href="assets/css/app.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="<?php echo htmlspecialchars($pref['tema']); ?>" data-user-color="<?php echo htmlspecialchars($pref['color_preferido']); ?>">

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
                                <button class="delete-btn" onclick="deleteMessage(<?php echo $m['id']; ?>, this)">
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
document.addEventListener('DOMContentLoaded', function() {
    document.documentElement.style.setProperty('--user-color', document.body.dataset.userColor);
    const form = document.querySelector('.input-form');
    const input = document.querySelector('.message-input');
    input.focus();
    scrollToBottom();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        const res = await fetch('chat_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'send', mensaje: text})
        });
        const data = await res.json();
        if (data.error) { alert(data.error); return; }
        appendMessage('usuario', text, data.user_id);
        appendMessage('asistente', data.reply, data.assistant_id);
        scrollToBottom();
        if (data.fin) { window.dispatchEvent(new Event('fin_info')); }
    });
});

function toggleSettings() {
    const panel = document.getElementById('settings-panel');
    panel.style.display = panel.style.display === 'none' || panel.style.display === '' ? 'block' : 'none';
}

function deleteMessage(id, btn) {
    if (!confirm('¿Estás seguro de que quieres eliminar este mensaje?')) return;
    fetch('chat_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id})
    }).then(r => r.json()).then(data => {
        if (data.success) {
            btn.closest('.message-container').remove();
        }
    });
}

function appendMessage(role, text, id) {
    const container = document.createElement('div');
    container.className = 'message-container ' + (role === 'usuario' ? 'usuario' : 'asistente');
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.innerHTML = role === 'usuario' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
    const content = document.createElement('div');
    content.className = 'message-content';
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message';
    messageDiv.innerHTML = text.replace(/\n/g, '<br>');
    const actions = document.createElement('div');
    actions.className = 'message-actions';
    const delBtn = document.createElement('button');
    delBtn.className = 'delete-btn';
    delBtn.innerHTML = '<i class="fas fa-trash"></i>';
    delBtn.onclick = () => deleteMessage(id, delBtn);
    actions.appendChild(delBtn);
    messageDiv.appendChild(actions);
    const timeDiv = document.createElement('div');
    timeDiv.className = 'message-time';
    const now = new Date();
    timeDiv.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    content.appendChild(messageDiv);
    content.appendChild(timeDiv);
    container.appendChild(avatar);
    container.appendChild(content);
    document.getElementById('chat-window').appendChild(container);
}

function scrollToBottom() {
    const chatWindow = document.getElementById('chat-window');
    chatWindow.scrollTop = chatWindow.scrollHeight;
}

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
