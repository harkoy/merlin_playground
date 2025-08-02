<?php
session_start();
require 'db.php';
require 'openai.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT conversacion_id, resumen_json FROM branding_briefs WHERE id = ?');
$stmt->execute([$id]);
$brief = $stmt->fetch();
if (!$brief) {
    echo 'Brief no encontrado';
    exit;
}
$conver_id = $brief['conversacion_id'];
$resumen = json_decode($brief['resumen_json'], true);
$pretty = htmlspecialchars(json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$lastStmt = $pdo->prepare('SELECT MAX(fecha_envio) FROM mensajes WHERE conversacion_id = ?');
$lastStmt->execute([$conver_id]);
$lastTime = $lastStmt->fetchColumn();
$inactive = false;
if ($lastTime && (time() - strtotime($lastTime) >= 600)) {
    $inactive = true;
    end_conversation($conver_id, $pdo);
}

function end_conversation($cid, $pdo) {
    $stmt = $pdo->prepare('SELECT emisor, texto FROM mensajes WHERE conversacion_id = ? ORDER BY id');
    $stmt->execute([$cid]);
    $hist = $stmt->fetchAll();
    $messages = [];
    foreach ($hist as $m) {
        $messages[] = ['role' => $m['emisor'] === 'usuario' ? 'user' : 'assistant', 'content' => $m['texto']];
    }
    $sys = $pdo->query("SELECT content FROM prompt_lines WHERE role='system' ORDER BY id LIMIT 1")->fetchColumn();
    if ($sys) {
        array_unshift($messages, ['role' => 'system', 'content' => $sys]);
    }
    $messages[] = ['role' => 'user', 'content' => '¿Deseas cerrar aquí?'];
    $resp = call_openai_api($messages);
    $ins = $pdo->prepare('INSERT INTO mensajes (conversacion_id, emisor, texto) VALUES (?, "asistente", ?)');
    $ins->execute([$cid, $resp]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resumen Branding</title>
<link rel="stylesheet" href="assets/css/chat.css">
</head>
<body>
<h1>Resumen de Branding</h1>
<?php if ($inactive): ?>
<p>La conversación ha estado inactiva por más de 10 minutos.</p>
<?php endif; ?>
<pre><?= $pretty ?></pre>
<form action="chat.php" method="post" style="display:inline-block;">
    <input type="hidden" name="mensaje" value="confirmado">
    <button type="submit">Confirmar</button>
</form>
<form action="chat.php" method="post" style="display:inline-block;">
    <input type="text" name="mensaje" placeholder="Ajustar...">
    <button type="submit">Enviar</button>
</form>
</body>
</html>
