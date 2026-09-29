document.addEventListener("DOMContentLoaded", () => {
  // INCIDENTES (Cloudflare Status API)
  fetch("https://www.cloudflarestatus.com/api/v2/incidents.json")
    .then((r) => r.json())
    .then((data) => {
      const container = document.getElementById("cf-incidents");
      container.innerHTML = "";

      data.incidents.slice(0, 10).forEach((incident) => {
        const card = document.createElement("div");
        card.className = "cf-card";

        card.innerHTML = `
                    <div class="cf-card-title">${incident.name}</div>
                    <div class="cf-card-status">Estado: ${incident.status}</div>
                    <div class="cf-card-body">${incident.incident_updates[0]?.body || "Sin descripción"}</div>
                    <div class="cf-card-link">
                        <a href="${incident.shortlink}" target="_blank">Ver más</a>
                    </div>
                `;

        container.appendChild(card);
      });
    });

  // CAJAS PEQUEÑAS (solo texto simple)
  document.getElementById("cf-traffic-box").innerHTML =
    "<h3>Tráfico Global</h3><p>Datos disponibles en Radar</p>";

  document.getElementById("cf-attacks-box").innerHTML =
    "<h3>Ataques Globales</h3><p>Datos disponibles en Radar</p>";

  document.getElementById("cf-latency-box").innerHTML =
    "<h3>Latencia Global</h3><p>Datos disponibles en Radar</p>";
});
