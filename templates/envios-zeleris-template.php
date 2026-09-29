<div class="ps-zeleris-wrapper">

    <h3 class="ps-zeleris-title">Zeleris — Seguimiento de envíos</h3>

    <!-- Selector de modo -->
    <div class="ps-zeleris-mode">
        <button class="ps-zeleris-btn active" data-mode="rapida">Búsqueda rápida</button>
        <button class="ps-zeleris-btn" data-mode="avanzada">Búsqueda avanzada</button>
    </div>

    <!-- ========================= -->
    <!-- BÚSQUEDA RÁPIDA -->
    <!-- ========================= -->
    <div id="ps-zeleris-rapida" class="ps-zeleris-section active">

        <p class="ps-zeleris-desc">
            Introduzca el número de envío, delegación + número de expedición, referencia de expedición,
            o bien utilice la búsqueda avanzada para más opciones.
        </p>

        <div class="ps-zeleris-form">

            <label>Nº Seguimiento</label>
            <input type="text" id="zeleris_num_seguimiento" placeholder="Ej: 123456789">

            <label>Nº Delegación + Nº Expedición</label>
            <div class="ps-zeleris-inline">
                <input type="text" id="zeleris_del1" placeholder="Del.">
                <input type="text" id="zeleris_expedicion" placeholder="Expedición">
            </div>

            <label>Referencia de Expedición</label>
            <input type="text" id="zeleris_ref_expedicion">

            <label>Nº Delegación + Nº Recogida</label>
            <div class="ps-zeleris-inline">
                <input type="text" id="zeleris_del1_recogida" placeholder="Del.">
                <input type="text" id="zeleris_recogida" placeholder="Recogida">
            </div>

            <label>Referencia de Recogida</label>
            <input type="text" id="zeleris_ref_recogida">

            <div class="ps-zeleris-actions">
                <button id="zeleris_rapida_clear" class="ps-btn-clear">Borrar formulario</button>
                <button id="zeleris_rapida_send" class="ps-btn-send">Enviar</button>
            </div>

        </div>
    </div>

    <!-- ========================= -->
    <!-- BÚSQUEDA AVANZADA -->
    <!-- ========================= -->
    <div id="ps-zeleris-avanzada" class="ps-zeleris-section">

        <p class="ps-zeleris-desc">
            Utilice los filtros para localizar envíos por fecha, cliente, provincia, situación o referencia.
        </p>

        <div class="ps-zeleris-form">

            <label>Tráfico</label>
            <select id="zeleris_trafico">
                <option value="salidas">Salidas</option>
                <option value="entradas">Entradas</option>
            </select>

            <label>Nº Seguimiento</label>
            <input type="text" id="zeleris_av_num_seguimiento">

            <label>Número de expedición</label>
            <input type="text" id="zeleris_av_expedicion">

            <label>Referencia</label>
            <input type="text" id="zeleris_av_referencia">

            <label>Cliente</label>
            <select id="zeleris_av_cliente">
                <option value="000803294">000803294</option>
                <option value="000803467">000803467</option>
            </select>

            <label>Fecha desde</label>
            <input type="date" id="zeleris_av_fecha_desde">

            <label>Fecha hasta</label>
            <input type="date" id="zeleris_av_fecha_hasta">

            <label>Situación</label>
            <select id="zeleris_av_situacion">
                <option value="todas">Todas</option>
                <option value="transito">En tránsito</option>
                <option value="entregado">Entregado</option>
                <option value="incidencia">Incidencia</option>
            </select>

            <label>Nombre del destinatario</label>
            <input type="text" id="zeleris_av_destinatario">

            <label>Provincia</label>
            <input type="text" id="zeleris_av_provincia">

            <label>Población del destinatario</label>
            <input type="text" id="zeleris_av_poblacion">

            <label>Ordenar por</label>
            <select id="zeleris_av_orden">
                <option value="fecha">Fecha</option>
                <option value="estado">Estado</option>
                <option value="cliente">Cliente</option>
            </select>

            <div class="ps-zeleris-actions">
                <button id="zeleris_av_clear" class="ps-btn-clear">Borrar formulario</button>
                <button id="zeleris_av_send" class="ps-btn-send">Enviar</button>
            </div>

        </div>
    </div>

    <!-- ========================= -->
    <!-- RESULTADOS -->
    <!-- ========================= -->
    <div id="ps-zeleris-resultados" class="ps-zeleris-resultados">
        <p class="ps-zeleris-info">Realice una búsqueda para ver los resultados.</p>
    </div>

    <!-- ========================= -->
    <!-- MODAL DETALLES -->
    <!-- ========================= -->
    <div id="ps-zeleris-modal" class="ps-modal">
        <div class="ps-modal-content">
            <span class="ps-modal-close">&times;</span>
            <div id="ps-zeleris-detalles"></div>
        </div>
    </div>

</div>
