/* ============================================================
   ZELERIS — Lógica de búsquedas y resultados
   ============================================================ */

jQuery(document).ready(function ($) {
  /* ------------------------------------------
       FUNCIÓN GENERAL PARA ENVIAR PETICIONES
    ------------------------------------------ */
  function enviarBusquedaZeleris(tipo, datos) {
    $("#ps-zeleris-resultados").html(
      '<p class="ps-zeleris-info">Buscando en Zeleris…</p>',
    );

    $.ajax({
      url: punkEnvios.ajaxurl,
      type: "POST",
      data: {
        action: "punksetter_zeleris_busqueda",
        tipo: tipo,
        datos: datos,
      },
      success: function (res) {
        if (!res || res.error) {
          $("#ps-zeleris-resultados").html(
            '<p class="ps-zeleris-info">No se encontraron resultados o hubo un error.</p>',
          );
          return;
        }

        $("#ps-zeleris-resultados").html(res.html);
      },
      error: function () {
        $("#ps-zeleris-resultados").html(
          '<p class="ps-zeleris-info">Error de conexión con Zeleris.</p>',
        );
      },
    });
  }

  /* ------------------------------------------
       BÚSQUEDA RÁPIDA
    ------------------------------------------ */
  $("#zeleris_rapida_send").on("click", function () {
    let datos = {
      num_seguimiento: $("#zeleris_num_seguimiento").val(),
      del1: $("#zeleris_del1").val(),
      expedicion: $("#zeleris_expedicion").val(),
      ref_expedicion: $("#zeleris_ref_expedicion").val(),
      del1_recogida: $("#zeleris_del1_recogida").val(),
      recogida: $("#zeleris_recogida").val(),
      ref_recogida: $("#zeleris_ref_recogida").val(),
    };

    enviarBusquedaZeleris("rapida", datos);
  });

  $("#zeleris_rapida_clear").on("click", function () {
    $("#ps-zeleris-rapida input").val("");
  });

  /* ------------------------------------------
       BÚSQUEDA AVANZADA
    ------------------------------------------ */
  $("#zeleris_av_send").on("click", function () {
    let datos = {
      trafico: $("#zeleris_trafico").val(),
      num_seguimiento: $("#zeleris_av_num_seguimiento").val(),
      expedicion: $("#zeleris_av_expedicion").val(),
      referencia: $("#zeleris_av_referencia").val(),
      cliente: $("#zeleris_av_cliente").val(),
      fecha_desde: $("#zeleris_av_fecha_desde").val(),
      fecha_hasta: $("#zeleris_av_fecha_hasta").val(),
      situacion: $("#zeleris_av_situacion").val(),
      destinatario: $("#zeleris_av_destinatario").val(),
      provincia: $("#zeleris_av_provincia").val(),
      poblacion: $("#zeleris_av_poblacion").val(),
      orden: $("#zeleris_av_orden").val(),
    };

    enviarBusquedaZeleris("avanzada", datos);
  });

  $("#zeleris_av_clear").on("click", function () {
    $("#ps-zeleris-avanzada input").val("");
    $("#ps-zeleris-avanzada select").prop("selectedIndex", 0);
  });

  /* ------------------------------------------
       ABRIR DETALLES (HISTORIAL)
    ------------------------------------------ */
  $(document).on("click", ".ps-zeleris-detalle", function () {
    let id = $(this).data("id");

    $("#ps-zeleris-detalles").html("<p>Cargando historial…</p>");
    $("#ps-zeleris-modal").fadeIn(200);

    $.ajax({
      url: punkEnvios.ajaxurl,
      type: "POST",
      data: {
        action: "punksetter_zeleris_detalles",
        id: id,
      },
      success: function (res) {
        $("#ps-zeleris-detalles").html(res.html);
      },
      error: function () {
        $("#ps-zeleris-detalles").html("<p>Error al cargar detalles.</p>");
      },
    });
  });
});
