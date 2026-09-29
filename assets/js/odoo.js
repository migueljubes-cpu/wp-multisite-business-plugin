(function () {
  function qs(s, c) {
    return (c || document).querySelector(s);
  }
  function qsa(s, c) {
    return Array.prototype.slice.call((c || document).querySelectorAll(s));
  }

  var wrap = qs("[data-ps-odoo]");
  if (!wrap) return;

  var mainTabs = qsa(".ps-odoo-main-tab", wrap);
  var subtabsVentas = qs(".ps-odoo-subtabs--ventas", wrap);
  var subtabsCompras = qs(".ps-odoo-subtabs--compras", wrap);
  var subtabsFacturas = qs(".ps-odoo-subtabs--facturas", wrap);
  var subtabs = qsa(".ps-odoo-tab", wrap);
  var panels = qsa(".ps-odoo-panel", wrap);

  function activateMain(name) {
    mainTabs.forEach(function (b) {
      b.classList.toggle("ps-odoo-main-tab--active", b.dataset.main === name);
    });

    subtabsVentas.classList.remove("ps-odoo-subtabs--active");
    subtabsCompras.classList.remove("ps-odoo-subtabs--active");
    subtabsFacturas.classList.remove("ps-odoo-subtabs--active");

    if (name === "ventas") {
      subtabsVentas.classList.add("ps-odoo-subtabs--active");
      activateSub("ventas_presupuestos");
    } else if (name === "compras") {
      subtabsCompras.classList.add("ps-odoo-subtabs--active");
      activateSub("compras_pedidos");
    } else {
      subtabsFacturas.classList.add("ps-odoo-subtabs--active");
      activateSub("facturas_ventas");
    }
  }

  function activateSub(name) {
    subtabs.forEach(function (t) {
      t.classList.toggle("ps-odoo-tab--active", t.dataset.psTab === name);
    });
    panels.forEach(function (p) {
      p.classList.toggle(
        "ps-odoo-panel--active",
        p.id === "ps-odoo-panel-" + name,
      );
    });
  }

  mainTabs.forEach(function (btn) {
    btn.addEventListener("click", function () {
      activateMain(btn.dataset.main);
    });
  });

  subtabs.forEach(function (btn) {
    btn.addEventListener("click", function () {
      activateSub(btn.dataset.psTab);
    });
  });

  // Modal documento
  var modal = qs("#ps-odoo-doc-modal");
  var modalBody = qs(".ps-odoo-modal-body", modal);
  var modalClose = qs(".ps-odoo-modal-close", modal);

  modalClose.addEventListener("click", function () {
    modal.style.display = "none";
  });

  qsa(".ps-odoo-view-doc", wrap).forEach(function (btn) {
    btn.addEventListener("click", function () {
      var tipo = btn.dataset.tipo || "";
      var numero = btn.dataset.numero || "";
      var id = btn.dataset.id || "";
      var cliente = btn.dataset.cliente || "";
      var total = btn.dataset.total || "";
      var fecha = btn.dataset.fecha || "";
      var vencimiento = btn.dataset.vencimiento || "";
      var untaxed = btn.dataset.untaxed || "";
      var tax = btn.dataset.tax || "";
      var origen = btn.dataset.origen || "";
      var notas = btn.dataset.notas || "";

      var titulo = "Documento";
      if (tipo === "presupuesto") titulo = "Presupuesto";
      else if (tipo === "factura_venta") titulo = "Factura de venta";
      else if (tipo === "factura_proveedor") titulo = "Factura de proveedor";
      else if (tipo === "factura_gasto") titulo = "Factura de gasto";

      var html = "";
      html += "<p><strong>Tipo:</strong> " + titulo + "</p>";
      html += "<p><strong>Número:</strong> " + numero + "</p>";
      html += "<p><strong>ID interno:</strong> " + id + "</p>";
      html += "<p><strong>Cliente / Proveedor:</strong> " + cliente + "</p>";
      if (fecha) html += "<p><strong>Fecha:</strong> " + fecha + "</p>";
      if (vencimiento)
        html += "<p><strong>Vencimiento:</strong> " + vencimiento + "</p>";
      if (origen) html += "<p><strong>Origen:</strong> " + origen + "</p>";
      html += "<p><strong>Total:</strong> " + total + "</p>";
      if (untaxed || tax) {
        html += "<h4>Totales</h4>";
        html += '<table class="ps-table"><tbody>';
        if (untaxed)
          html += "<tr><td>Importe base</td><td>" + untaxed + "</td></tr>";
        if (tax) html += "<tr><td>Impuestos</td><td>" + tax + "</td></tr>";
        html += "<tr><td>Total</td><td>" + total + "</td></tr>";
        html += "</tbody></table>";
      }
      if (notas) {
        html += "<h4>Notas internas</h4>";
        html += "<p>" + notas + "</p>";
      }

      modalBody.innerHTML = html;
      modal.style.display = "flex";
    });
  });

  // Inicial: Ventas / Presupuestos
  activateMain("ventas");
})();
