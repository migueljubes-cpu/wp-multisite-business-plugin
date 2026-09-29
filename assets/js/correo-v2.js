jQuery(document).ready(function ($) {
  const apiURL = punkCorreo.api_url;
  const authURL = punkCorreo.auth_url;
  const viewURL = punkCorreo.view_url;

  const tableBody = $("#ps-gmail-body");
  const statusBox = $("#ps-gmail-status");
  const modal = $("#ps-gmail-modal");

  // ================================
  // BOTÓN CONECTAR GMAIL
  // ================================
  $("#ps-connect-gmail").on("click", function () {
    window.location.href = authURL;
  });

  // ================================
  // BOTÓN DESCONECTAR GMAIL
  // ================================
  $("#ps-disconnect-gmail").on("click", function () {
    statusBox.text("Desconectado de Gmail");
    tableBody.html("");
    modal.hide();
  });

  // ================================
  // CARGAR CORREOS REALES
  // ================================
  function cargarCorreos() {
    statusBox.text("Cargando correos...");

    $.ajax({
      url: apiURL,
      method: "GET",
      dataType: "json",

      success: function (res) {
        if (res.error) {
          statusBox.text("No conectado a Gmail");
          return;
        }

        statusBox.text("Conectado ✔");

        tableBody.html("");

        res.messages.forEach((msg) => {
          const fila = `
                        <tr>
                            <td>${msg.from}</td>
                            <td>${msg.subject}</td>
                            <td>${msg.date}</td>
                            <td>
                                <button class="ps-btn-view" data-id="${msg.id}">
                                    Ver
                                </button>
                            </td>
                        </tr>
                    `;

          tableBody.append(fila);
        });
      },

      error: function () {
        statusBox.text("Error al cargar correos");
      },
    });
  }

  // ================================
  // ABRIR MODAL Y CARGAR CUERPO DEL CORREO
  // ================================
  $(document).on("click", ".ps-btn-view", function () {
    const id = $(this).data("id");

    modal.show();
    $("#ps-modal-body").html("Cargando contenido...");

    $.ajax({
      url: viewURL + "&id=" + id,
      method: "GET",
      dataType: "json",

      success: function (res) {
        let body = "";

        // Función para decodificar Base64 URL-Safe
        function decodeData(data) {
          try {
            return atob(data.replace(/-/g, "+").replace(/_/g, "/"));
          } catch (e) {
            return null;
          }
        }

        // 1. Caso: el correo viene sin partes (texto plano o HTML simple)
        if (res.payload.body && res.payload.body.data) {
          body = decodeData(res.payload.body.data);
        }

        // 2. Caso: el correo viene multipart (lo más común)
        if (!body && res.payload.parts) {
          res.payload.parts.forEach((part) => {
            // Partes directas
            if (part.body && part.body.data) {
              const decoded = decodeData(part.body.data);
              if (decoded) body = decoded;
            }

            // Partes anidadas (multipart/alternative)
            if (part.parts) {
              part.parts.forEach((sub) => {
                if (sub.body && sub.body.data) {
                  const decoded = decodeData(sub.body.data);
                  if (decoded) body = decoded;
                }
              });
            }
          });
        }

        // 3. Si no se pudo decodificar nada
        if (!body) {
          body = "(No se pudo decodificar el cuerpo del correo)";
        }

        $("#ps-modal-body").html(body);
      },

      error: function () {
        $("#ps-modal-body").html("Error al cargar el correo");
      },
    });
  });

  // ================================
  // CERRAR MODAL
  // ================================
  $(".ps-modal-close").on("click", function () {
    modal.hide();
  });

  // ================================
  // AUTO-CARGA DE CORREOS AL ENTRAR
  // ================================
  cargarCorreos();
});
