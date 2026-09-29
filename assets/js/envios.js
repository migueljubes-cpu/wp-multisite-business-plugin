/* ============================================================
   ENVÍOS — Control general de pestañas
   ============================================================ */

jQuery(document).ready(function ($) {
  /* ------------------------------------------
       CAMBIO ENTRE ZELERIS / CORREOS
    ------------------------------------------ */
  $(".ps-envios-tab").on("click", function () {
    if ($(this).hasClass("disabled")) {
      return; // Correos está desactivado
    }

    $(".ps-envios-tab").removeClass("active");
    $(this).addClass("active");

    let target = $(this).data("target");

    $(".ps-envios-section").removeClass("active").hide();
    $("#ps-envios-" + target)
      .addClass("active")
      .fadeIn(200);
  });

  /* ------------------------------------------
       CAMBIO ENTRE BÚSQUEDA RÁPIDA / AVANZADA
       (solo dentro de Zeleris)
    ------------------------------------------ */
  $(".ps-zeleris-btn").on("click", function () {
    $(".ps-zeleris-btn").removeClass("active");
    $(this).addClass("active");

    let mode = $(this).data("mode");

    $(".ps-zeleris-section").removeClass("active").hide();
    $("#ps-zeleris-" + mode)
      .addClass("active")
      .fadeIn(200);
  });

  /* ------------------------------------------
       CERRAR MODAL DE DETALLES
    ------------------------------------------ */
  $(document).on("click", ".ps-modal-close", function () {
    $("#ps-zeleris-modal").fadeOut(200);
  });

  $(document).on("click", "#ps-zeleris-modal", function (e) {
    if (e.target.id === "ps-zeleris-modal") {
      $("#ps-zeleris-modal").fadeOut(200);
    }
  });
});
