<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="home.php">
            <i class="fas fa-glasses me-2 text-primary"></i>
            Optilente 2020
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavEmployee" aria-controls="navbarNavEmployee" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavEmployee">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Dropdown para Módulos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-th-large me-2"></i>Módulos
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="inventario.php">
                                <i class="fas fa-box-open me-2"></i>Stock
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="pedidos.php">
                                <i class="fas fa-shopping-cart me-2"></i>Pedidos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="prescripciones.php">
                                <i class="fas fa-file-medical me-2"></i>Prescripciones
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Reportes se mantiene separado -->
                <li class="nav-item">
                    <a class="nav-link" href="reportes.php">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <a href="perfil.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-user me-2"></i>Perfil
                </a>
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Script para asegurar que los dropdowns funcionen -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    dropdownElementList.map(function(dropdownToggleEl) {
        dropdownToggleEl.addEventListener('click', function(e) {
            e.preventDefault();
            var dropdown = this.nextElementSibling;
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            } else {
                dropdown.classList.add('show');
            }
        });
    });

    // Cerrar dropdowns cuando se hace clic fuera de ellos
    document.addEventListener('click', function(e) {
        if (!e.target.matches('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
            var dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(function(dropdown) {
                dropdown.classList.remove('show');
            });
        }
    });
});
</script>