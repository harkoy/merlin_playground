<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare('SELECT analisis FROM resultados_analisis WHERE usuario_id = ? ORDER BY fecha_registro DESC LIMIT 1');
$stmt->execute([$usuario_id]);
$analysis = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen</title>
</head>
<body>
<h1>Resumen de la información</h1>
<?php if ($analysis): ?>
<p><?php echo nl2br(htmlspecialchars($analysis)); ?></p>
<?php else: ?>
<p>No hay información registrada.</p>
<?php endif; ?>
<form method="post">
    <button type="submit">Confirmar</button>
</form>
<p><a href="chat.php">Solicitar ajustes</a></p>
<?php include 'branch.php'; ?>
</body>
</html>
