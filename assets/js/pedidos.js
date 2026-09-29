document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("panel-modal");
  const modalBody = document.getElementById("panel-modal-body");
  const modalClose = document.getElementById("panel-modal-close");

  // Cerrar modal
  modalClose.addEventListener("click", () => {
    modal.style.display = "none";
    modalBody.innerHTML = "";
  });

  /* ============================================================
       BOTÓN VER — VER DETALLES DEL PEDIDO
       ============================================================ */
  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".view-order");
    if (!btn) return;

    let orderId = btn.dataset.order;

    fetch(
      `/wp-admin/admin-ajax.php?action=panel_ver_pedido&order_id=${orderId}`,
    )
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) {
          alert("Error: " + res.data);
          return;
        }

        let o = res.data;

        let productosHTML = "";
        o.items.forEach((item) => {
          productosHTML += `
                        <li>${item.producto} — ${item.cantidad} uds — ${item.total}</li>
                    `;
        });

        modalBody.innerHTML = `
                    <div class="pedido-scroll">
                        <h2>Pedido #${o.id}</h2>
                        <p><strong>Cliente:</strong> ${o.cliente}</p>
                        <p><strong>Estado:</strong> ${o.estado}</p>
                        <p><strong>Total:</strong> ${o.total}</p>
                        <p><strong>Fecha:</strong> ${o.fecha}</p>

                        <h3>Productos</h3>
                        <ul>${productosHTML}</ul>
                    </div>
                `;

        modal.style.display = "block";
      });
  });

  /* ============================================================
       BOTÓN ESTADO — CAMBIAR ESTADO DEL PEDIDO
       ============================================================ */
  document.addEventListener("click", function (e) {
    const btn = e.target.closest(".change-status");
    if (!btn) return;

    let orderId = btn.dataset.order;

    modalBody.innerHTML = `
            <h2>Cambiar estado del pedido #${orderId}</h2>

            <select id="ps-estado-select">
                <option value="processing">Procesando</option>
                <option value="completed">Completado</option>
                <option value="on-hold">En espera</option>
                <option value="cancelled">Cancelado</option>
                <option value="refunded">Reembolsado</option>
            </select>

            <button id="ps-estado-guardar" class="panel-btn-small">
                Guardar estado
            </button>
        `;

    modal.style.display = "block";

    document
      .getElementById("ps-estado-guardar")
      .addEventListener("click", function () {
        let estado = document.getElementById("ps-estado-select").value;

        fetch(`/wp-admin/admin-ajax.php?action=panel_cambiar_estado`, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `order_id=${orderId}&estado=${estado}`,
        })
          .then((r) => r.json())
          .then((res) => {
            if (!res.success) {
              alert("Error: " + res.data);
              return;
            }

            alert("Estado actualizado correctamente");
            location.reload();
          });
      });
  });
});
