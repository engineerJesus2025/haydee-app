<?php 
if ($permisosVista['registrar']) : 
?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-tooltip="true" 
    data-bs-target="<?php echo $btn_nuevo['target'] ?? '#modal_registrar';?>" 
    title="<?php echo $btn_nuevo['tooltip'] ?? 'Nuevo'; ?>" id="boton_nuevo_registro">
        <i class="bi bi-plus-lg me-1"></i>
        <?php echo $btn_nuevo['texto'] ?? 'Nuevo Registro'; ?>
    </button>
<?php endif; ?>