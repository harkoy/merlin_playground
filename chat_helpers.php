<?php
/**
 * Utility helpers for chat conversation.
 */
function build_base_messages(PDO $pdo, int $usuario_id): array {
    $messages = [];

    // Load prompt set associated with the user
    $setStmt = $pdo->prepare('SELECT prompt_set_id FROM usuarios WHERE id = ?');
    $setStmt->execute([$usuario_id]);
    $setId = $setStmt->fetchColumn();
    if ($setId) {
        $pstmt = $pdo->prepare('SELECT role, content FROM prompt_lines WHERE set_id = ? ORDER BY orden');
        $pstmt->execute([$setId]);
        foreach ($pstmt->fetchAll() as $p) {
            $messages[] = ['role' => $p['role'], 'content' => $p['content']];
        }
    }

    // Gather admin-defined questions to guide the conversation
    $qstmt = $pdo->query('SELECT texto_pregunta FROM preguntas_admin ORDER BY orden');
    $questions = $qstmt->fetchAll(PDO::FETCH_COLUMN);
    if ($questions) {
        $messages[] = [
            'role' => 'system',
            'content' => 'Recopila la siguiente información integrándola de manera natural en la conversación: ' . implode(' | ', $questions) . '. Cuando ya no puedas obtener más datos relevantes, resume todo y ofrece la posibilidad de confirmar o ajustar el resumen.'
        ];
    }

    return $messages;
}
?>
