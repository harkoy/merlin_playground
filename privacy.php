<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 Política de Privacidad - MERLIN</title>
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
            --card-bg: rgba(26, 35, 50, 0.9);
            --border-glow: rgba(212, 175, 55, 0.4);
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
            line-height: 1.6;
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

        /* Header */
        .header {
            background: rgba(11, 20, 38, 0.95);
            backdrop-filter: blur(15px);
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

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary-gold);
            font-size: 1.8rem;
            font-weight: 300;
            letter-spacing: 2px;
            text-decoration: none;
        }

        .logo i {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--primary-gold);
            background: rgba(212, 175, 55, 0.1);
            transform: translateY(-2px);
        }

        .nav-link.primary {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: var(--deep-blue);
            font-weight: 600;
        }

        .nav-link.primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px var(--shadow-gold);
        }

        /* Main content */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .hero {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-icon {
            font-size: 4rem;
            color: var(--primary-gold);
            margin-bottom: 1rem;
            display: block;
        }

        .hero h1 {
            font-size: 3rem;
            color: var(--secondary-gold);
            margin-bottom: 1rem;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Privacy sections */
        .privacy-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .privacy-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 2px solid var(--border-glow);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
        }

        .privacy-card:nth-child(1) { animation-delay: 0.1s; }
        .privacy-card:nth-child(2) { animation-delay: 0.2s; }
        .privacy-card:nth-child(3) { animation-delay: 0.3s; }
        .privacy-card:nth-child(4) { animation-delay: 0.4s; }

        .privacy-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.2);
            border-color: var(--primary-gold);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--deep-blue);
        }

        .card-title {
            font-size: 1.5rem;
            color: var(--secondary-gold);
            font-weight: 600;
            margin: 0;
        }

        .card-content {
            color: var(--text-light);
            line-height: 1.7;
        }

        .card-content ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .card-content li {
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }

        .highlight {
            color: var(--secondary-gold);
            font-weight: 600;
        }

        /* CTA Section */
        .cta-section {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 2px solid var(--primary-gold);
            border-radius: 25px;
            padding: 3rem 2rem;
            text-align: center;
            margin-top: 3rem;
            animation: fadeInUp 0.8s ease-out 0.5s both;
        }

        .cta-title {
            font-size: 2rem;
            color: var(--secondary-gold);
            margin-bottom: 1rem;
            font-weight: 400;
        }

        .cta-text {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
            color: var(--deep-blue);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px var(--shadow-gold);
        }

        .btn-secondary {
            background: transparent;
            color: var(--primary-gold);
            border: 2px solid var(--primary-gold);
        }

        .btn-secondary:hover {
            background: var(--primary-gold);
            color: var(--deep-blue);
            transform: translateY(-3px);
        }

        /* Footer */
        .footer {
            background: rgba(11, 20, 38, 0.95);
            backdrop-filter: blur(15px);
            border-top: 2px solid var(--primary-gold);
            padding: 2rem;
            text-align: center;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            color: var(--text-muted);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .footer-link {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--primary-gold);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }

            .container {
                padding: 2rem 1rem;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .privacy-card {
                padding: 1.5rem;
            }

            .cta-section {
                padding: 2rem 1rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        /* Scroll animations */
        .fade-in-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }

        .fade-in-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="chat.php" class="logo">
                <i class="fas fa-star"></i>
                MERLIN
            </a>
            <nav class="nav-links">
                <a href="chat.php" class="nav-link">
                    <i class="fas fa-comments"></i>
                    <span>Chat</span>
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user-circle"></i>
                    <span>Perfil</span>
                </a>
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Salir</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Hero Section -->
        <section class="hero">
            <i class="fas fa-shield-alt hero-icon"></i>
            <h1>Tu Privacidad es Nuestra Prioridad</h1>
            <p>En MERLIN, protegemos tu información personal con los más altos estándares de seguridad y transparencia.</p>
        </section>

        <!-- Privacy Grid -->
        <div class="privacy-grid">
            <!-- Data Collection -->
            <div class="privacy-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h2 class="card-title">¿Qué Información Recopilamos?</h2>
                </div>
                <div class="card-content">
                    <p>Recopilamos únicamente la información necesaria para brindarte la mejor experiencia:</p>
                    <ul>
                        <li><span class="highlight">Mensajes del chat:</span> Tus conversaciones para mantener el historial</li>
                        <li><span class="highlight">Datos de perfil:</span> Nombre de usuario y preferencias de personalización</li>
                        <li><span class="highlight">Configuraciones:</span> Tema, colores y preferencias de diseño</li>
                        <li><span class="highlight">Información técnica:</span> Datos de sesión para el funcionamiento seguro</li>
                    </ul>
                </div>
            </div>

            <!-- Data Usage -->
            <div class="privacy-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h2 class="card-title">¿Cómo Usamos Tu Información?</h2>
                </div>
                <div class="card-content">
                    <p>Tu información se utiliza exclusivamente para:</p>
                    <ul>
                        <li><span class="highlight">Generar respuestas:</span> Procesamos tus mensajes con la API de OpenAI</li>
                        <li><span class="highlight">Personalizar experiencia:</span> Aplicar tus preferencias de tema y color</li>
                        <li><span class="highlight">Mantener historial:</span> Conservar el contexto de tus conversaciones</li>
                        <li><span class="highlight">Mejorar el servicio:</span> Optimizar la funcionalidad y rendimiento</li>
                    </ul>
                </div>
            </div>

            <!-- Data Security -->
            <div class="privacy-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h2 class="card-title">Seguridad y Protección</h2>
                </div>
                <div class="card-content">
                    <p>Implementamos múltiples capas de seguridad:</p>
                    <ul>
                        <li><span class="highlight">Encriptación:</span> Toda la comunicación está cifrada con SSL/TLS</li>
                        <li><span class="highlight">Acceso controlado:</span> Solo tú puedes acceder a tus conversaciones</li>
                        <li><span class="highlight">Servidores seguros:</span> Infraestructura protegida y monitoreada</li>
                        <li><span class="highlight">API confiable:</span> Utilizamos OpenAI con estrictos estándares de privacidad</li>
                    </ul>
                </div>
            </div>

            <!-- Your Rights -->
            <div class="privacy-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2 class="card-title">Tus Derechos y Control</h2>
                </div>
                <div class="card-content">
                    <p>Tienes control total sobre tu información:</p>
                    <ul>
                        <li><span class="highlight">Acceso completo:</span> Visualizar toda tu información desde tu perfil</li>
                        <li><span class="highlight">Edición libre:</span> Modificar tus datos personales cuando quieras</li>
                        <li><span class="highlight">Eliminación:</span> Borrar mensajes individuales o tu cuenta completa</li>
                        <li><span class="highlight">Transparencia:</span> Información clara sobre el uso de tus datos</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <section class="cta-section">
            <h2 class="cta-title">¿Tienes Alguna Pregunta?</h2>
            <p class="cta-text">
                Si necesitas más información sobre nuestra política de privacidad o quieres ejercer alguno de tus derechos, 
                no dudes en contactarnos o gestionar tu información desde tu perfil.
            </p>
            <div class="cta-buttons">
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-user-cog"></i>
                    Gestionar Mi Información
                </a>
                <a href="chat.php" class="btn btn-secondary">
                    <i class="fas fa-comments"></i>
                    Volver al Chat
                </a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-links">
                <a href="chat.php" class="footer-link">Inicio</a>
                <a href="profile.php" class="footer-link">Mi Perfil</a>
                <a href="privacy.php" class="footer-link">Privacidad</a>
            </div>
            <p>&copy; 2025 MERLIN. Protegiendo tu privacidad con tecnología celestial.</p>
        </div>
    </footer>

    <script>
        // Smooth scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe all privacy cards
        document.querySelectorAll('.privacy-card').forEach(card => {
            card.classList.add('fade-in-scroll');
            observer.observe(card);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add subtle parallax effect to background
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.body;
            const speed = scrolled * 0.5;
            parallax.style.backgroundPosition = `center ${speed}px`;
        });
    </script>
</body>
</html>