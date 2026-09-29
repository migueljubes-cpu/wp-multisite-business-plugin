<?php
if ( ! defined('ABSPATH') ) exit;
?>

<div class="panel-frame">

  <!-- CABECERA DEL PANEL -->
  <div class="panel-header-box">

    <!-- LOGO -->
    <img src="<?= esc_url( 'http://panel.punksetter.com/wp-content/uploads/sites/5/2026/08/logo-horizontal.png' ); ?>"
         class="panel-logo-top"
         alt="<?= esc_attr( 'Punksetter Panel' ); ?>"
         loading="lazy">

    <!-- FILA 1 -->
    <div class="panel-header-bar panel-row-1" role="tablist" aria-label="Panel principal fila 1">
      <button id="tab-btn-clientes" role="tab" aria-selected="true" aria-controls="tab-clientes" class="panel-tab-btn active" data-tab="clientes">Clientes</button>
      <button id="tab-btn-pedidos" role="tab" aria-selected="false" aria-controls="tab-pedidos" class="panel-tab-btn" data-tab="pedidos">Pedidos</button>
      <button id="tab-btn-correo" role="tab" aria-selected="false" aria-controls="tab-correo" class="panel-tab-btn" data-tab="correo">Correo</button>
      <button id="tab-btn-envios" role="tab" aria-selected="false" aria-controls="tab-envios" class="panel-tab-btn" data-tab="envios">Envios</button>
    </div>

    <!-- FILA 2 -->
    <div class="panel-header-bar panel-row-2" role="tablist" aria-label="Panel principal fila 2">
      <button id="tab-btn-odoo" role="tab" aria-selected="false" aria-controls="tab-odoo" class="panel-tab-btn" data-tab="odoo">Odoo</button>
      <button id="tab-btn-marketing" role="tab" aria-selected="false" aria-controls="tab-marketing" class="panel-tab-btn" data-tab="marketing">Marketing</button>
      <button id="tab-btn-cloudflare" role="tab" aria-selected="false" aria-controls="tab-cloudflare" class="panel-tab-btn" data-tab="cloudflare">Cloudflare</button>
      <button id="tab-btn-claude" role="tab" aria-selected="false" aria-controls="tab-claude" class="panel-tab-btn" data-tab="claude">Claude</button>
    </div>

  </div>

  <!-- CONTENIDO DEL PANEL -->
  <div class="panel-content-wrapper">

    <!-- CLIENTES -->
    <div id="tab-clientes" role="tabpanel" aria-labelledby="tab-btn-clientes" class="panel-content active-content">
      <h2>Clientes</h2>
      <?= shortcode_exists('panel_clientes') ? do_shortcode('[panel_clientes]') : '<p class="ps-warning">Módulo Clientes no disponible.</p>'; ?>
    </div>

    <!-- PEDIDOS -->
    <div id="tab-pedidos" role="tabpanel" aria-labelledby="tab-btn-pedidos" class="panel-content">
      <h2>Pedidos</h2>
      <?= shortcode_exists('panel_pedidos') ? do_shortcode('[panel_pedidos]') : '<p class="ps-warning">Módulo Pedidos no disponible.</p>'; ?>
    </div>

    <!-- CORREO -->
    <div id="tab-correo" role="tabpanel" aria-labelledby="tab-btn-correo" class="panel-content">
      <h2>Correo</h2>
      <?= shortcode_exists('punksetter_correo') ? do_shortcode('[punksetter_correo]') : '<p class="ps-warning">Módulo Correo no disponible.</p>'; ?>
    </div>

   <!-- ENVIOS -->
    <div id="tab-envios" role="tabpanel" aria-labelledby="tab-btn-envios" class="panel-content">
      <h2>Envios</h2>
    <?= do_shortcode('[punksetter_envios]'); ?>
  </div>


    <!-- ODOO -->
    <div id="tab-odoo" role="tabpanel" aria-labelledby="tab-btn-odoo" class="panel-content">
      <h2>Odoo</h2>
      <?= shortcode_exists('panel_odoo') ? do_shortcode('[panel_odoo]') : '<p class="ps-warning">Módulo Odoo no disponible.</p>'; ?>
    </div>

    <!-- MARKETING -->
    <div id="tab-marketing" role="tabpanel" aria-labelledby="tab-btn-marketing" class="panel-content">
      <h2>Marketing</h2>
      <?= shortcode_exists('panel_marketing') ? do_shortcode('[panel_marketing]') : '<p class="ps-warning">Módulo Marketing no disponible.</p>'; ?>
    </div>

    <!-- CLOUDFLARE -->
    <div id="tab-cloudflare" role="tabpanel" aria-labelledby="tab-btn-cloudflare" class="panel-content">
      <h2>Cloudflare</h2>
      <?= function_exists('punksetter_cloudflare_render') ? punksetter_cloudflare_render() : '<p class="ps-warning">Módulo Cloudflare no disponible.</p>'; ?>
    </div>

    <!-- CLAUDE -->
    <div id="tab-claude" role="tabpanel" aria-labelledby="tab-btn-claude" class="panel-content">
     <?= do_shortcode('[punksetter_ia]'); ?>
     </div>


    <!-- MODAL GLOBAL -->
    <div id="panel-modal" class="panel-modal" aria-hidden="true" role="dialog" aria-modal="true">
      <div class="panel-modal-content">
        <button id="panel-modal-close" class="panel-modal-close" aria-label="Cerrar modal">&times;</button>
        <div id="panel-modal-body"></div>
      </div>
    </div>

  </div>
</div>

<!-- BOTÓN FLOTANTE: BLOC DE NOTAS -->
<div class="punk-notes-btn" role="button" aria-label="Abrir notas" tabindex="0">
  <img src="<?= esc_url( 'http://panel.punksetter.com/wp-content/uploads/sites/5/2026/08/Bloc-de-notas.png' ); ?>" alt="Notas" loading="lazy">
</div>

<!-- BOTÓN FLOTANTE: FLOC -->
<div class="punk-floc-btn" role="button" aria-label="Abrir FLOC" tabindex="0">
  <img src="<?= esc_url( 'http://panel.punksetter.com/wp-content/uploads/sites/5/2026/08/floc-panel.png' ); ?>" alt="FLOC" loading="lazy">
</div>

<?php include PUNKSETTER_PANEL_DIR . 'templates/floc-template.php'; ?>
