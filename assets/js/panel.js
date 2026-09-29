// assets/js/panel.js
(function () {
  "use strict";

  /* ---------------------------------------------------------
       ⭐ BLOQUE CRÍTICO:
       Evitar que panel.js interfiera con el modal de notas
    --------------------------------------------------------- */
  document.addEventListener("click", function (e) {
    const modal = document.getElementById("panel-modal");

    // Si no existe el modal → salir
    if (!modal) return;

    // Si el modal está abierto
    if (modal.classList.contains("open")) {
      // Si el clic fue dentro del modal → permitir
      if (modal.contains(e.target)) return;

      // Si el clic fue en la X → permitir
      if (e.target.id === "panel-modal-close") return;

      // ⭐ Si el clic fue fuera → NO permitir que panel.js actúe
      e.stopPropagation();
      e.preventDefault();
      return;
    }
  });

  /* ---------------------------------------------------------
       SISTEMA DE PESTAÑAS DEL PANEL
    --------------------------------------------------------- */

  var tabButtons = document.querySelectorAll(".panel-tab-btn");
  var panels = document.querySelectorAll(".panel-content");

  if (!tabButtons.length || !panels.length) return;

  function deactivateAll() {
    tabButtons.forEach(function (btn) {
      btn.classList.remove("active");
      btn.setAttribute("aria-selected", "false");
    });
    panels.forEach(function (p) {
      p.classList.remove("active-content");
      p.setAttribute("hidden", "true");
    });
  }

  function activate(tabName) {
    var btn = document.querySelector(
      '.panel-tab-btn[data-tab="' + tabName + '"]',
    );
    var panel = document.getElementById("tab-" + tabName);
    if (!btn || !panel) return;

    deactivateAll();

    btn.classList.add("active");
    btn.setAttribute("aria-selected", "true");

    panel.classList.add("active-content");
    panel.removeAttribute("hidden");

    // Accesibilidad: mover foco al panel
    panel.setAttribute("tabindex", "-1");
    panel.focus();
  }

  tabButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var tab = btn.getAttribute("data-tab");
      activate(tab);
    });

    btn.addEventListener("keydown", function (e) {
      var idx = Array.prototype.indexOf.call(tabButtons, btn);

      if (e.key === "ArrowRight") {
        tabButtons[(idx + 1) % tabButtons.length].focus();
      } else if (e.key === "ArrowLeft") {
        tabButtons[(idx - 1 + tabButtons.length) % tabButtons.length].focus();
      } else if (e.key === "Home") {
        tabButtons[0].focus();
      } else if (e.key === "End") {
        tabButtons[tabButtons.length - 1].focus();
      } else if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        var tab = btn.getAttribute("data-tab");
        activate(tab);
      }
    });
  });

  // Inicializar: ocultar paneles no activos
  panels.forEach(function (p) {
    if (!p.classList.contains("active-content")) {
      p.setAttribute("hidden", "true");
    } else {
      p.removeAttribute("hidden");
    }
  });
})();
