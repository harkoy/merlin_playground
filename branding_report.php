<?php
session_start();
require 'db.php';
if (!is_admin()) {
    header('Location: login.php');
    exit;
}
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT final_report FROM branding_briefs WHERE id = ?');
$stmt->execute([$id]);
$report = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Informe de Branding</title>
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<h1>Informe de Branding</h1>
<pre><?php echo nl2br(htmlspecialchars($report)); ?></pre>
<a href="admin.php">Volver</a>
</body>
</html>
