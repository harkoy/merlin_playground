<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $data['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $stmt = $pdo->prepare('INSERT INTO preguntas_admin (texto_pregunta) VALUES (?)');
            $stmt->execute([$data['texto'] ?? '']);
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;
        case 'update':
            $stmt = $pdo->prepare('UPDATE preguntas_admin SET texto_pregunta = ? WHERE id = ?');
            $stmt->execute([$data['texto'] ?? '', $data['id'] ?? 0]);
            echo json_encode(['success' => true]);
            break;
        case 'delete':
            $stmt = $pdo->prepare('DELETE FROM preguntas_admin WHERE id = ?');
            $stmt->execute([$data['id'] ?? 0]);
            echo json_encode(['success' => true]);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de servidor']);
}
