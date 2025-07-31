<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'openai.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';

// Obtener o crear conversacion
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

if ($action === 'delete') {
    $msgId = (int)($input['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT m.id FROM mensajes m JOIN conversaciones c ON m.conversacion_id = c.id WHERE m.id = ? AND c.usuario_id = ? LIMIT 1');
    $stmt->execute([$msgId, $usuario_id]);
    if ($stmt->fetch()) {
        $pdo->prepare('DELETE FROM mensajes WHERE id = ?')->execute([$msgId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Mensaje no encontrado']);
    }
    exit;
}

if ($action === 'send') {
    $mensaje = trim($input['mensaje'] ?? '');
    if ($mensaje === '') {
        echo json_encode(['error' => 'Mensaje vacío']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO mensajes (conversacion_id, emisor, texto) VALUES (?, 'usuario', ?)");
    $stmt->execute([$conver_id, $mensaje]);
    $userId = $pdo->lastInsertId();

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
        $stmt = $pdo->prepare('UPDATE preferencias_disenio SET color_preferido = ? WHERE usuario_id = ?');
        $stmt->execute([$newColor, $usuario_id]);
    }
    if (strpos($mensajeLower, 'oscuro') !== false || strpos($mensajeLower, 'dark') !== false) {
        $stmt = $pdo->prepare('UPDATE preferencias_disenio SET tema = ? WHERE usuario_id = ?');
        $stmt->execute(['dark', $usuario_id]);
    }
    if (strpos($mensajeLower, 'claro') !== false || strpos($mensajeLower, 'light') !== false) {
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
    $assistantId = $pdo->lastInsertId();

    echo json_encode([
        'user_id' => $userId,
        'assistant_id' => $assistantId,
        'reply' => $respuesta,
        'fin' => strpos($respuesta, '<<FIN_INFO>>') !== false
    ]);
    exit;
}

echo json_encode(['error' => 'Acción inválida']);
