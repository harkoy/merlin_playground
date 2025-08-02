<?php
session_start();
require 'db.php';
require 'openai.php';
require 'chat_helpers.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

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

// Finalizar y generar informe
if (isset($_POST['finalizar'])) {
    $stmt = $pdo->prepare("SELECT emisor, texto FROM mensajes WHERE conversacion_id = ? ORDER BY id");
    $stmt->execute([$conver_id]);
    $historial = $stmt->fetchAll();
    $messages = build_base_messages($pdo, $usuario_id);
    foreach ($historial as $m) {
        $messages[] = ['role' => $m['emisor'] === 'usuario' ? 'user' : 'assistant', 'content' => $m['texto']];
    }
    $messages[] = ['role' => 'system', 'content' => 'Genera un informe estructurado con toda la información recopilada listo para un diseñador o marketer.'];
    $analysis = call_openai_api($messages);
    $stmt = $pdo->prepare("INSERT INTO resultados_analisis (usuario_id, analisis) VALUES (?, ?) ON DUPLICATE KEY UPDATE analisis = VALUES(analisis), fecha_registro = CURRENT_TIMESTAMP");
    $stmt->execute([$usuario_id, $analysis]);
    header('Location: summary.php');
    exit;
}

// Enviar mensaje
if (isset($_POST['mensaje']) && trim($_POST['mensaje']) !== '') {
    $mensaje = trim($_POST['mensaje']);
    $stmt = $pdo->prepare("INSERT INTO mensajes (conversacion_id, emisor, texto) VALUES (?, 'usuario', ?)");
    $stmt->execute([$conver_id, $mensaje]);

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
        $base = build_base_messages($pdo, $usuario_id);
        $messages = array_merge($base, $messages);
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
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat de Marca</title>
<link rel="stylesheet" href="assets/css/chat.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="chat-container">
    <div class="chat-window" id="chat-window">
        <?php if (empty($mensajes)): ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>¡Bienvenido!</h3>
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

    <div class="input-area">
        <form method="post" class="input-form">
            <input
                name="mensaje"
                class="message-input"
                placeholder="Escribe tu mensaje..."
                type="text"
                autocomplete="off"
                required
            >
            <button type="submit" class="send-btn">
                <i class="fas fa-paper-plane"></i>
                <span>Enviar</span>
            </button>
        </form>
        <form method="post" class="finalizar-form">
            <button type="submit" name="finalizar" class="finalize-btn">Finalizar y generar informe</button>
        </form>
        <div class="mode-choice">
            <a href="questionnaire.php">Responder preguntas directamente</a>
        </div>
    </div>
</div>

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
function deleteMessage(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este mensaje?')) {
        window.location.href = '?del_msg=' + id;
    }
}
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
</script>

<?php include 'branch.php'; ?>

</body>
</html>
