<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header('Location: login.php');
    exit;
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$_GET['delete_user']]);
}

// Handle question add/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_question'])) {
        $stmt = $pdo->prepare('INSERT INTO preguntas_admin (texto_pregunta) VALUES (?)');
        $stmt->execute([$_POST['new_question']]);
    }
    if (isset($_POST['edit_id']) && isset($_POST['edit_text'])) {
        $stmt = $pdo->prepare('UPDATE preguntas_admin SET texto_pregunta = ? WHERE id = ?');
        $stmt->execute([$_POST['edit_text'], $_POST['edit_id']]);
    }
}
if (isset($_GET['del_question'])) {
    $stmt = $pdo->prepare('DELETE FROM preguntas_admin WHERE id = ?');
    $stmt->execute([$_GET['del_question']]);
}

$users = $pdo->query('SELECT id, nombre, apellido, email, telefono, es_admin FROM usuarios')->fetchAll();
$questions = $pdo->query('SELECT id, texto_pregunta FROM preguntas_admin ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin</title>
<style>
table {border-collapse: collapse; width:100%;}
th,td{border:1px solid #ccc; padding:4px;}
</style>
</head>
<body>
<h1>Panel de Administración</h1>
<p><a href="logout.php">Cerrar sesión</a></p>
<h2>Usuarios</h2>
<table>
<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Admin</th><th>Acciones</th></tr>
<?php foreach ($users as $u): ?>
<tr>
<td><?php echo $u['id']; ?></td>
<td><?php echo htmlspecialchars($u['nombre'].' '.$u['apellido']); ?></td>
<td><?php echo htmlspecialchars($u['email']); ?></td>
<td><?php echo htmlspecialchars($u['telefono']); ?></td>
<td><?php echo $u['es_admin'] ? 'Sí' : 'No'; ?></td>
<td><a href="?delete_user=<?php echo $u['id']; ?>" onclick="return confirm('Eliminar usuario?')">Eliminar</a></td>
</tr>
<?php endforeach; ?>
</table>
<h2>Preguntas</h2>
<table>
<tr><th>ID</th><th>Pregunta</th><th>Acciones</th></tr>
<?php foreach ($questions as $q): ?>
<tr>
<form method="post">
<td><?php echo $q['id']; ?><input type="hidden" name="edit_id" value="<?php echo $q['id']; ?>"></td>
<td><input type="text" name="edit_text" value="<?php echo htmlspecialchars($q['texto_pregunta']); ?>" style="width:80%"></td>
<td>
<button type="submit">Guardar</button>
<a href="?del_question=<?php echo $q['id']; ?>" onclick="return confirm('Eliminar pregunta?')">Eliminar</a>
</td>
</form>
</tr>
<?php endforeach; ?>
</table>
<h3>Nueva pregunta</h3>
<form method="post">
<input type="text" name="new_question" style="width:60%">
<button type="submit">Agregar</button>
</form>
</body>
</html>
