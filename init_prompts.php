<?php
// init_prompts.php - Load base prompts from prompts.php into the database
require 'db.php';
$prompts = include 'prompts.php';

$stmt = $pdo->prepare('INSERT INTO preguntas_admin (texto_pregunta, orden) VALUES (?, ?)');
$order = 1;
foreach ($prompts as $p) {
    if ($p['role'] === 'assistant') {
        $stmt->execute([$p['content'], $order]);
        $order++;
    }
}

echo "Preguntas cargadas: " . ($order - 1) . "\n";
