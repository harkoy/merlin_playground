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

$qstmt = $pdo->query('SELECT id, texto_pregunta FROM preguntas_admin ORDER BY orden');
$preguntas = $qstmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($preguntas as $preg) {
        $answer = trim($_POST['pregunta_' . $preg['id']] ?? '');
        if ($answer !== '') {
            $ins = $pdo->prepare('REPLACE INTO respuestas (usuario_id, pregunta_id, respuesta) VALUES (?, ?, ?)');
            $ins->execute([$usuario_id, $preg['id'], $answer]);
        }
    }

    $resStmt = $pdo->prepare('SELECT p.texto_pregunta, r.respuesta FROM respuestas r JOIN preguntas_admin p ON r.pregunta_id = p.id WHERE r.usuario_id = ? ORDER BY p.orden');
    $resStmt->execute([$usuario_id]);
    $pairs = $resStmt->fetchAll();
    $text = "";
    foreach ($pairs as $pr) {
        $text .= $pr['texto_pregunta'] . ": " . $pr['respuesta'] . "\n";
    }
    $messages = build_base_messages($pdo, $usuario_id);
    $messages[] = ['role' => 'user', 'content' => "Respuestas del formulario:\n" . $text];
    $messages[] = ['role' => 'system', 'content' => 'Genera un informe estructurado con la información proporcionada.'];
    $analysis = call_openai_api($messages);
    $stmt = $pdo->prepare("INSERT INTO resultados_analisis (usuario_id, analisis) VALUES (?, ?) ON DUPLICATE KEY UPDATE analisis = VALUES(analisis), fecha_registro = CURRENT_TIMESTAMP");
    $stmt->execute([$usuario_id, $analysis]);
    header('Location: summary.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de preguntas</title>
</head>
<body>
<h1>Responder preguntas</h1>
<form method="post">
<?php foreach ($preguntas as $p): ?>
    <label><?php echo htmlspecialchars($p['texto_pregunta']); ?></label><br>
    <textarea name="pregunta_<?php echo $p['id']; ?>" rows="2" cols="50"></textarea><br><br>
<?php endforeach; ?>
    <button type="submit">Enviar</button>
</form>
<p><a href="chat.php">Volver al chat</a></p>
</body>
</html>
