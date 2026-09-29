<div class="ps-envios-wrapper">

    <h2 class="ps-envios-title">📦 Envíos</h2>

    <!-- Pestañas internas -->
    <div class="ps-envios-tabs">
        <button class="ps-envios-tab active" data-target="zeleris">Zeleris</button>
        <button class="ps-envios-tab disabled" data-target="correos">Correos (próximamente)</button>
    </div>

    <!-- Contenedor donde se cargan los módulos -->
    <div id="ps-envios-content">

        <!-- Zeleris cargará aquí -->
        <div id="ps-envios-zeleris" class="ps-envios-section active">
            <?php include PUNKSETTER_PANEL_DIR . 'templates/envios-zeleris-template.php'; ?>
        </div>

        <!-- Correos (desactivado) -->
        <div id="ps-envios-correos" class="ps-envios-section" style="display:none; opacity:0.4;">
            <?php include PUNKSETTER_PANEL_DIR . 'templates/envios-correos-template.php'; ?>
        </div>

    </div>

</div>
