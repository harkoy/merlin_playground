<?php
session_start();

// Store user info before destroying session (for personalized goodbye)
$was_logged_in = isset($_SESSION['usuario_id']);
$redirect_delay = 3; // seconds

// Destroy the session
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>✨ Hasta Pronto - Celestial Chat</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>

<!-- Animated Stars Background -->
<div class="stars" id="stars"></div>

<!-- Logout Container -->
<div class="logout-container">
    <div class="logout-card">
        
        <!-- Logout Icon -->
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>

        <!-- Content -->
        <?php if ($was_logged_in): ?>
            <h1 class="logout-title">¡Hasta Pronto!</h1>
            <p class="logout-message">
                <span class="success-checkmark">
                    <i class="fas fa-check"></i>
                </span>
                Has cerrado sesión exitosamente del Chat Celestial
            </p>
            <p class="logout-submessage">
                Gracias por usar nuestra plataforma. Esperamos verte pronto en las estrellas.
            </p>
        <?php else: ?>
            <h1 class="logout-title">Sesión Finalizada</h1>
            <p class="logout-message">
                Tu sesión ha sido cerrada correctamente
            </p>
            <p class="logout-submessage">
                Puedes iniciar sesión nuevamente cuando lo desees.
            </p>
        <?php endif; ?>

        <!-- Countdown -->
        <div class="countdown-container">
            <div class="countdown-text">Redirigiendo al login en:</div>
            <div class="countdown-timer">
                <i class="fas fa-clock"></i>
                <span id="countdown"><?php echo $redirect_delay; ?></span>
                <span>segundos</span>
            </div>
            <div class="countdown-progress">
                <div class="countdown-progress-bar" id="progressBar"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="login.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesión
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i>
                Crear Cuenta
            </a>
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

// Countdown functionality
let timeLeft = <?php echo $redirect_delay; ?>;
const countdownElement = document.getElementById('countdown');
const progressBar = document.getElementById('progressBar');
const totalTime = timeLeft;

function updateCountdown() {
    countdownElement.textContent = timeLeft;
    
    // Update progress bar
    const percentage = ((totalTime - timeLeft) / totalTime) * 100;
    progressBar.style.width = percentage + '%';
    
    if (timeLeft <= 0) {
        window.location.href = 'login.php';
        return;
    }
    
    timeLeft--;
    setTimeout(updateCountdown, 1000);
}

// Create floating particles
function createParticle() {
    const particle = document.createElement('div');
    particle.className = 'particle';
    particle.style.left = Math.random() * 100 + '%';
    particle.style.bottom = '0';
    particle.style.width = Math.random() * 4 + 2 + 'px';
    particle.style.height = particle.style.width;
    particle.style.animationDelay = Math.random() * 2 + 's';
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

// Add keyboard shortcut for immediate login
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
        window.location.href = 'login.php';
    }
});

// Prevent accidental back navigation
window.addEventListener('beforeunload', function(e) {
    // Don't show confirmation for logout page
    return;
});

// Add subtle floating animation to the card
let start = Date.now();
function animateCard() {
    const card = document.querySelector('.logout-card');
    const elapsed = Date.now() - start;
    const y = Math.sin(elapsed / 2000) * 2;
    card.style.transform = `translateY(${y}px)`;
    requestAnimationFrame(animateCard);
}
animateCard();
</script>

</body>
</html>