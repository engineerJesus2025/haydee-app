<div class="input-group grupo-buscador-unificado shadow-sm">
    <span class="input-group-text buscador-bg border-end-0">
        <i class="bi bi-search transition-color text-muted" id="icono_busqueda"></i>
    </span>
    <input type="text" id="busqueda_global" data-tooltip="true" title="Buscar Registro (B)" class="form-control border-start-0 rounded-end shadow-none buscador-bg" placeholder="<?php echo $placeholder_buscar ?? "Buscar..." ?>">
    <button class="btn border-start-0 d-none text-muted shadow-none hover-limpiar buscador-bg" type="button" id="btn_limpiar_busqueda" data-tooltip="true" title="Limpiar búsqueda">
        <i class="bi bi-x-lg"></i>
    </button>
    
</div>