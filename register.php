<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $empresa  = trim($_POST['empresa']);
    $email    = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR telefono = ? LIMIT 1");
    $stmt->execute([$email, $telefono]);
    if ($stmt->fetch()) {
        $error = 'Ya existe un usuario con ese email o teléfono.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, empresa, email, telefono, password, es_admin) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$nombre, $apellido, $empresa, $email, $telefono, $hash]);
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro</title>
</head>
<body>
<h1>Registro</h1>
<form method="post">
    <input name="nombre" placeholder="Nombre" required><br>
    <input name="apellido" placeholder="Apellido" required><br>
    <input name="empresa" placeholder="Empresa"><br>
    <input name="email" type="email" placeholder="Email" required><br>
    <input name="telefono" placeholder="Teléfono" required><br>
    <input name="password" type="password" placeholder="Contraseña" required><br>
    <button type="submit">Registrar</button>
</form>
<p style="font-size:small">Al registrarte aceptas la <a href="privacy.php">política de privacidad</a> y que tus datos sean almacenados.</p>
<?php if (!empty($error)) echo "<p>$error</p>"; ?>
</body>
</html>
