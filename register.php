<?php
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $empresa  = trim($_POST['empresa']);
    $email    = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del email no es válido.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR telefono = ? LIMIT 1");
        $stmt->execute([$email, $telefono]);
        if ($stmt->fetch()) {
            $error = 'Ya existe un usuario con ese email o teléfono.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $defaultPrompt = $pdo->query("SELECT id FROM prompt_sets ORDER BY id LIMIT 1")->fetchColumn();
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, empresa, email, telefono, password, prompt_set_id, es_admin) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$nombre, $apellido, $empresa, $email, $telefono, $hash, $defaultPrompt]);
            $success = 'Registro exitoso. Redirigiendo al login...';
            header('refresh:2;url=login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✨ Únete a MERLIN</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gold: #D4AF37;
            --secondary-gold: #F4E177;
            --deep-blue: #0B1426;
            --navy-blue: #1A2332;
            --light-blue: #2C3E50;
            --accent-blue: #34495E;
            --text-light: #FFFFFF;
            --text-muted: #A0A6B0;
            --shadow-gold: rgba(212, 175, 55, 0.3);
            --gradient-bg: linear-gradient(135deg, #0B1426 0%, #1A2332 100%);
            --card-bg: rgba(26, 35, 50, 0.95);
            --border-glow: rgba(212, 175, 55, 0.4);
            --error-color: #E74C3C;
            --success-color: #27AE60;
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
            overflow-x: hidden;
        }

        /* Animated background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
            z-index: -1;
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        /* Floating stars */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .star {
            position: absolute;
            color: var(--primary-gold);
            font-size: 0.5rem;
            animation: twinkle 3s infinite;
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        /* Main container */
        .register-container {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 2px solid var(--border-glow);
            border-radius: 25px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            margin: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(50px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Header */
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo-icon {
            font-size: 3.5rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
            display: block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .register-title {
            font-size: 2.2rem;
            color: var(--secondary-gold);
            margin-bottom: 0.5rem;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .register-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* Form */
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-group {
            flex: 1;
            position: relative;
        }

        .form-group.full-width {
            flex: none;
        }

        .form-input {
            width: 100%;
            background: rgba(52, 73, 94, 0.7);
            border: 2px solid var(--border-glow);
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
            background: rgba(52, 73, 94, 0.9);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-gold);
            font-size: 1.1rem;
            z-index: 1;
        }

        /* Password strength indicator */
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: rgba(52, 73, 94, 0.5);
            border-radius: 2px;
            overflow: hidden;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .password-strength.visible {
            opacity: 1;
        }

        .strength-fill {
            height: 100%;
            background: var(--error-color);
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-fill.weak { background: var(--error-color); }
        .strength-fill.medium { background: #F39C12; }
        .strength-fill.strong { background: var(--success-color); }

        /* Submit button */
        .submit-btn {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            border: none;
            color: var(--deep-blue);
            padding: 1.2rem 2rem;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow-gold);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Loading animation */
        .submit-btn .loading {
            display: none;
        }

        .submit-btn.loading .loading {
            display: inline-block;
            margin-right: 0.5rem;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 15px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            border: 2px solid var(--error-color);
            color: var(--error-color);
        }

        .message.success {
            background: rgba(39, 174, 96, 0.1);
            border: 2px solid var(--success-color);
            color: var(--success-color);
        }

        /* Footer */
        .register-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-glow);
        }

        .privacy-text {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .privacy-link {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .privacy-link:hover {
            color: var(--secondary-gold);
            text-decoration: underline;
        }

        .login-link {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .login-link a {
            color: var(--primary-gold);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--secondary-gold);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                margin: 1rem;
                padding: 2rem;
            }

            .form-row {
                flex-direction: column;
                gap: 1.5rem;
            }

            .register-title {
                font-size: 1.8rem;
            }

            .logo-icon {
                font-size: 2.5rem;
            }
        }

        /* Validation styles */
        .form-input.valid {
            border-color: var(--success-color);
        }

        .form-input.invalid {
            border-color: var(--error-color);
        }

        .validation-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .validation-icon.valid {
            color: var(--success-color);
            opacity: 1;
        }

        .validation-icon.invalid {
            color: var(--error-color);
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Floating stars -->
    <div class="stars" id="stars"></div>

    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <i class="fas fa-star logo-icon"></i>
            <h1 class="register-title">Únete a MERLIN</h1>
            <p class="register-subtitle">Crea tu cuenta y descubre conversaciones mágicas</p>
        </div>

        <!-- Messages -->
        <?php if (!empty($error)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="post" class="register-form" id="registerForm">
            <div class="form-row">
                <div class="form-group">
                    <i class="fas fa-user form-icon"></i>
                    <input name="nombre" class="form-input" placeholder="Nombre" required 
                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                    <i class="fas fa-check validation-icon"></i>
                </div>
                <div class="form-group">
                    <i class="fas fa-user form-icon"></i>
                    <input name="apellido" class="form-input" placeholder="Apellido" required
                           value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>">
                    <i class="fas fa-check validation-icon"></i>
                </div>
            </div>

            <div class="form-group full-width">
                <i class="fas fa-building form-icon"></i>
                <input name="empresa" class="form-input" placeholder="Empresa (opcional)"
                       value="<?php echo isset($_POST['empresa']) ? htmlspecialchars($_POST['empresa']) : ''; ?>">
            </div>

            <div class="form-group full-width">
                <i class="fas fa-envelope form-icon"></i>
                <input name="email" type="email" class="form-input" placeholder="Correo electrónico" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <i class="fas fa-check validation-icon"></i>
            </div>

            <div class="form-group full-width">
                <i class="fas fa-phone form-icon"></i>
                <input name="telefono" class="form-input" placeholder="Teléfono" required
                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                <i class="fas fa-check validation-icon"></i>
            </div>

            <div class="form-group full-width">
                <i class="fas fa-lock form-icon"></i>
                <input name="password" type="password" class="form-input" placeholder="Contraseña" required id="password">
                <i class="fas fa-check validation-icon"></i>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
            </div>

            <div class="form-group full-width">
                <i class="fas fa-lock form-icon"></i>
                <input name="confirm_password" type="password" class="form-input" placeholder="Confirmar contraseña" required id="confirmPassword">
                <i class="fas fa-check validation-icon"></i>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-spinner loading"></i>
                <i class="fas fa-rocket"></i>
                Crear Cuenta Celestial
            </button>
        </form>

        <!-- Footer -->
        <div class="register-footer">
            <p class="privacy-text">
                Al registrarte aceptas nuestra 
                <a href="privacy.php" class="privacy-link">política de privacidad</a> 
                y el almacenamiento seguro de tus datos.
            </p>
            <p class="login-link">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </div>
    </div>

    <script>
        // Create floating stars
        function createStars() {
            const starsContainer = document.getElementById('stars');
            const starCount = 50;

            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.innerHTML = '✦';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 3 + 's';
                starsContainer.appendChild(star);
            }
        }

        // Form validation
        function validateForm() {
            const inputs = document.querySelectorAll('.form-input[required]');
            let allValid = true;

            inputs.forEach(input => {
                const validationIcon = input.nextElementSibling;
                let isValid = false;

                if (input.type === 'email') {
                    isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
                } else if (input.name === 'password') {
                    isValid = input.value.length >= 6;
                } else if (input.name === 'confirm_password') {
                    const password = document.getElementById('password').value;
                    isValid = input.value === password && input.value.length > 0;
                } else {
                    isValid = input.value.trim().length > 0;
                }

                if (isValid) {
                    input.classList.add('valid');
                    input.classList.remove('invalid');
                    validationIcon.classList.add('valid');
                    validationIcon.classList.remove('invalid');
                } else {
                    input.classList.add('invalid');
                    input.classList.remove('valid');
                    validationIcon.classList.add('invalid');
                    validationIcon.classList.remove('valid');
                    allValid = false;
                }
            });

            return allValid;
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthIndicator = document.getElementById('passwordStrength');
            const strengthFill = document.getElementById('strengthFill');
            
            if (password.length === 0) {
                strengthIndicator.classList.remove('visible');
                return;
            }

            strengthIndicator.classList.add('visible');
            
            let score = 0;
            if (password.length >= 6) score += 25;
            if (password.length >= 8) score += 25;
            if (/[A-Z]/.test(password)) score += 25;
            if (/[0-9]/.test(password)) score += 25;

            strengthFill.style.width = score + '%';
            
            if (score < 50) {
                strengthFill.className = 'strength-fill weak';
            } else if (score < 75) {
                strengthFill.className = 'strength-fill medium';
            } else {
                strengthFill.className = 'strength-fill strong';
            }
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            createStars();

            const form = document.getElementById('registerForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const submitBtn = document.getElementById('submitBtn');

            // Real-time validation
            form.addEventListener('input', function(e) {
                if (e.target.classList.contains('form-input')) {
                    validateForm();
                }

                if (e.target.id === 'password') {
                    checkPasswordStrength(e.target.value);
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return;
                }

                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Auto-focus first input
            document.querySelector('input[name="nombre"]').focus();
        });

        // Smooth scroll animations
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const stars = document.querySelectorAll('.star');
            stars.forEach((star, index) => {
                const speed = (index % 3 + 1) * 0.5;
                star.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    </script>
</body>
</html>