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
<title>✨ El chat de Merlin</title>
<link rel="stylesheet" href="assets/css/chat.css">
<style>
:root { --user-color: <?php echo htmlspecialchars($pref['color_preferido']); ?>; }
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="<?php echo htmlspecialchars($pref['tema']); ?>">

<!-- Header -->
<header class="header">
    <h1>
     <i class="fa-solid fa-hat-wizard"></i>
        MERLIN
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
                            <i class="fa-solid fa-hat-wizard"></i>
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
