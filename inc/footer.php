<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-glasses me-3 text-white fs-2"></i>
                    <h3 class="footer-title">Optilente 2020</h3>
                </div>
                <p class="text-white">Comprometidos con tu visión y bienestar. Ofrecemos soluciones visuales de alta calidad con tecnología de vanguardia.</p>
            </div>
            <div class="col-md-4">
                <h4 class="text-white mb-4">Navegación</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="#servicios">Servicios</a></li>
                    <li><a href="#marcas">Marcas</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h4 class="text-white mb-4">Contáctanos</h4>
                <div class="social-icons">
                    <a href="https://www.facebook.com/p/Optilente-2020-100086649541903/" class="text-white"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/optilente2020c.a/" class="text-white"><i class="fab fa-instagram"></i></a>
                </div>
                <p class="text-white mt-3">
                    <i class="fas fa-map-marker-alt me-2"></i> Dirección: C. Mariño, Cumaná 6101, Sucre
                    <br>
                    <i class="fas fa-phone me-2"></i> Teléfono: +584128250539
                    <br>
                    <i class="fas fa-envelope me-2"></i> Email: optilente2022@gmail.com
                </p>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Optilente 2020. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS animation
    AOS.init();
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>