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
$selectedSet = isset($_GET['prompt_set']) ? (int)$_GET['prompt_set'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // questions
    if (isset($_POST['new_question'])) {
        $stmt = $pdo->prepare('INSERT INTO preguntas_admin (texto_pregunta) VALUES (?)');
        $stmt->execute([$_POST['new_question']]);
    }
    if (isset($_POST['edit_id']) && isset($_POST['edit_text'])) {
        $stmt = $pdo->prepare('UPDATE preguntas_admin SET texto_pregunta = ? WHERE id = ?');
        $stmt->execute([$_POST['edit_text'], $_POST['edit_id']]);
    }
    if (isset($_POST['prompt_update'])) {
        $stmt = $pdo->prepare('UPDATE usuarios SET prompt_set_id = ? WHERE id = ?');
        $stmt->execute([$_POST['prompt_set_id'], $_POST['user_id']]);
    }

    // prompt set operations
    if (isset($_POST['add_set'])) {
        $stmt = $pdo->prepare('INSERT INTO prompt_sets (nombre) VALUES (?)');
        $stmt->execute([$_POST['new_set_name']]);
        $selectedSet = $pdo->lastInsertId();
    }
    if (isset($_POST['rename_set'])) {
        $stmt = $pdo->prepare('UPDATE prompt_sets SET nombre = ? WHERE id = ?');
        $stmt->execute([$_POST['set_name'], $_POST['set_id']]);
    }

    if (isset($_POST['add_line'])) {
        $stmt = $pdo->prepare('INSERT INTO prompt_lines (set_id, role, content, orden) VALUES (?,?,?,?)');
        $stmt->execute([$selectedSet, $_POST['line_role'], $_POST['line_content'], (int)$_POST['line_order']]);
    }
    if (isset($_POST['edit_line'])) {
        $stmt = $pdo->prepare('UPDATE prompt_lines SET role = ?, content = ?, orden = ? WHERE id = ?');
        $stmt->execute([$_POST['line_role'], $_POST['line_content'], (int)$_POST['line_order'], $_POST['line_id']]);
    }
}
if (isset($_GET['del_question'])) {
    $stmt = $pdo->prepare('DELETE FROM preguntas_admin WHERE id = ?');
    $stmt->execute([$_GET['del_question']]);
}
if (isset($_GET['del_set'])) {
    $stmt = $pdo->prepare('DELETE FROM prompt_sets WHERE id = ?');
    $stmt->execute([$_GET['del_set']]);
    $selectedSet = null;
}
if (isset($_GET['del_line']) && $selectedSet) {
    $stmt = $pdo->prepare('DELETE FROM prompt_lines WHERE id = ? AND set_id = ?');
    $stmt->execute([$_GET['del_line'], $selectedSet]);
}

$users = $pdo->query('SELECT id, nombre, apellido, email, telefono, es_admin, prompt_set_id FROM usuarios')->fetchAll();
$promptSets = $pdo->query('SELECT id, nombre FROM prompt_sets ORDER BY id')->fetchAll();
$questions = $pdo->query('SELECT id, texto_pregunta FROM preguntas_admin ORDER BY id')->fetchAll();
$promptLines = [];
if ($selectedSet) {
    $stmt = $pdo->prepare('SELECT id, role, content, orden FROM prompt_lines WHERE set_id = ? ORDER BY orden');
    $stmt->execute([$selectedSet]);
    $promptLines = $stmt->fetchAll();
}
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
<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Admin</th><th>Prompt</th><th>Acciones</th></tr>
<?php foreach ($users as $u): ?>
<tr>
<td><?php echo $u['id']; ?></td>
<td><?php echo htmlspecialchars($u['nombre'].' '.$u['apellido']); ?></td>
<td><?php echo htmlspecialchars($u['email']); ?></td>
<td><?php echo htmlspecialchars($u['telefono']); ?></td>
<td><?php echo $u['es_admin'] ? 'Sí' : 'No'; ?></td>
<td>
    <form method="post" style="display:inline;">
        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
        <select name="prompt_set_id">
            <?php foreach ($promptSets as $set): ?>
                <option value="<?php echo $set['id']; ?>" <?php if($set['id']==$u['prompt_set_id']) echo 'selected'; ?>><?php echo htmlspecialchars($set['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="prompt_update">Guardar</button>
    </form>
</td>
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

<h2>Prompts</h2>
<h3>Conjuntos</h3>
<table>
<tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
<?php foreach ($promptSets as $pset): ?>
<tr>
<form method="post" action="?prompt_set=<?php echo $pset['id']; ?>">
<td><?php echo $pset['id']; ?><input type="hidden" name="set_id" value="<?php echo $pset['id']; ?>"></td>
<td><input type="text" name="set_name" value="<?php echo htmlspecialchars($pset['nombre']); ?>"></td>
<td>
<button type="submit" name="rename_set">Guardar</button>
<a href="?del_set=<?php echo $pset['id']; ?>" onclick="return confirm('Eliminar set?')">Eliminar</a>
<a href="?prompt_set=<?php echo $pset['id']; ?>">Ver mensajes</a>
</td>
</form>
</tr>
<?php endforeach; ?>
</table>
<form method="post">
<input type="text" name="new_set_name" placeholder="Nuevo conjunto">
<button type="submit" name="add_set">Crear</button>
</form>

<?php if ($selectedSet): ?>
<h3>Mensajes del set <?php echo $selectedSet; ?></h3>
<table>
<tr><th>Orden</th><th>Rol</th><th>Contenido</th><th>Acciones</th></tr>
<?php foreach ($promptLines as $line): ?>
<tr>
<form method="post" action="?prompt_set=<?php echo $selectedSet; ?>">
<td>
<input type="hidden" name="line_id" value="<?php echo $line['id']; ?>">
<input type="number" name="line_order" value="<?php echo $line['orden']; ?>" style="width:50px">
</td>
<td>
<select name="line_role">
<option value="system" <?php if($line['role']=='system') echo 'selected'; ?>>system</option>
<option value="assistant" <?php if($line['role']=='assistant') echo 'selected'; ?>>assistant</option>
<option value="user" <?php if($line['role']=='user') echo 'selected'; ?>>user</option>
</select>
</td>
<td><textarea name="line_content" rows="2" cols="60"><?php echo htmlspecialchars($line['content']); ?></textarea></td>
<td>
<button type="submit" name="edit_line">Guardar</button>
<a href="?prompt_set=<?php echo $selectedSet; ?>&del_line=<?php echo $line['id']; ?>" onclick="return confirm('Eliminar mensaje?')">Eliminar</a>
</td>
</form>
</tr>
<?php endforeach; ?>
</table>
<form method="post" action="?prompt_set=<?php echo $selectedSet; ?>">
<input type="number" name="line_order" value="<?php echo count($promptLines)+1; ?>" style="width:50px">
<select name="line_role">
<option value="system">system</option>
<option value="assistant">assistant</option>
<option value="user">user</option>
</select>
<input type="text" name="line_content" style="width:60%">
<button type="submit" name="add_line">Agregar</button>
</form>
<?php endif; ?>
</body>
</html>
