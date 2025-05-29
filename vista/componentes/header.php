<header class="row justify-content-between" style="background-color: #3939a9;">
    <div class="header_toggle col-md-3">
        <i class=' bi bi-list bi-x-lg' id="header-toggle" style="color: #fff"></i> 
    </div>
    <div class="col-md-3 text-right">
        <div class="dropdown mt-1 d-flex justify-content-end">
            <button class="btn bg-none text-white dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Hola, <?php echo $_SESSION["nombre_completo"]; ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?pagina=login_controlador.php&accion=cerrar">Salir</a></li>
            </ul>
        </div>
    </div>
</header>