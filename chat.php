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
    $pref = ['tema' => 'light', 'color_preferido' => '#4CAF50'];
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
        $basePrompts = include 'prompts.php';
        $messages = array_merge($basePrompts, $messages);
    }

    $respuesta = call_openai_api($messages);

    $stmt = $pdo->prepare("INSERT INTO mensajes (conversacion_id, emisor, texto) VALUES (?, 'asistente', ?)");
    $stmt->execute([$conver_id, $respuesta]);
}

// Obtener mensajes para mostrar
$stmt = $pdo->prepare("SELECT emisor, texto, fecha_envio FROM mensajes WHERE conversacion_id = ? ORDER BY id");
$stmt->execute([$conver_id]);
$mensajes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo htmlspecialchars($pref['tema']); ?>">
<head>
<meta charset="UTF-8">
<title>Chat</title>
<style>
body.light { background: #fff; color: #000; }
body.dark { background: #121212; color: #eee; }
.user { text-align: right; }
.message { margin: 5px 0; padding: 8px; border-radius: 4px; max-width: 70%; }
.user .message { background: <?php echo htmlspecialchars($pref['color_preferido']); ?>; color: #fff; margin-left: auto; }
.assistant .message { background: #ccc; color: #000; }
</style>
</head>
<body class="<?php echo htmlspecialchars($pref['tema']); ?>">
<h1>Chat</h1>
<form method="post" style="margin-bottom:1em;">
    Tema:
    <select name="tema">
        <option value="light" <?php if($pref['tema']==='light') echo 'selected'; ?>>Claro</option>
        <option value="dark" <?php if($pref['tema']==='dark') echo 'selected'; ?>>Oscuro</option>
    </select>
    Color: <input type="color" name="color" value="<?php echo htmlspecialchars($pref['color_preferido']); ?>">
    <button type="submit">Guardar</button>
</form>
<div id="chat">
<?php foreach ($mensajes as $m): ?>
    <div class="<?php echo $m['emisor']; ?>">
        <div class="message">
            <?php echo nl2br(htmlspecialchars($m['texto'])); ?>
        </div>
    </div>
<?php endforeach; ?>
</div>
<form method="post">
    <input name="mensaje" placeholder="Escribe un mensaje" style="width:80%;">
    <button type="submit">Enviar</button>
</form>
<p>
    <a href="profile.php">Mi perfil</a> |
    <a href="logout.php">Cerrar sesión</a>
</p>
</body>
</html>
