<?php if (!defined('ABSPATH')) exit; ?>

<div class="cf-wrapper">

    <h2 class="cf-title">Cloudflare</h2>

    <div class="cf-boxes">

        <div class="cf-box" id="cf-traffic-box">
            <h3>Tráfico Global</h3>
            <p class="cf-loading">Cargando…</p>
        </div>

        <div class="cf-box" id="cf-attacks-box">
            <h3>Ataques Globales</h3>
            <p class="cf-loading">Cargando…</p>
        </div>

        <div class="cf-box" id="cf-latency-box">
            <h3>Latencia Global</h3>
            <p class="cf-loading">Cargando…</p>
        </div>

    </div>

    <h3 class="cf-subtitle">Incidentes recientes</h3>

    <div id="cf-incidents" class="cf-incidents"></div>

</div>
