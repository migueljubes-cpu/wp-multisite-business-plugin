<?php
/* ============================================
   FLOC — PANEL FLOTANTE (FASE 1)
   Estático + Voz + IA + Enter
   ============================================ */
?>

<!-- Avatar de FLOC -->
<img id="floc-avatar"
     src="<?php echo plugins_url('floc/floc-panel.png', dirname(__FILE__)); ?>"
     alt="FLOC"
     style="cursor:pointer; position:fixed; bottom:20px; right:20px; width:80px; z-index:9999;">

<!-- Panel flotante -->
<div id="floc-panel" style="
    display:none;
    position:fixed;
    bottom:120px;
    right:20px;
    width:300px;
    background:#ffffff;
    border:1px solid #ddd;
    border-radius:12px;
    padding:15px;
    box-shadow:0 4px 12px rgba(0,0,0,0.15);
    z-index:9999;
">

    <h3 style="margin-top:0; font-size:18px; font-weight:600;">FLOC IA</h3>

    <!-- Área de texto -->
    <textarea id="floc-text"
              placeholder="Escribe algo para FLOC..."
              style="
                width:100%;
                height:120px;
                padding:10px;
                border-radius:8px;
                border:1px solid #ccc;
                resize:none;
                font-size:14px;
              "></textarea>

    <!-- Estado / Loader -->
    <div id="floc-status"
         style="display:none; margin-top:8px; font-size:13px; color:#0073aa;">
         Procesando IA...
    </div>

    <!-- Botones -->
    <div style="margin-top:10px; display:flex; gap:10px;">

        <button id="floc-speak" style="
            flex:1;
            padding:10px;
            background:#f0f0f0;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:14px;
        ">
            🔊 Hablar
        </button>

        <button id="floc-send" style="
            flex:1;
            padding:10px;
            background:#0073aa;
            color:#fff;
            border:none;
            border-radius:8px;
            cursor:pointer;
            font-size:14px;
        ">
            Enviar
        </button>

    </div>

</div>
