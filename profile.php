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
<link rel="stylesheet" href="assets/css/profile.css">
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

<?php include 'branch.php'; ?>

</body>
</html>
