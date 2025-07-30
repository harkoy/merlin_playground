<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        header('Location: chat.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
</head>
<body>
<h1>Login</h1>
<form method="post">
    <input name="email" type="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Contraseña" required><br>
    <button type="submit">Entrar</button>
</form>
<?php if (!empty($error)) echo "<p>$error</p>"; ?>
</body>
</html>
