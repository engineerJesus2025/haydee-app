<form action="?pagina=cartelera_virtual_controlador.php&accion=guardar" method="POST" id="form_cartelera" name="form_cartelera" enctype="multipart/form-data">
    <div class="container mt-4">
        <h3 class="text-center mb-4">Registrar Publicación</h3>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="titulo">Título de la publicación</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-type"></i></span>
                    <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Título"
                        aria-label="titulo" minlength="3" maxlength="30" required>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <label for="descripcion">Descripción de la publicación</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="Describe el contenido..." required></textarea>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="fecha">Fecha de la publicación</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" class="form-control" name="fecha" id="fecha" required>
                </div>
            </div>

            <div class="col-md-6">
                <label for="imagen">Imagen de la publicación</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-image"></i></span>
                    <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*">
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="prioridad">Prioridad de la publicación</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                    <select class="form-select" name="prioridad" id="prioridad" required>
                        <option value="" disabled selected>Seleccione una prioridad</option>
                        <option value="1">Alta</option>
                        <option value="2">Media</option>
                        <option value="3">Baja</option>
                    </select>
                </div>
            </div>
        </div>
        <input type="hidden" id="nombre_usuario" value="<?php echo $_SESSION['nombre_completo']; ?>">

        <div class="row mb-3">
            <div class="col-md-12 text-center">
                <button class="btn btn-primary" type="submit" id="boton_formulario">Registrar</button>
            </div>
        </div>
    </div>
</form>
