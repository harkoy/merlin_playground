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

/* Logout Container */
.logout-container {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 500px;
    padding: 2rem;
    text-align: center;
}

.logout-card {
    background: rgba(26, 35, 50, 0.9);
    backdrop-filter: blur(20px);
    border: 2px solid var(--primary-gold);
    border-radius: 20px;
    padding: 3rem 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 0 0 40px var(--shadow-gold);
    animation: fadeInScale 0.8s ease-out;
}

@keyframes fadeInScale {
    from { 
        opacity: 0; 
        transform: scale(0.8); 
    }
    to { 
        opacity: 1; 
        transform: scale(1); 
    }
}

/* Logout Icon */
.logout-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--deep-blue);
    margin: 0 auto 2rem;
    position: relative;
    box-shadow: 0 8px 25px var(--shadow-gold);
    animation: pulse 2s infinite ease-in-out;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.logout-icon::after {
    content: '';
    position: absolute;
    inset: -4px;
    background: linear-gradient(45deg, var(--primary-gold), var(--secondary-gold), var(--primary-gold));
    border-radius: 50%;
    z-index: -1;
    animation: rotate 3s linear infinite;
}

@keyframes rotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Typography */
.logout-title {
    color: var(--secondary-gold);
    font-size: 2rem;
    font-weight: 300;
    letter-spacing: 1px;
    margin-bottom: 1rem;
}

.logout-message {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.logout-submessage {
    color: var(--text-muted);
    font-size: 0.95rem;
    margin-bottom: 2rem;
    line-height: 1.5;
}

/* Countdown */
.countdown-container {
    background: rgba(52, 73, 94, 0.5);
    border: 2px solid rgba(212, 175, 55, 0.3);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.countdown-text {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.countdown-timer {
    color: var(--primary-gold);
    font-size: 2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.countdown-progress {
    width: 100%;
    height: 4px;
    background: rgba(52, 73, 94, 0.5);
    border-radius: 2px;
    margin-top: 1rem;
    overflow: hidden;
}

.countdown-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-gold), var(--secondary-gold));
    border-radius: 2px;
    transition: width 1s linear;
    width: 100%;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    text-align: center;
    justify-content: center;
    min-width: 150px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    color: var(--deep-blue);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-gold);
}

.btn-secondary {
    background: rgba(52, 73, 94, 0.8);
    color: var(--text-light);
    border: 2px solid rgba(212, 175, 55, 0.3);
}

.btn-secondary:hover {
    border-color: var(--primary-gold);
    background: rgba(52, 73, 94, 1);
    transform: translateY(-2px);
}

/* Success Animation */
.success-checkmark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--primary-gold);
    color: var(--deep-blue);
    animation: checkmarkPop 0.6s ease-out;
}

@keyframes checkmarkPop {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Floating particles */
.particle {
    position: absolute;
    background: var(--primary-gold);
    border-radius: 50%;
    pointer-events: none;
    animation: floatUp 3s ease-out infinite;
}

@keyframes floatUp {
    0% { 
        opacity: 1; 
        transform: translateY(0) scale(1); 
    }
    100% { 
        opacity: 0; 
        transform: translateY(-100px) scale(0.5); 
    }
}

/* Responsive Design */
@media (max-width: 480px) {
    .logout-container {
        padding: 1rem;
    }
    
    .logout-card {
        padding: 2rem 1.5rem;
    }
    
    .logout-title {
        font-size: 1.6rem;
    }
    
    .countdown-timer {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<!-- Animated Stars Background -->
<div class="stars" id="stars"></div>


<!-- Welcome Text -->

<!-- Login Container -->
<div class="login-container">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <h1 class="login-title">
              <i class="fa-solid fa-hat-wizard"></i>
                MERLIN
            </h1>
            <p class="login-subtitle">Inicia sesión para acceder a tu chat de negocio privado</p>
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
    const numStars = 60;
    
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
    
     // Remove particle after animation
    setTimeout(() => particle.remove(), 3000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    createStars();
    updateCountdown();
    
    // Create particles periodically
    setInterval(createParticle, 500);
});


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