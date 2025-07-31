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
        case 'create_set':
            $stmt = $pdo->prepare('INSERT INTO prompt_sets (nombre) VALUES (?)');
            $stmt->execute([$data['nombre'] ?? '']);
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;
        case 'rename_set':
            $stmt = $pdo->prepare('UPDATE prompt_sets SET nombre = ? WHERE id = ?');
            $stmt->execute([$data['nombre'] ?? '', $data['id'] ?? 0]);
            echo json_encode(['success' => true]);
            break;
        case 'delete_set':
            $stmt = $pdo->prepare('DELETE FROM prompt_sets WHERE id = ?');
            $stmt->execute([$data['id'] ?? 0]);
            echo json_encode(['success' => true]);
            break;
        case 'create_line':
            $stmt = $pdo->prepare('INSERT INTO prompt_lines (set_id, role, content, orden) VALUES (?,?,?,?)');
            $stmt->execute([
                $data['set_id'] ?? 0,
                $data['role'] ?? '',
                $data['content'] ?? '',
                $data['orden'] ?? 0
            ]);
            echo json_encode(['id' => $pdo->lastInsertId()]);
            break;
        case 'update_line':
            $stmt = $pdo->prepare('UPDATE prompt_lines SET role = ?, content = ?, orden = ? WHERE id = ?');
            $stmt->execute([
                $data['role'] ?? '',
                $data['content'] ?? '',
                $data['orden'] ?? 0,
                $data['id'] ?? 0
            ]);
            echo json_encode(['success' => true]);
            break;
        case 'delete_line':
            $stmt = $pdo->prepare('DELETE FROM prompt_lines WHERE id = ?');
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
