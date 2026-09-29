document.addEventListener("click", function (e) {
  if (!e.target.classList.contains("marketing-tab")) return;

  let tab = e.target.dataset.tab;
  let box = document.getElementById("marketing-content");

  document
    .querySelectorAll(".marketing-tab")
    .forEach((btn) => btn.classList.remove("active"));
  e.target.classList.add("active");

  /*
    |--------------------------------------------------------------------------
    | GA4 (Dashboard Profesional)
    |--------------------------------------------------------------------------
    */
  if (tab === "analytics") {
    box.innerHTML = `
            <h2 style="color:#ff4b8b;">📈 Analytics GA4</h2>

            <!-- TARJETAS RESUMEN -->
            <div class="ga4-summary">
                <div class="ga4-card" id="sum-visitas">Visitas</div>
                <div class="ga4-card" id="sum-paginas">Páginas</div>
                <div class="ga4-card" id="sum-paises">Países</div>
                <div class="ga4-card" id="sum-eventos">Eventos</div>
                <div class="ga4-card" id="sum-conversiones">Conversiones</div>
            </div>

            <!-- PESTAÑAS INTERNAS -->
            <div class="ga4-tabs">
                <button class="ga4-tab active" data-ga4="visitas">Visitas</button>
                <button class="ga4-tab" data-ga4="paginas">Páginas</button>
                <button class="ga4-tab" data-ga4="paises">Países</button>
                <button class="ga4-tab" data-ga4="tiempo">Tiempo en página</button>
                <button class="ga4-tab" data-ga4="dispositivos">Dispositivos</button>
                <button class="ga4-tab" data-ga4="eventos">Eventos</button>
                <button class="ga4-tab" data-ga4="conversiones">Conversiones</button>
                <button class="ga4-tab" data-ga4="carritos">Carritos</button>
                <button class="ga4-tab" data-ga4="compras">Compras</button>
            </div>

            <!-- CONTENIDO -->
            <div id="ga4-content" class="marketing-card">
                Cargando datos...
            </div>
        `;

    cargarGA4(document.getElementById("ga4-range").value);
  }

  /*
    |--------------------------------------------------------------------------
    | SEARCH CONSOLE (compacto con pestañas internas)
    |--------------------------------------------------------------------------
    */
  if (tab === "seo") {
    box.innerHTML = `
            <h2 style="color:#ff4b8b;">🔍 SEO (Search Console)</h2>

            <div class="seo-tabs">
                <button class="seo-tab active" data-seo="queries">Consultas</button>
                <button class="seo-tab" data-seo="pages">Páginas</button>
                <button class="seo-tab" data-seo="countries">Países</button>
                <button class="seo-tab" data-seo="devices">Dispositivos</button>
            </div>

            <div id="seo-content" class="marketing-card">
                Cargando datos...
            </div>
        `;

    fetch(`/wp-admin/admin-ajax.php?action=punksetter_search_console_all`)
      .then((r) => r.json())
      .then((res) => {
        if (!res.success) {
          document.getElementById("seo-content").innerHTML =
            "Error: " + res.data;
          return;
        }

        let data = res.data;

        function renderSEO(tab) {
          if (tab === "queries") {
            document.getElementById("seo-content").innerHTML = `
                            <h3>Consultas (keywords)</h3>
                            <table class="marketing-table">
                                <thead>
                                    <tr>
                                        <th>Keyword</th>
                                        <th>Clicks</th>
                                        <th>Impresiones</th>
                                        <th>CTR</th>
                                        <th>Posición</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(data.queries?.rows || [])
                                      .map(
                                        (r) => `
                                            <tr>
                                                <td>${r.keys[0]}</td>
                                                <td>${r.clicks}</td>
                                                <td>${r.impressions}</td>
                                                <td>${(r.ctr * 100).toFixed(2)}%</td>
                                                <td>${r.position.toFixed(1)}</td>
                                            </tr>
                                        `,
                                      )
                                      .join("")}
                                </tbody>
                            </table>
                        `;
          }

          if (tab === "pages") {
            document.getElementById("seo-content").innerHTML = `
                            <h3>Páginas</h3>
                            <table class="marketing-table">
                                <thead>
                                    <tr>
                                        <th>URL</th>
                                        <th>Clicks</th>
                                        <th>Impresiones</th>
                                        <th>CTR</th>
                                        <th>Posición</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(data.pages?.rows || [])
                                      .map(
                                        (r) => `
                                            <tr>
                                                <td>${r.keys[0]}</td>
                                                <td>${r.clicks}</td>
                                                <td>${r.impressions}</td>
                                                <td>${(r.ctr * 100).toFixed(2)}%</td>
                                                <td>${r.position.toFixed(1)}</td>
                                            </tr>
                                        `,
                                      )
                                      .join("")}
                                </tbody>
                            </table>
                        `;
          }

          if (tab === "countries") {
            document.getElementById("seo-content").innerHTML = `
                            <h3>Países</h3>
                            <table class="marketing-table">
                                <thead>
                                    <tr>
                                        <th>País</th>
                                        <th>Clicks</th>
                                        <th>Impresiones</th>
                                        <th>CTR</th>
                                        <th>Posición</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(data.countries?.rows || [])
                                      .map(
                                        (r) => `
                                            <tr>
                                                <td>${r.keys[0]}</td>
                                                <td>${r.clicks}</td>
                                                <td>${r.impressions}</td>
                                                <td>${(r.ctr * 100).toFixed(2)}%</td>
                                                <td>${r.position.toFixed(1)}</td>
                                            </tr>
                                        `,
                                      )
                                      .join("")}
                                </tbody>
                            </table>
                        `;
          }

          if (tab === "devices") {
            document.getElementById("seo-content").innerHTML = `
                            <h3>Dispositivos</h3>
                            <table class="marketing-table">
                                <thead>
                                    <tr>
                                        <th>Dispositivo</th>
                                        <th>Clicks</th>
                                        <th>Impresiones</th>
                                        <th>CTR</th>
                                        <th>Posición</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(data.devices?.rows || [])
                                      .map(
                                        (r) => `
                                            <tr>
                                                <td>${r.keys[0]}</td>
                                                <td>${r.clicks}</td>
                                                <td>${r.impressions}</td>
                                                <td>${(r.ctr * 100).toFixed(2)}%</td>
                                                <td>${r.position.toFixed(1)}</td>
                                            </tr>
                                        `,
                                      )
                                      .join("")}
                                </tbody>
                            </table>
                        `;
          }
        }

        document.querySelectorAll(".seo-tab").forEach((btn) => {
          btn.addEventListener("click", () => {
            document
              .querySelectorAll(".seo-tab")
              .forEach((b) => b.classList.remove("active"));
            btn.classList.add("active");
            renderSEO(btn.dataset.seo);
          });
        });

        renderSEO("queries");
      });
  }
}); // FIN DEL LISTENER PRINCIPAL

/*
|--------------------------------------------------------------------------
| GA4 COMPLETO (Dashboard Profesional)
|--------------------------------------------------------------------------
*/
function cargarGA4(rango) {
  fetch(`/wp-admin/admin-ajax.php?action=punksetter_ga4_all&range=${rango}`)
    .then((r) => r.json())
    .then((res) => {
      if (!res.success) {
        document.getElementById("ga4-content").innerHTML = "Error: " + res.data;
        return;
      }

      let data = res.data;

      /*
            |--------------------------------------------------------------------------
            | TARJETAS RESUMEN
            |--------------------------------------------------------------------------
            */
      document.getElementById("sum-visitas").innerHTML =
        `Visitas<br><strong>${data.visitas?.rows?.reduce((a, b) => a + parseInt(b.metricValues[0].value), 0)}</strong>`;

      document.getElementById("sum-paginas").innerHTML =
        `Páginas<br><strong>${data.paginas?.rows?.length}</strong>`;

      document.getElementById("sum-paises").innerHTML =
        `Países<br><strong>${data.paises?.rows?.length}</strong>`;

      document.getElementById("sum-eventos").innerHTML =
        `Eventos<br><strong>${data.eventos?.rows?.length}</strong>`;

      document.getElementById("sum-conversiones").innerHTML =
        `Conversiones<br><strong>${data.conversiones?.rows?.length}</strong>`;

      /*
            |--------------------------------------------------------------------------
            | RENDERIZAR PESTAÑAS
            |--------------------------------------------------------------------------
            */
      function renderGA4(tab) {
        if (tab === "visitas") {
          let rows = data.visitas.rows;

          let fechas = rows.map((r) =>
            formatearFechaGA4(r.dimensionValues[0].value),
          );
          let visitas = rows.map((r) => parseInt(r.metricValues[0].value));

          document.getElementById("ga4-content").innerHTML =
            `<canvas id="ga4_visitas_big"></canvas>`;

          new Chart(document.getElementById("ga4_visitas_big"), {
            type: "line",
            data: {
              labels: fechas,
              datasets: [
                {
                  label: "Visitas",
                  data: visitas,
                  borderColor: "#ff4b8b",
                  backgroundColor: "rgba(255,75,139,0.3)",
                  borderWidth: 3,
                  tension: 0.4,
                },
              ],
            },
          });
        }

        if (tab === "paginas") {
          let rows = data.paginas.rows;

          let html = `
                        <h3>Páginas más vistas</h3>
                        <table class="ga4-table">
                            <thead>
                                <tr>
                                    <th>Página</th>
                                    <th>Vistas</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows
                                  .map(
                                    (r) => `
                                        <tr>
                                            <td>${r.dimensionValues[0].value}</td>
                                            <td>${r.metricValues[0].value}</td>
                                        </tr>
                                    `,
                                  )
                                  .join("")}
                            </tbody>
                        </table>
                    `;

          document.getElementById("ga4-content").innerHTML = html;
        }

        if (tab === "paises") {
          let rows = data.paises.rows;

          let paises = rows.map((r) => r.dimensionValues[0].value);
          let visitas = rows.map((r) => parseInt(r.metricValues[0].value));

          document.getElementById("ga4-content").innerHTML =
            `<canvas id="ga4_paises_big"></canvas>`;

          new Chart(document.getElementById("ga4_paises_big"), {
            type: "bar",
            data: {
              labels: paises,
              datasets: [
                {
                  label: "Visitas",
                  data: visitas,
                  backgroundColor: "#4bc0c0",
                  borderColor: "#009999",
                  borderWidth: 1,
                },
              ],
            },
            options: {
              indexAxis: "y",
            },
          });
        }

        if (tab === "tiempo") {
          let rows = data.tiempo.rows;

          let html = `
                        <h3>Tiempo en página</h3>
                        <table class="ga4-table">
                            <thead>
                                <tr>
                                    <th>Página</th>
                                    <th>Segundos</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows
                                  .map(
                                    (r) => `
                                        <tr>
                                            <td>${r.dimensionValues[0].value}</td>
                                            <td>${r.metricValues[0].value}</td>
                                        </tr>
                                    `,
                                  )
                                  .join("")}
                            </tbody>
                        </table>
                    `;

          document.getElementById("ga4-content").innerHTML = html;
        }

        if (tab === "carritos") {
          let rows = data.carritos.rows;

          let fechas = rows.map((r) =>
            formatearFechaGA4(r.dimensionValues[0].value),
          );
          let count = rows.map((r) => parseInt(r.metricValues[0].value));

          document.getElementById("ga4-content").innerHTML =
            `<canvas id="ga4_carritos_big"></canvas>`;

          new Chart(document.getElementById("ga4_carritos_big"), {
            type: "line",
            data: {
              labels: fechas,
              datasets: [
                {
                  label: "Carritos",
                  data: count,
                  borderColor: "#ff9f40",
                  backgroundColor: "rgba(255,159,64,0.3)",
                  borderWidth: 3,
                  tension: 0.4,
                },
              ],
            },
          });
        }

        if (tab === "compras") {
          let rows = data.compras.rows;

          let fechas = rows.map((r) =>
            formatearFechaGA4(r.dimensionValues[0].value),
          );
          let count = rows.map((r) => parseInt(r.metricValues[0].value));

          document.getElementById("ga4-content").innerHTML =
            `<canvas id="ga4_compras_big"></canvas>`;

          new Chart(document.getElementById("ga4_compras_big"), {
            type: "line",
            data: {
              labels: fechas,
              datasets: [
                {
                  label: "Compras",
                  data: count,
                  borderColor: "#9966ff",
                  backgroundColor: "rgba(153,102,255,0.3)",
                  borderWidth: 3,
                  tension: 0.4,
                },
              ],
            },
          });
        }
      }

      document.querySelectorAll(".ga4-tab").forEach((btn) => {
        btn.addEventListener("click", () => {
          document
            .querySelectorAll(".ga4-tab")
            .forEach((b) => b.classList.remove("active"));
          btn.classList.add("active");
          renderGA4(btn.dataset.ga4);
        });
      });

      renderGA4("visitas");
    });
}

/*
|--------------------------------------------------------------------------
| CONVERTIR FECHA GA4 (20260805 → 05/08/2026)
|--------------------------------------------------------------------------
*/
function formatearFechaGA4(fecha) {
  return fecha.slice(6, 8) + "/" + fecha.slice(4, 6) + "/" + fecha.slice(0, 4);
}

// AUTO-CARGAR GA4 AL ENTRAR AL PANEL
document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    const btn = document.querySelector('.marketing-tab[data-tab="analytics"]');
    if (btn) btn.click();
  }, 50);
});
