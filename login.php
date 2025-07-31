<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password, es_admin FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['es_admin'] = $usuario['es_admin'];
        $destino = $usuario['es_admin'] ? 'admin.php' : 'chat.php';
        header('Location: ' . $destino);
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>✨ MERLIN</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<!-- Animated Stars Background -->
<div class="stars" id="stars"></div>

<!-- Welcome Text -->
<div class="welcome-text">
    Este es MERLIN
</div>

<!-- Login Container -->
<div class="login-container">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <h1 class="login-title">
                <i class="fas fa-star star-icon"></i>
                MERLIN
            </h1>
            <p class="login-subtitle">Inicia sesión para acceder a tu chat privado</p>
        </div>

        <!-- Login Form -->
        <form method="post" class="login-form" id="loginForm">
            <div class="form-group">
                <i class="fas fa-envelope form-icon"></i>
                <input 
                    name="email" 
                    type="email" 
                    class="form-input"
                    placeholder="Tu correo electrónico" 
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <i class="fas fa-lock form-icon"></i>
                <input 
                    name="password" 
                    type="password" 
                    class="form-input"
                    placeholder="Tu contraseña secreta" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Entrar al Chat</span>
            </button>
        </form>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="login-footer">
            <div class="privacy-notice">
                <p>
                    Al usar este chat aceptas nuestra 
                    <a href="privacy.php" class="privacy-link">
                        <i class="fas fa-shield-alt"></i>
                        Política de Privacidad
                    </a>
                </p>
            </div>
            <div class="footer-links">
                <a href="register.php" class="footer-link">
                    <i class="fas fa-user-plus"></i> Crear cuenta
                </a>
                <a href="forgot-password.php" class="footer-link">
                    <i class="fas fa-key"></i> ¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Create animated stars
function createStars() {
    const starsContainer = document.getElementById('stars');
    const numStars = 50;
    
    for (let i = 0; i < numStars; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.width = Math.random() * 3 + 1 + 'px';
        star.style.height = star.style.width;
        star.style.animationDelay = Math.random() * 2 + 's';
        starsContainer.appendChild(star);
    }
}

// Form submission with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const form = this;
    const btn = document.getElementById('loginBtn');
    const btnText = btn.querySelector('span');
    const btnIcon = btn.querySelector('i');
    
    // Add loading state
    form.classList.add('loading');
    btnIcon.className = 'loading-spinner';
    btnText.textContent = 'Conectando...';
    
    // Remove loading state after 3 seconds if form hasn't redirected
    setTimeout(() => {
        form.classList.remove('loading');
        btnIcon.className = 'fas fa-sign-in-alt';
        btnText.textContent = 'Entrar al Chat';
    }, 3000);
});

// Focus on first input
document.addEventListener('DOMContentLoaded', function() {
    createStars();
    document.querySelector('input[name="email"]').focus();
});

// Add particle effect on hover
document.querySelectorAll('.form-input, .login-btn').forEach(element => {
    element.addEventListener('mouseenter', function(e) {
        createParticle(e.pageX, e.pageY);
    });
});

function createParticle(x, y) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.left = x + 'px';
    particle.style.top = y + 'px';
    particle.style.width = '4px';
    particle.style.height = '4px';
    particle.style.opacity = '0.8';
    document.body.appendChild(particle);
    
    // Animate and remove particle
    setTimeout(() => {
        particle.style.opacity = '0';
        particle.style.transform = 'translateY(-20px) scale(0)';
        particle.style.transition = 'all 0.5s ease-out';
        setTimeout(() => particle.remove(), 500);
    }, 100);
}

// Add enter key functionality
document.querySelectorAll('.form-input').forEach(input => {
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('loginForm').submit();
        }
    });
});

// Add subtle floating animation to the card
let start = Date.now();
function animateCard() {
    const card = document.querySelector('.login-card');
    const elapsed = Date.now() - start;
    const y = Math.sin(elapsed / 1000) * 3;
    card.style.transform = `translateY(${y}px)`;
    requestAnimationFrame(animateCard);
}
animateCard();
</script>

</body>
</html>