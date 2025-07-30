<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos actuales
$stmt = $pdo->prepare('SELECT nombre, apellido, empresa, email, telefono FROM usuarios WHERE id = ?');
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    echo 'Usuario no encontrado';
    exit;
}

// Actualizar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $empresa = trim($_POST['empresa']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);

    $stmt = $pdo->prepare('UPDATE usuarios SET nombre = ?, apellido = ?, empresa = ?, email = ?, telefono = ? WHERE id = ?');
    $stmt->execute([$nombre, $apellido, $empresa, $email, $telefono, $usuario_id]);
    header('Location: profile.php');
    exit;
}

// Eliminar cuenta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$usuario_id]);
    session_destroy();
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil</title>
</head>
<body>
<h1>Mi perfil</h1>
<form method="post">
    <input name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required><br>
    <input name="apellido" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" required><br>
    <input name="empresa" value="<?php echo htmlspecialchars($usuario['empresa']); ?>"><br>
    <input name="email" type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required><br>
    <input name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required><br>
    <button type="submit" name="update">Guardar cambios</button>
</form>
<form method="post" onsubmit="return confirm('¿Eliminar cuenta definitivamente?')" style="margin-top:1em;">
    <button type="submit" name="delete">Eliminar mi cuenta</button>
</form>
<p><a href="chat.php">Volver al chat</a></p>
</body>
</html>
