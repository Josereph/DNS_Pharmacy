<?php
// guest.php - Vista pública para clientes (informativa)
// No requiere autenticación - Solo información de la farmacia

// Definir base_url para rutas correctas
$base_url = '/DNS_Pharmacy';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNS Pharmacy | Tu farmacia de confianza en El Salvador</title>
    <link rel="stylesheet" href="../assets/css/guest.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="guest-wrapper">
    
    <!-- ==================== HEADER ==================== -->
    <header class="guest-header">
        <div class="container">
            <div class="header-inner">
                <div class="logo-area">
                    <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" alt="DNS Pharmacy" class="header-logo">
                </div>
                
                <nav class="main-nav">
                    <ul>
                        <li><a href="#inicio" class="nav-link active">Inicio</a></li>
                        <li><a href="#servicios" class="nav-link">Servicios</a></li>
                        <li><a href="#nosotros" class="nav-link">Nosotros</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <button class="menu-toggle" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- ==================== HERO ==================== -->
    <section class="hero" id="inicio">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <span class="hero-badge">Bienvenidos a DNS Pharmacy</span>
                    <h1>Tu salud, <span class="highlight">nuestra prioridad</span></h1>
                    <p>Ofrecemos medicamentos de calidad, productos de cuidado personal y asesoría profesional. ¡Conoce nuestras promociones!</p>
                    <div class="hero-buttons">
                        <a href="#servicios" class="btn btn-primary">
                            <i class="fas fa-stethoscope"></i> Nuestros servicios
                        </a>
                        <a href="#nosotros" class="btn btn-outline">
                            <i class="fas fa-info-circle"></i> Conócenos
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="hero-img-placeholder">
                        <i class="fas fa-hand-holding-medical"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== STATS ==================== -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-row">
                <div class="stat-card-guest">
                    <div class="stat-icon"><i class="fas fa-tablets"></i></div>
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Productos disponibles</div>
                </div>
                <div class="stat-card-guest">
                    <div class="stat-icon"><i class="fas fa-user-md"></i></div>
                    <div class="stat-number">20+</div>
                    <div class="stat-label">Profesionales aliados</div>
                </div>
                <div class="stat-card-guest">
                    <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-number">10+</div>
                    <div class="stat-label">Años de experiencia</div>
                </div>
                <div class="stat-card-guest">
                    <div class="stat-icon"><i class="fas fa-smile"></i></div>
                    <div class="stat-number">5k+</div>
                    <div class="stat-label">Clientes felices</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== SERVICIOS ==================== -->
    <section class="servicios-section" id="servicios">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nuestros <span class="highlight">Servicios</span></h2>
                <p class="section-subtitle">Servicios profesionales para tu salud</p>
            </div>
            <div class="servicios-grid">
                <div class="servicio-card">
                    <div class="servicio-icon"><i class="fas fa-stethoscope"></i></div>
                    <h3>Asesoría Farmacéutica</h3>
                    <p>Consulta gratuita con nuestros farmacéuticos profesionales.</p>
                </div>
                <div class="servicio-card">
                    <div class="servicio-icon"><i class="fas fa-tint"></i></div>
                    <h3>Toma de presión</h3>
                    <p>Servicio gratuito de monitoreo de presión arterial.</p>
                </div>
                <div class="servicio-card">
                    <div class="servicio-icon"><i class="fas fa-truck"></i></div>
                    <h3>Entregas a domicilio</h3>
                    <p>Llevamos tus medicamentos hasta tu puerta.</p>
                </div>
                <div class="servicio-card">
                    <div class="servicio-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Programa de afiliación</h3>
                    <p>Beneficios exclusivos para clientes frecuentes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== NOSOTROS ==================== -->
    <section class="nosotros-section" id="nosotros">
        <div class="container">
            <div class="nosotros-content">
                <div class="nosotros-text">
                    <span class="section-badge">Sobre nosotros</span>
                    <h2>Comprometidos con tu <span class="highlight">salud</span></h2>
                    <p>DNS Pharmacy nació con el propósito de brindar acceso a medicamentos de calidad y atención profesional a toda la familia salvadoreña.</p>
                    <p>Contamos con más de 10 años de experiencia en el sector farmacéutico, ofreciendo productos originales, precios justos y un servicio cálido y confiable.</p>
                    <div class="nosotros-features">
                        <div><i class="fas fa-check-circle"></i> Productos 100% originales</div>
                        <div><i class="fas fa-check-circle"></i> Precios competitivos</div>
                        <div><i class="fas fa-check-circle"></i> Personal capacitado</div>
                        <div><i class="fas fa-check-circle"></i> Horarios extendidos</div>
                    </div>
                </div>
                <div class="nosotros-image">
                    <div class="nosotros-img-placeholder">
                        <i class="fas fa-hospital-user"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer class="guest-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <img src="<?php echo $base_url; ?>/assets/img/DNS_LOGO.png" alt="DNS Pharmacy" class="footer-logo-img">
                    </div>
                    <p class="footer-description">
                        Boulevard Los Próceres, San Salvador, El Salvador
                    </p>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@dnspharmacy.com</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <p>Lunes a Viernes: 8:00 am - 8:00 pm</p>
                            <p>Sábados: 8:00 am - 6:00 pm</p>
                        </div>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Enlaces rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="#inicio">Inicio</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                        <li><a href="#nosotros">Nosotros</a></li>
                        <li><a href="/DNS_Pharmacy/views/Login.php">Área de trabajo</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Contáctanos</h4>
                    <div class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contacto@dnspharmacy.com</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fab fa-whatsapp"></i>
                        <span>+503 7622-4659</span>
                    </div>
                    <div class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>San Salvador, El Salvador</span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?= date('Y') ?> DNS Pharmacy. Todos los derechos reservados.</p>
                    <p>Desarrollado por <strong>DevCore</strong></p>
                </div>
            </div>
        </div>
    </footer>

</div><!-- fin guest-wrapper -->

<!-- ==================== CHATBOT DNSBOT ==================== -->
<button id="dns-chat-btn" title="Habla con DNSBot">💊</button>

<div id="dns-chat-window">
    <div id="dns-chat-header">
        <div class="dns-avatar">🤖</div>
        <div class="dns-info">
            <strong>DNSBot</strong>
            <span>Asistente de DNS Pharmacy</span>
        </div>
        <button id="dns-chat-close" title="Cerrar">✕</button>
    </div>

    <div id="dns-chat-chips">
        <span class="dns-chip" onclick="dnsChip(this)">¿Qué medicamentos tienen?</span>
        <span class="dns-chip" onclick="dnsChip(this)">¿Tienen analgésicos?</span>
        <span class="dns-chip" onclick="dnsChip(this)">¿Cuáles son los precios?</span>
        <span class="dns-chip" onclick="dnsChip(this)">¿Qué hay en stock?</span>
    </div>

    <div id="dns-chat-messages">
        <div class="dns-msg dns-bot">
            👋 ¡Hola! Soy <strong>DNSBot</strong>, el asistente de DNS Pharmacy.<br>
            Puedo ayudarte a consultar <strong>medicamentos disponibles</strong>,
            <strong>precios</strong> y <strong>stock</strong>. ¿En qué te ayudo?
        </div>
    </div>

    <div id="dns-chat-footer">
        <input
            type="text"
            id="dns-chat-input"
            placeholder="Escribe tu pregunta..."
            maxlength="300"
            autocomplete="off"
        />
        <button id="dns-chat-send" title="Enviar">➤</button>
    </div>
</div>
<!-- ==================== FIN CHATBOT ==================== -->

<script src="../assets/js/guest.js"></script>
</body>
</html>