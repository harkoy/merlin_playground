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

$success_message = '';
$error_message = '';

// Actualizar datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $empresa = trim($_POST['empresa']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);

    // Validar email único (excepto el actual)
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
    $stmt->execute([$email, $usuario_id]);
    if ($stmt->fetch()) {
        $error_message = 'Este email ya está en uso por otro usuario.';
    } else {
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre = ?, apellido = ?, empresa = ?, email = ?, telefono = ? WHERE id = ?');
        $stmt->execute([$nombre, $apellido, $empresa, $email, $telefono, $usuario_id]);
        $success_message = 'Perfil actualizado correctamente.';
        
        // Actualizar datos para mostrar
        $usuario['nombre'] = $nombre;
        $usuario['apellido'] = $apellido;
        $usuario['empresa'] = $empresa;
        $usuario['email'] = $email;
        $usuario['telefono'] = $telefono;
    }
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>✨ Mi Perfil Celestial</title>
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
    --warning-orange: #F39C12;
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
    padding-bottom: 100px;
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
    animation: twinkle 3s infinite ease-in-out;
}

.star:nth-child(odd) {
    animation-delay: 1.5s;
}

@keyframes twinkle {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.3); }
}

/* Header */
.header {
    background: rgba(11, 20, 38, 0.9);
    backdrop-filter: blur(10px);
    padding: 1.5rem 2rem;
    border-bottom: 2px solid var(--primary-gold);
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 4px 20px var(--shadow-gold);
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    color: var(--secondary-gold);
    font-size: 1.8rem;
    font-weight: 300;
    letter-spacing: 2px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.star-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.back-btn {
    background: transparent;
    border: 2px solid var(--primary-gold);
    color: var(--primary-gold);
    padding: 0.5rem 1rem;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.back-btn:hover {
    background: var(--primary-gold);
    color: var(--deep-blue);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px var(--shadow-gold);
}

/* Main Container */
.main-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 2rem;
    position: relative;
    z-index: 10;
}

/* Profile Card */
.profile-card {
    background: rgba(26, 35, 50, 0.9);
    backdrop-filter: blur(20px);
    border: 2px solid var(--primary-gold);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 0 0 40px var(--shadow-gold);
    animation: slideInUp 0.6s ease-out;
    margin-bottom: 2rem;
}

@keyframes slideInUp {
    from { 
        opacity: 0; 
        transform: translateY(50px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Profile Header */
.profile-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.profile-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--deep-blue);
    margin: 0 auto 1rem;
    position: relative;
    box-shadow: 0 8px 25px var(--shadow-gold);
}

.profile-avatar::after {
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

.profile-name {
    color: var(--secondary-gold);
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.profile-email {
    color: var(--text-muted);
    font-size: 1rem;
}

/* Form Styles */
.profile-form {
    display: grid;
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    position: relative;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    color: var(--secondary-gold);
    font-weight: 500;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-input {
    width: 100%;
    background: rgba(52, 73, 94, 0.5);
    border: 2px solid rgba(212, 175, 55, 0.3);
    color: var(--text-light);
    padding: 1rem;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.form-input:focus {
    border-color: var(--primary-gold);
    box-shadow: 0 0 15px var(--shadow-gold);
    background: rgba(52, 73, 94, 0.8);
    transform: translateY(-2px);
}

.form-input::placeholder {
    color: var(--text-muted);
}

/* Buttons */
.btn-group {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
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
    transform: translateY(-3px);
    box-shadow: 0 8px 25px var(--shadow-gold);
}

.btn-danger {
    background: linear-gradient(135deg, var(--error-red), #C0392B);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
}

/* Messages */
.message {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideInDown 0.4s ease-out;
}

@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.success {
    background: rgba(39, 174, 96, 0.1);
    border: 1px solid var(--success-green);
    color: var(--success-green);
}

.message.error {
    background: rgba(231, 76, 60, 0.1);
    border: 1px solid var(--error-red);
    color: var(--error-red);
}

/* Danger Zone */
.danger-zone {
    background: rgba(231, 76, 60, 0.05);
    border: 2px solid rgba(231, 76, 60, 0.3);
    border-radius: 15px;
    padding: 1.5rem;
    margin-top: 2rem;
}

.danger-zone h3 {
    color: var(--error-red);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.danger-zone p {
    color: var(--text-muted);
    margin-bottom: 1rem;
    line-height: 1.6;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: rgba(26, 35, 50, 0.95);
    backdrop-filter: blur(20px);
    border: 2px solid var(--primary-gold);
    border-radius: 20px;
    padding: 2rem;
    max-width: 400px;
    width: 90%;
    text-align: center;
    animation: modalSlide 0.3s ease-out;
}

@keyframes modalSlide {
    from { opacity: 0; transform: scale(0.8); }
    to { opacity: 1; transform: scale(1); }
}

.modal h3 {
    color: var(--error-red);
    margin-bottom: 1rem;
}

.modal p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.modal-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.modal .btn {
    min-width: 100px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header {
        padding: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        gap: 1rem;
    }
    
    .main-container {
        padding: 0 1rem;
    }
    
    .profile-card {
        padding: 1.5rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .modal {
        padding: 1.5rem;
    }
    
    .modal-buttons {
        flex-direction: column;
    }
}
</style>
</head>
<body>

<!-- Animated Stars Background -->
<div class="stars" id="stars"></div>

<!-- Header -->
<header class="header">
    <div class="header-content">
        <h1>
            <i class="fas fa-star star-icon"></i>
            Mi Perfil Celestial
        </h1>
        <a href="chat.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Volver al Chat
        </a>
    </div>
</header>

<!-- Main Container -->
<div class="main-container">
    
    <!-- Success/Error Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="message error">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- Profile Card -->
    <div class="profile-card">
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2 class="profile-name">
                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
            </h2>
            <p class="profile-email">
                <?php echo htmlspecialchars($usuario['email']); ?>
            </p>
        </div>

        <!-- Profile Form -->
        <form method="post" class="profile-form" id="profileForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Nombre
                    </label>
                    <input 
                        name="nombre" 
                        class="form-input"
                        value="<?php echo htmlspecialchars($usuario['nombre']); ?>" 
                        placeholder="Tu nombre"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user-tag"></i>
                        Apellido
                    </label>
                    <input 
                        name="apellido" 
                        class="form-input"
                        value="<?php echo htmlspecialchars($usuario['apellido']); ?>" 
                        placeholder="Tu apellido"
                        required
                    >
                </div>
            </div>

            <div class="form-group full-width">
                <label class="form-label">
                    <i class="fas fa-building"></i>
                    Empresa
                </label>
                <input 
                    name="empresa" 
                    class="form-input"
                    value="<?php echo htmlspecialchars($usuario['empresa']); ?>" 
                    placeholder="Tu empresa (opcional)"
                >
            </div>

            <div class="form-group full-width">
                <label class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <input 
                    name="email" 
                    type="email" 
                    class="form-input"
                    value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                    placeholder="tu@email.com"
                    required
                >
            </div>

            <div class="form-group full-width">
                <label class="form-label">
                    <i class="fas fa-phone"></i>
                    Teléfono
                </label>
                <input 
                    name="telefono" 
                    class="form-input"
                    value="<?php echo htmlspecialchars($usuario['telefono']); ?>" 
                    placeholder="Tu número de teléfono"
                    required
                >
            </div>

            <div class="btn-group">
                <button type="submit" name="update" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>

    </div>

    <!-- Danger Zone -->
    <div class="danger-zone">
        <h3>
            <i class="fas fa-exclamation-triangle"></i>
            Zona de Peligro
        </h3>
        <p>
            Una vez que elimines tu cuenta, no hay vuelta atrás. Por favor, asegúrate de que realmente quieres hacer esto.
        </p>
        <button class="btn btn-danger" onclick="showDeleteModal()">
            <i class="fas fa-trash"></i>
            Eliminar Mi Cuenta
        </button>
    </div>

</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>
            <i class="fas fa-exclamation-triangle"></i>
            ¿Eliminar Cuenta?
        </h3>
        <p>
            Esta acción es <strong>irreversible</strong>. Se eliminarán todos tus datos, conversaciones y configuraciones.
        </p>
        <div class="modal-buttons">
            <button class="btn btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash"></i>
                Sí, Eliminar
            </button>
            <button class="btn btn-primary" onclick="hideDeleteModal()">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
        </div>
    </div>
</div>

<!-- Hidden delete form -->
<form method="post" id="deleteForm" style="display: none;">
    <input type="hidden" name="delete" value="1">
</form>

<script>
// Create animated stars
function createStars() {
    const starsContainer = document.getElementById('stars');
    const numStars = 40;
    
    for (let i = 0; i < numStars; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.width = Math.random() * 3 + 1 + 'px';
        star.style.height = star.style.width;
        star.style.animationDelay = Math.random() * 3 + 's';
        starsContainer.appendChild(star);
    }
}

// Delete Modal Functions
function showDeleteModal() {
    document.getElementById('deleteModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmDelete() {
    document.getElementById('deleteForm').submit();
}

// Close modal on overlay click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteModal();
    }
});

// Form validation and enhancement
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const btnIcon = submitBtn.querySelector('i');
    const btnText = submitBtn.lastChild;
    
    // Add loading state
    submitBtn.disabled = true;
    btnIcon.className = 'fas fa-spinner fa-spin';
    btnText.textContent = ' Guardando...';
    
    // Re-enable after a delay (in case of validation errors)
    setTimeout(() => {
        submitBtn.disabled = false;
        btnIcon.className = 'fas fa-save';
        btnText.textContent = ' Guardar Cambios';
    }, 3000);
});

// Auto-hide success messages
setTimeout(() => {
    const successMessage = document.querySelector('.message.success');
    if (successMessage) {
        successMessage.style.opacity = '0';
        successMessage.style.transform = 'translateY(-20px)';
        setTimeout(() => successMessage.remove(), 300);
    }
}, 5000);

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    createStars();
    
    // Focus on first input
    document.querySelector('.form-input').focus();
});

// Add floating animation to profile avatar
let start = Date.now();
function animateAvatar() {
    const avatar = document.querySelector('.profile-avatar');
    const elapsed = Date.now() - start;
    const y = Math.sin(elapsed / 2000) * 2;
    avatar.style.transform = `translateY(${y}px)`;
    requestAnimationFrame(animateAvatar);
}
animateAvatar();

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+S to save
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('profileForm').submit();
    }
    
    // Escape to close modal
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
});
</script>

</body>
</html>