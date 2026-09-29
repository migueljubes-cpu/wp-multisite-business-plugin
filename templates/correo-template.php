<div class="ps-panel-section">

    <h2 class="ps-title">📧 Bandeja de entrada — info@punksetter.com</h2>

    <!-- Botón conectar Gmail -->
    <div class="ps-gmail-actions">
        <button id="ps-connect-gmail" class="ps-btn-primary">
            Conectar Gmail
        </button>

        <button id="ps-disconnect-gmail" class="ps-btn-danger">
            Desconectar Gmail
        </button>
    </div>

    <!-- Contenedor de estado -->
    <div id="ps-gmail-status" class="ps-status-box">
        Conectando con Gmail…
    </div>

    <!-- Tabla de correos -->
    <table class="ps-table" id="ps-gmail-table">
        <thead>
            <tr>
                <th>Remitente</th>
                <th>Asunto</th>
                <th>Fecha</th>
                <th>Ver</th>
            </tr>
        </thead>
        <tbody id="ps-gmail-body">
            <!-- Aquí JS insertará los correos -->
        </tbody>
    </table>

    <!-- Modal para ver correo -->
    <div id="ps-gmail-modal" class="ps-modal">
        <div class="ps-modal-content">
            <span class="ps-modal-close">&times;</span>

            <h3 id="ps-modal-subject">Asunto</h3>
            <p id="ps-modal-from">Remitente</p>
            <p id="ps-modal-date">Fecha</p>

            <div id="ps-modal-body" class="ps-modal-body">
                <!-- Aquí JS insertará el cuerpo del correo -->
            </div>
        </div>
    </div>

</div>
