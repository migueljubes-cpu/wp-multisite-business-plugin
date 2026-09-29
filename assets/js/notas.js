document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("panel-modal");
  const modalBody = document.getElementById("panel-modal-body");
  const modalClose = document.getElementById("panel-modal-close");
  const notesBtn = document.querySelector(".punk-notes-btn");

  // ABRIR BLOC DE NOTAS
  function abrirBlocNotas() {
    fetch(punkNotas.ajaxurl + "?action=punksetter_notas_template")
      .then((r) => r.text())
      .then((html) => {
        modalBody.innerHTML = html;

        // Abrir modal REALMENTE
        modal.classList.add("open");
        modal.style.display = "block";

        // Esperar a que el HTML esté cargado
        setTimeout(() => iniciarNotas(), 50);
      });
  }

  // CARGAR NOTAS
  function cargarNotas() {
    fetch(punkNotas.ajaxurl + "?action=punksetter_get_notas")
      .then((r) => r.json())
      .then((res) => {
        if (!res || !res.data) return;

        const lista = document.getElementById("notas-lista");
        if (!lista) return;

        lista.innerHTML = "";

        res.data.forEach((nota) => {
          lista.innerHTML += `
                        <div class="nota-item">
                            <p>${nota.texto}</p>
                            <small>${nota.fecha}</small>
                            <button class="nota-borrar" data-id="${nota.id}">Borrar</button>
                        </div>
                    `;
        });

        // Mostrar la última nota en el textarea
        const textarea = document.getElementById("nota-texto");
        if (textarea && res.data.length > 0) {
          textarea.value = res.data[0].texto;
        }
      });
  }

  // GUARDAR NOTA
  function guardarNota() {
    const textarea = document.getElementById("nota-texto");
    if (!textarea) return;

    const texto = textarea.value.trim();
    if (!texto) return;

    const formData = new FormData();
    formData.append("action", "punksetter_guardar_nota");
    formData.append("texto", texto);

    fetch(punkNotas.ajaxurl, { method: "POST", body: formData }).then(() =>
      cargarNotas(),
    );
  }

  // BORRAR NOTA
  function borrarNota(id) {
    const formData = new FormData();
    formData.append("action", "punksetter_borrar_nota");
    formData.append("id", id);

    fetch(punkNotas.ajaxurl, { method: "POST", body: formData }).then(() =>
      cargarNotas(),
    );
  }

  // INICIAR EVENTOS
  function iniciarNotas() {
    cargarNotas();

    const guardarBtn = document.getElementById("nota-guardar");
    if (guardarBtn) {
      guardarBtn.addEventListener("click", guardarNota);
    }

    const lista = document.getElementById("notas-lista");
    if (lista) {
      lista.addEventListener("click", (e) => {
        if (e.target.classList.contains("nota-borrar")) {
          borrarNota(e.target.dataset.id);
        }
      });
    }
  }

  // EVENTO BOTÓN LIBRETA
  notesBtn.addEventListener("click", abrirBlocNotas);

  // CERRAR MODAL
  modalClose.addEventListener("click", () => {
    // Cerrar modal REALMENTE
    modal.classList.remove("open");
    modal.style.display = "none";

    // Limpiar contenido
    modalBody.innerHTML = "";

    // Resetear eventos para permitir abrir por segunda vez
    const guardarBtn = document.getElementById("nota-guardar");
    if (guardarBtn) {
      guardarBtn.replaceWith(guardarBtn.cloneNode(true));
    }

    const lista = document.getElementById("notas-lista");
    if (lista) {
      lista.replaceWith(lista.cloneNode(true));
    }

    // Restaurar display para próxima apertura
    setTimeout(() => {
      modal.style.display = "";
    }, 50);
  });
});
