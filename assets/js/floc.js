/* ============================================
   FLOC — ESTÁTICO (FASE 1)
   Panel flotante + Voz masculina + IA + Enter
   ============================================ */

document.addEventListener("DOMContentLoaded", function () {
  const flocAvatar = document.getElementById("floc-avatar");
  const flocPanel = document.getElementById("floc-panel");
  const flocText = document.getElementById("floc-text");
  const flocSpeakBtn = document.getElementById("floc-speak");
  const flocSendBtn = document.getElementById("floc-send");
  const flocStatus = document.getElementById("floc-status");

  /* -----------------------------
       Mostrar / ocultar panel
       ----------------------------- */
  if (flocAvatar) {
    flocAvatar.addEventListener("click", function () {
      const visible = flocPanel.style.display === "block";
      flocPanel.style.display = visible ? "none" : "block";
    });
  }

  /* -----------------------------
       Enviar con ENTER
       ----------------------------- */
  if (flocText) {
    flocText.addEventListener("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        flocSendBtn.click();
      }
    });
  }

  /* -----------------------------
       Voz — Web Speech API (voz masculina)
       ----------------------------- */
  if (flocSpeakBtn) {
    flocSpeakBtn.addEventListener("click", function () {
      const msg = new SpeechSynthesisUtterance(flocText.value);
      msg.lang = "es-ES";

      const voices = speechSynthesis.getVoices();
      const maleVoice = voices.find(
        (v) =>
          v.lang.includes("es") &&
          (v.name.toLowerCase().includes("male") ||
            v.name.toLowerCase().includes("hombre") ||
            v.name.toLowerCase().includes("man")),
      );

      if (maleVoice) {
        msg.voice = maleVoice;
      }

      speechSynthesis.speak(msg);
    });
  }

  /* -----------------------------
       Enviar texto a Claude IA (AJAX WordPress)
       ----------------------------- */
  if (flocSendBtn) {
    flocSendBtn.addEventListener("click", function () {
      const userText = flocText.value.trim();
      if (userText.length === 0) return;

      // Mostrar loader
      if (flocStatus) {
        flocStatus.style.display = "block";
        flocStatus.textContent = "Procesando IA...";
      }

      flocText.value = "";

      fetch(punkFloc.ajaxurl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "action=floc_ask&prompt=" + encodeURIComponent(userText),
      })
        .then((response) => response.json())
        .then((data) => {
          if (flocStatus) flocStatus.style.display = "none";

          if (data.success) {
            flocText.value = data.reply;
          } else {
            flocText.value = "Error IA: " + data.data;
          }
        })
        .catch((err) => {
          if (flocStatus) flocStatus.style.display = "none";
          flocText.value = "Error de conexión.";
          console.error("FLOC IA error:", err);
        });
    });
  }
});
