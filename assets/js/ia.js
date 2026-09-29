/* ============================================================
   IA DEL PANEL PUNKSETTER (Claude)
   Versión FINAL — estable y compatible con core/init.php
   ============================================================ */

jQuery(document).ready(function ($) {
  const input = $("#ps-ia-input");
  const output = $("#ps-ia-output");
  const sendBtn = $("#ps-ia-send");
  const status = $("#ps-ia-status");

  // 🔥 AJAX del panel (localizado desde init.php)
  const ajaxIA =
    typeof punkIA !== "undefined" ? punkIA.ajaxurl : "/wp-admin/admin-ajax.php";

  /* ============================================================
       ENVÍO DE CONSULTA IA (CHAT CLAUDE)
       ============================================================ */
  sendBtn.on("click", function () {
    const prompt = input.val().trim();

    if (!prompt || prompt.length === 0) {
      output.html('<p class="ps-ia-error">Escribe algo primero.</p>');
      return;
    }

    status.show().text("Procesando consulta IA...");
    output.html("");

    $.ajax({
      url: ajaxIA,
      type: "POST",
      dataType: "json",
      data: {
        action: "punksetter_ia_execute",
        prompt: prompt,
      },
      success: function (response) {
        status.hide();

        if (response && response.success) {
          output.html(
            '<div class="ps-ia-reply">' + response.data.reply + "</div>",
          );
        } else {
          output.html(
            '<p class="ps-ia-error">Error IA: ' +
              (response ? response.data : "Respuesta inválida") +
              "</p>",
          );
        }
      },

      error: function () {
        status.hide();
        output.html('<p class="ps-ia-error">Error de conexión con IA.</p>');
      },
    });
  });

  /* ============================================================
       PASO 4 — ACCIONES IA DEL PANEL (CREAR, BORRAR, SYNC, ETC.)
       ============================================================ */

  $("#ia-run-action").on("click", function () {
    let action = $("#ia-action-name").val().trim();
    let payload = $("#ia-action-payload").val().trim();

    if (action.length === 0) {
      $("#ia-action-output").text("Debes escribir una acción IA.");
      return;
    }

    $.post(
      ajaxIA,
      {
        action: "punksetter_ia_action",
        action_name: action,
        payload: payload,
      },
      function (response) {
        $("#ia-action-output").text(JSON.stringify(response, null, 2));
      },
    ).fail(function () {
      $("#ia-action-output").text("Error de conexión con IA.");
    });
  });
});
