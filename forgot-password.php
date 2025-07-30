<?php
session_start();
require 'db.php';

$mensaje = '';
$error = '';

// Request password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $pdo->prepare("INSERT INTO password_resets (usuario_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=VALUES(token), expires_at=VALUES(expires_at)")
                ->execute([$user['id'], $token, $expires]);
            // In a real setup you'd send the email with the link
            $resetLink = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/forgot-password.php?token=$token";
            // mail($email, 'Recuperar contraseña', 'Ingresa a ' . $resetLink);
            $mensaje = 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.';
        } else {
            $mensaje = 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.';
        }
    } elseif (isset($_POST['token']) && isset($_POST['password'])) {
        $token = $_POST['token'];
        $stmt = $pdo->prepare("SELECT usuario_id FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if ($row) {
            $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?")->execute([$hash, $row['usuario_id']]);
            $pdo->prepare("DELETE FROM password_resets WHERE usuario_id = ?")->execute([$row['usuario_id']]);
            $mensaje = 'Contraseña actualizada. Ahora puedes iniciar sesión.';
        } else {
            $error = 'El enlace no es válido o ha expirado.';
        }
    }
}

$token = $_GET['token'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <h1 class="login-title">Recuperar contraseña</h1>
        <?php if ($mensaje): ?>
            <p class="success-message"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($token && !$mensaje): ?>
            <form method="post" class="login-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" name="password" class="form-input" placeholder="Nueva contraseña" required>
                </div>
                <button type="submit" class="login-btn">Guardar contraseña</button>
            </form>
        <?php else: ?>
            <form method="post" class="login-form">
                <div class="form-group">
                    <i class="fas fa-envelope form-icon"></i>
                    <input type="email" name="email" class="form-input" placeholder="Tu correo" required>
                </div>
                <button type="submit" class="login-btn">Enviar enlace</button>
            </form>
        <?php endif; ?>
        <div class="login-footer">
            <a href="login.php" class="footer-link"><i class="fas fa-arrow-left"></i> Volver al login</a>
        </div>
    </div>
</div>
</body>
</html>
