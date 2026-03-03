<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';

if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
  header("Location: ../dashboard.php"); // ajusta si tu dashboard está en otro archivo
  exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registro de Asistencia</title>
  <style>
    body{font-family: Arial, sans-serif; margin:0; background:#f4f6f9;}
    .wrap{max-width: 980px; margin: 30px auto; padding: 16px;}
    .card{background:#fff; border-radius:12px; padding:18px; box-shadow: 0 6px 18px rgba(0,0,0,.08);}
    .grid{display:grid; grid-template-columns: 1.2fr .8fr; gap:16px;}
    label{display:block; margin:8px 0 6px;}
    input{width:100%; padding:10px 12px; border:1px solid #cfd6df; border-radius:10px;}
    button{padding:10px 12px; border:0; border-radius:10px; cursor:pointer;}
    .btn{background:#2563eb; color:#fff;}
    .btn2{background:#111827; color:#fff;}
    .muted{color:#6b7280;}
    .row{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    .pill{display:inline-block; padding:6px 10px; border-radius:999px; background:#eef2ff; color:#1e40af;}
    video{width:100%; border-radius:12px; background:#111;}
    .status{padding:10px; border-radius:10px; background:#f1f5f9; margin-top:10px;}
    .ok{background:#ecfdf5; color:#065f46;}
    .bad{background:#fef2f2; color:#991b1b;}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="row" style="justify-content:space-between;">
        <div>
          <h2 style="margin:0;">Registro de Asistencia</h2>
          <div class="muted">Ingrese DNI y presione “Marcar asistencia”.</div>
        </div>
        <div>
          <a href="../asistencia/admin_login.php" style="text-decoration:none;">
            <button class="btn2">Ingreso como administrador</button>
          </a>
        </div>
      </div>

      <hr style="border:none;border-top:1px solid #e5e7eb; margin:16px 0;">

      <div class="grid">
        <div>
          <label for="dni">DNI (8 dígitos)</label>
          <input id="dni" maxlength="8" placeholder="Ej: 12345678" inputmode="numeric">

          <div style="margin-top:10px;">
            <span class="pill">Nombre:</span>
            <strong id="nombre">—</strong>
          </div>

          <div style="margin-top:10px;">
            <span class="pill">Hora:</span>
            <strong id="hora_pc">—</strong>
          </div>

          <div style="margin-top:14px;" class="row">
            <button class="btn" id="btn_marcar">Marcar asistencia</button>
          </div>

          <div class="status" id="estado">
            Estado: <span class="muted">Esperando DNI…</span>
          </div>

          <!-- Canvas oculto para tomar “foto” sin guardarla -->
          <canvas id="snapshot" style="display:none;"></canvas>
        </div>

        <div>
          <video id="video" autoplay playsinline></video>
          <div class="muted" style="margin-top:8px;">Cámara</div>
        </div>
      </div>
    </div>
  </div>

<script>
  const dniEl = document.getElementById('dni');
  const nombreEl = document.getElementById('nombre');
  const horaPcEl = document.getElementById('hora_pc');
  const estadoEl = document.getElementById('estado');
  const btnMarcar = document.getElementById('btn_marcar');

  const video = document.getElementById('video');
  const canvas = document.getElementById('snapshot');
  const ctx = canvas.getContext('2d');

  let relojInterval = null;

  function setEstado(msg, tipo='') {
    estadoEl.className = 'status ' + (tipo || '');
    estadoEl.innerHTML = 'Estado: ' + msg;
  }

  // Reloj del PC (cliente)
  function iniciarReloj() {
    function tick() {
      const now = new Date();
      horaPcEl.textContent = now.toLocaleString();
    }
    tick();
    relojInterval = setInterval(tick, 1000);
  }

  function pararReloj() {
    if (relojInterval) {
      clearInterval(relojInterval);
      relojInterval = null;
    }
  }

  function reiniciarPantalla() {
    dniEl.value = '';
    nombreEl.textContent = '—';

    // reinicia el reloj (vuelve a correr)
    iniciarReloj();

    setEstado('<span class="muted">Listo. Ingresa otro DNI…</span>');
  }

  function bloquearBotonConCuenta(segundos = 8) {
    let s = segundos;
    btnMarcar.disabled = true;

    const t = setInterval(() => {
      btnMarcar.textContent = `Puedes marcar en ${s}s…`;
      s--;

      if (s < 0) {
        clearInterval(t);
        btnMarcar.textContent = 'Marcar asistencia';
        btnMarcar.disabled = false;
        reiniciarPantalla();
      }
    }, 1000);
  }

  iniciarReloj();

  // Cámara
  async function iniciarCamara() {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
      video.srcObject = stream;
      setEstado('<span class="muted">Cámara lista. Ingrese DNI.</span>');
    } catch (e) {
      setEstado('No se pudo acceder a la cámara. Permite el acceso.', 'bad');
    }
  }
  iniciarCamara();

  // Solo números en DNI
  dniEl.addEventListener('input', () => {
    dniEl.value = dniEl.value.replace(/\D/g, '').slice(0, 8);
  });

  // Capturar “foto” (NO se guarda, solo en canvas)
  function tomarFotoEnMemoria() {
    if (!video.srcObject) throw new Error('Cámara no disponible');

    const w = video.videoWidth;
    const h = video.videoHeight;
    if (!w || !h) throw new Error('Cámara aún no lista (espera 1-2s)');

    canvas.width = w;
    canvas.height = h;
    ctx.drawImage(video, 0, 0, w, h);
    return true;
  }

  // 1 solo flujo: buscar -> foto -> registrar -> parar reloj -> bloquear y reiniciar
  btnMarcar.addEventListener('click', async () => {
    const dni = dniEl.value.trim();
    nombreEl.textContent = '—';

    if (dni.length !== 8) {
      setEstado('El DNI debe tener 8 dígitos.', 'bad');
      return;
    }

    btnMarcar.disabled = true;
    btnMarcar.textContent = 'Procesando…';
    setEstado('<span class="muted">Buscando DNI…</span>');

    try {
      // 1) Buscar DNI
      const res1 = await fetch('./api_personal.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ dni })
      });
      const data1 = await res1.json();

      if (!data1.ok) {
        setEstado(data1.msg || 'No encontrado', 'bad');
        btnMarcar.disabled = false;
        btnMarcar.textContent = 'Marcar asistencia';
        return;
      }

      nombreEl.textContent = data1.nombre;
      setEstado('<span class="muted">DNI OK. Tomando foto…</span>');

      // 2) Tomar foto (sin guardar)
      tomarFotoEnMemoria();
      setEstado('<span class="muted">Foto capturada (no guardada). Registrando asistencia…</span>');

      // 3) Registrar asistencia (hora servidor)
      const res2 = await fetch('./api_registrar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idpers: data1.idpers })
      });
      const data2 = await res2.json();

      if (!data2.ok) {
        setEstado(data2.msg || 'No se pudo registrar', 'bad');
        btnMarcar.disabled = false;
        btnMarcar.textContent = 'Marcar asistencia';
        return;
      }

      // 4) Parar el tiempo (congelar reloj)
      pararReloj();

      setEstado(
        `✅ Asistencia marcada. <b>${data2.accion}</b> — Hora servidor: <b>${data2.hora_servidor}</b><br>
         <span class="muted">Se reiniciará automáticamente…</span>`,
        'ok'
      );

      // 5) Bloqueo con cuenta y reinicio automático
      bloquearBotonConCuenta(8);

    } catch (err) {
      setEstado('Error: ' + (err?.message || 'fallo de conexión'), 'bad');
      btnMarcar.disabled = false;
      btnMarcar.textContent = 'Marcar asistencia';
    }
  });
</script>
</body>
</html>