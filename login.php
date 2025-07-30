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
<title>✨ Celestial Chat - Iniciar Sesión</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-gold: #D4AF37;
    --secondary-gold: #FFD700;
    --deep-blue: #0B1426;
    --navy-blue: #1A2332;
    --light-blue: #2C3E50;
    --accent-blue: #34495E;
    --text-light: #ECF0F1;
    --text-muted: #BDC3C7;
    --shadow-gold: rgba(212, 175, 55, 0.3);
    --gradient-bg: linear-gradient(135deg, #0B1426 0%, #1A2332 50%, #2C3E50 100%);
    --error-red: #E74C3C;
    --success-green: #27AE60;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gradient-bg);
    color: var(--text-light);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Animated Background Stars */
.stars {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}

.star {
    position: absolute;
    background: var(--secondary-gold);
    border-radius: 50%;
    animation: twinkle 2s infinite ease-in-out;
}

.star:nth-child(odd) {
    animation-delay: 1s;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.2); }
}

/* Login Container */
.login-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 400px;
    padding: 2rem;
}

.login-card {
    background: rgba(26, 35, 50, 0.9);
    backdrop-filter: blur(20px);
    border: 2px solid var(--primary-gold);
    border-radius: 20px;
    padding: 3rem 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px var(--shadow-gold);
    text-align: center;
    animation: slideIn 0.8s ease-out;
}

@keyframes slideIn {
    from { 
        opacity: 0; 
        transform: translateY(50px) scale(0.9); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0) scale(1); 
    }
}

/* Header */
.login-header {
    margin-bottom: 2rem;
}

.login-title {
    color: var(--secondary-gold);
    font-size: 2.2rem;
    font-weight: 300;
    letter-spacing: 2px;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.star-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.login-subtitle {
    color: var(--text-muted);
    font-size: 1rem;
    font-weight: 300;
}

/* Form Styles */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    position: relative;
}

.form-input {
    width: 100%;
    background: rgba(52, 73, 94, 0.5);
    border: 2px solid rgba(212, 175, 55, 0.3);
    color: var(--text-light);
    padding: 1rem 1rem 1rem 3rem;
    border-radius: 15px;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.form-input:focus {
    border-color: var(--primary-gold);
    box-shadow: 0 0 20px var(--shadow-gold);
    background: rgba(52, 73, 94, 0.8);
    transform: translateY(-2px);
}

.form-input::placeholder {
    color: var(--text-muted);
    font-weight: 300;
}

.form-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-gold);
    font-size: 1.1rem;
}

/* Login Button */
.login-btn {
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    border: none;
    color: var(--deep-blue);
    padding: 1rem 2rem;
    border-radius: 15px;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    position: relative;
    overflow: hidden;
}

.login-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px var(--shadow-gold);
}

.login-btn:active {
    transform: translateY(-1px);
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.login-btn:hover::before {
    left: 100%;
}

/* Error Messages */
.error-message {
    background: rgba(231, 76, 60, 0.1);
    border: 1px solid var(--error-red);
    color: var(--error-red);
    padding: 1rem;
    border-radius: 10px;
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Footer Links */
.login-footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(212, 175, 55, 0.2);
}

.privacy-notice {
    text-align: center;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: var(--text-muted);
    line-height: 1.5;
}

.privacy-link {
    color: var(--primary-gold);
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
}

.privacy-link:hover {
    color: var(--secondary-gold);
    background: rgba(212, 175, 55, 0.1);
    transform: translateY(-1px);
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.footer-link {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.footer-link:hover {
    color: var(--primary-gold);
    background: rgba(212, 175, 55, 0.1);
    transform: translateY(-2px);
}

/* Loading State */
.loading {
    pointer-events: none;
}

.loading .login-btn {
    background: var(--accent-blue);
    color: var(--text-muted);
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 480px) {
    .login-container {
        padding: 1rem;
    }
    
    .login-card {
        padding: 2rem 1.5rem;
    }
    
    .login-title {
        font-size: 1.8rem;
    }
    
    .form-input {
        padding: 0.8rem 0.8rem 0.8rem 2.5rem;
    }
    
    .form-icon {
        left: 0.8rem;
    }
}

/* Welcome Animation */
.welcome-text {
    position: absolute;
    top: 10%;
    left: 50%;
    transform: translateX(-50%);
    color: var(--primary-gold);
    font-size: 1.2rem;
    font-weight: 300;
    letter-spacing: 1px;
    opacity: 0.7;
    z-index: 5;
}

/* Particle Effect */
.particle {
    position: absolute;
    background: var(--primary-gold);
    border-radius: 50%;
    pointer-events: none;
}
</style>
</head>
<body>

<!-- Animated Stars Background -->
<div class="stars" id="stars"></div>

<!-- Welcome Text -->
<div class="welcome-text">
    Bienvenido al Chat Celestial
</div>

<!-- Login Container -->
<div class="login-container">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <h1 class="login-title">
                <i class="fas fa-star star-icon"></i>
                Celestial Chat
            </h1>
            <p class="login-subtitle">Inicia sesión para acceder a tu chat mágico</p>
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
                    Al usar este chat celestial aceptas nuestra 
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