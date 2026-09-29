document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("panel-modal");
  const modalBody = document.getElementById("panel-modal-body");
  const modalClose = document.getElementById("panel-modal-close");

  modalClose.addEventListener("click", () => {
    modal.style.display = "none";
    modalBody.innerHTML = "";
  });

  function activarBotones() {
    document.querySelectorAll(".ver-direccion").forEach((btn) => {
      btn.addEventListener("click", () => {
        const nombre = btn.dataset.nombre;
        const direccion = btn.dataset.direccion;
        const telefono = btn.dataset.telefono;
        const email = btn.dataset.email;

        modalBody.innerHTML = `
                    <h3>Dirección del cliente</h3>
                    <p><strong>Nombre:</strong> ${nombre}</p>
                    <p><strong>Email:</strong> ${email}</p>
                    <p><strong>Teléfono:</strong> ${telefono}</p>
                    <p><strong>Dirección:</strong> ${direccion}</p>
                `;

        modal.style.display = "block";
      });
    });

    document.querySelectorAll(".ver-detalles").forEach((btn) => {
      btn.addEventListener("click", () => {
        const nombre = btn.dataset.nombre;
        const email = btn.dataset.email;
        const telefono = btn.dataset.telefono;
        const total = btn.dataset.total;
        const pedidos = JSON.parse(btn.dataset.pedidos);

        let pedidosHTML = "";

        pedidos.forEach((p) => {
          pedidosHTML += `
                        <div class="pedido-item">
                            <p><strong>ID:</strong> ${p.id}</p>
                            <p><strong>Total:</strong> ${p.total}</p>
                            <p><strong>Fecha:</strong> ${p.fecha}</p>
                            <p><strong>Estado:</strong> ${p.estado}</p>
                            <hr>
                        </div>
                    `;
        });

        modalBody.innerHTML = `
                    <h3>Detalles del cliente</h3>
                    <p><strong>Nombre:</strong> ${nombre}</p>
                    <p><strong>Email:</strong> ${email}</p>
                    <p><strong>Teléfono:</strong> ${telefono}</p>
                    <p><strong>Total gastado:</strong> ${total}</p>

                    <h4>Pedidos</h4>
                    ${pedidosHTML}
                `;

        modal.style.display = "block";
      });
    });
  }

  activarBotones();

  // ORDENACIÓN AJAX
  document.querySelectorAll(".ordenable").forEach((th) => {
    th.addEventListener("click", () => {
      let col = th.dataset.col;
      let dir = th.dataset.dir === "ASC" ? "DESC" : "ASC";
      th.dataset.dir = dir;

      fetch(
        `/wp-admin/admin-ajax.php?action=panel_ordenar_clientes&order_by=${col}&order_dir=${dir}`,
      )
        .then((r) => r.json())
        .then((res) => {
          if (!res.success) return;

          let tbody = document.querySelector(".panel-table tbody");
          tbody.innerHTML = "";

          res.data.forEach((row) => {
            tbody.innerHTML += `
                            <tr>
                                <td>${row.nombre}</td>
                                <td>${row.email}</td>
                                <td>${row.telefono}</td>
                                <td>${row.direccion}</td>
                                <td>${row.total}</td>
                                <td>${row.num_pedidos}</td>
                                <td>${row.ultimo_pedido}</td>
                                <td>
                                    <button class="panel-btn-small ver-direccion"
                                        data-nombre="${row.nombre}"
                                        data-direccion="${row.direccion}"
                                        data-telefono="${row.telefono}"
                                        data-email="${row.email}">
                                        Ver dirección
                                    </button>

                                    <button class="panel-btn-small ver-detalles"
                                        data-nombre="${row.nombre}"
                                        data-email="${row.email}"
                                        data-telefono="${row.telefono}"
                                        data-total="${row.total}"
                                        data-pedidos='${row.pedidos_json}'>
                                        Detalles
                                    </button>
                                </td>
                            </tr>
                        `;
          });

          activarBotones();
        });
    });
  });
});
