<?php
// init_prompts.php - Load prompt sets defined in prompts.php into the database
require 'db.php';
$promptSets = include 'prompts.php';

$insertSet = $pdo->prepare('INSERT INTO prompt_sets (nombre) VALUES (?)');
$insertLine = $pdo->prepare('INSERT INTO prompt_lines (set_id, role, content, orden) VALUES (?, ?, ?, ?)');

$totalLines = 0;
foreach ($promptSets as $name => $messages) {
    $insertSet->execute([$name]);
    $setId = $pdo->lastInsertId();
    $order = 1;
    foreach ($messages as $m) {
        $insertLine->execute([$setId, $m["role"], $m["content"], $order]);
        $order++; $totalLines++;
    }
    echo "Set '$name' cargado con ID $setId\n";
}
echo "Mensajes cargados: $totalLines\n";
