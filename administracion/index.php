<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Expediatravels · Panel de Control</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root{
      /* Paleta plomos claros */
      --bg:#f4f6f9;           /* gris muy claro de fondo */
      --card:#ffffff;         /* tarjetas blancas */
      --card-2:#f0f2f5;       /* bloques secundarios */
      --text:#1f2937;         /* gris-900 para texto */
      --muted:#6b7280;        /* gris-500 para texto secundario */
      --brand:#64748b;        /* slate-500 sutil */
      --brand-2:#94a3b8;      /* slate-400 */
      --ok:#16a34a;           /* verde */
      --warn:#d97706;         /* ámbar */
      --danger:#dc2626;       /* rojo */
      --border:rgba(2,6,23,.08);
      --shadow:0 10px 30px rgba(2,6,23,.06);
      --radius:16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; font-family:Inter,system-ui,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
      color:var(--text); background:linear-gradient(180deg,#f7f8fb 0%, #eef1f5 60%, #f7f8fb 100%);
    }
    .app{display:grid; grid-template-columns: 260px 1fr; min-height:100vh}
    .sidebar{
      position:sticky; top:0; height:100vh; overflow:auto;
      background: linear-gradient(180deg, #ffffff 0%, #f4f6f9 100%);
      border-right:1px solid var(--border);
      padding:20px 16px;
    }
    .brand{display:flex; align-items:center; gap:10px; padding:8px 10px; margin-bottom:12px}
    .brand .logo{width:36px; height:36px; display:grid; place-items:center; border-radius:12px; background:linear-gradient(135deg,var(--brand-2),var(--brand)); box-shadow: var(--shadow); color:white; font-weight:700}
    .brand b{letter-spacing:.3px}
    .search{position:relative; margin:12px 8px 18px}
    .search input{
      width:100%; padding:10px 34px; border-radius:999px; border:1px solid var(--border);
      background:#ffffff; color:var(--text); outline: none;
    }
    .search svg{position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.6}

    .nav{display:flex; flex-direction:column; gap:6px; padding:0 6px}
    .nav a{
      display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:12px; text-decoration:none; color:var(--text);
      border:1px solid transparent; transition:.16s ease; opacity:.95
    }
    .nav a:hover{background:#eef1f5; border-color:var(--border)}
    .nav a.active{background:linear-gradient(90deg, #eef1f5, #f8fafc); border-color:var(--border)}
    .section{margin-top:18px; margin-bottom:6px; padding:0 10px; color:var(--muted); font-size:.80rem; letter-spacing:.4px}

    .main{min-width:0;}
    .topbar{
      position:sticky; top:0; z-index:5;
      background: rgba(255,255,255,.75); backdrop-filter: blur(8px);
      border-bottom:1px solid var(--border);
    }
    .topbar-inner{display:flex; align-items:center; justify-content:space-between; padding:14px 18px}
    .left{display:flex; align-items:center; gap:10px}
    .hamburger{display:none; background:#ffffff; border:1px solid var(--border); border-radius:10px; padding:8px; cursor:pointer}
    .badge{padding:6px 10px; border:1px solid var(--border); border-radius:999px; font-size:.82rem; color:var(--muted); background:#ffffff}
    .right{display:flex; align-items:center; gap:12px}
    .pill{display:flex; align-items:center; gap:8px; padding:8px 12px; border:1px solid var(--border); border-radius:999px; background:#ffffff}
    .user{display:flex; align-items:center; gap:10px}
    .avatar{width:36px; height:36px; border-radius:50%; display:grid; place-items:center; background:linear-gradient(135deg, #e5e7eb, #d1d5db); border:1px solid var(--border); font-weight:600; color:#374151}

    .content{padding:22px 22px 34px;}
    .grid{display:grid; grid-template-columns: repeat(12, 1fr); gap:18px}
    .card{grid-column: span 3; background: var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:16px; box-shadow: var(--shadow)}
    .card h4{margin:0 0 6px; font-size:.95rem; color:var(--muted)}
    .metric{font-size:1.8rem; font-weight:700}
    .trend{font-size:.85rem; color:var(--muted)}

    .wide{grid-column: span 8}
    .tall{grid-column: span 4}
    .panel{background: var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:18px; box-shadow: var(--shadow)}
    .panel h3{margin:0 0 12px}

    .table{width:100%; border-collapse: collapse}
    .table th,.table td{border-bottom:1px solid var(--border); padding:12px 8px; text-align:left; font-size:.92rem}
    .status{display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:.8rem; border:1px solid var(--border)}
    .ok{background:rgba(22,163,74,.08); color:#166534}
    .warn{background:rgba(217,119,6,.10); color:#92400e}
    .danger{background:rgba(220,38,38,.10); color:#7f1d1d}

    .quick{padding:12px 10px; border-radius:14px; border:1px solid var(--border); background:linear-gradient(180deg, #f3f4f6, #e5e7eb); color:var(--text); cursor:pointer}
    .quick:hover{filter:brightness(1.02)}

    /* Responsive */
    @media (max-width: 1024px){
      .card{grid-column: span 6}
      .wide{grid-column: span 12}
      .tall{grid-column: span 12}
      .app{grid-template-columns: 1fr}
      .sidebar{position:fixed; left:-100%; width:280px; transition:.25s ease}
      .sidebar.open{left:0}
      .hamburger{display:inline-grid}
      body.menu-open{overflow:hidden}
    }
    @media (max-width:600px){
      .card{grid-column: span 12}
    }
  </style>
</head>
<body>
  <div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar" aria-label="Barra lateral">
      <div class="brand" aria-label="Marca">
        <div class="logo" aria-hidden="true">Ex</div>
        <div>
          <b>Expediatravels</b><br>
          <small style="color:var(--muted)">Panel de Control</small>
        </div>
      </div>

      <div class="search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        <input type="search" placeholder="Buscar…" aria-label="Buscar en el panel">
      </div>

      <div class="section">GESTIÓN</div>
      <nav class="nav" role="navigation">
        <a class="active" href="#"><span>🏠</span> Inicio</a>
        <a href="#"><span>📍</span> Destinos</a>
        <a href="#"><span>🗺️</span> Circuitos</a>
        <a href="#"><span>🎒</span> Paquetes</a>
        <a href="#"><span>🧑‍💼</span> Administradores</a>
      </nav>

      <div class="section">OPERACIÓN</div>
      <nav class="nav">
        <a href="#"><span>📨</span> Reservaciones</a>
        <a href="#"><span>📅</span> Calendario</a>
        <a href="#"><span>📊</span> Reportes</a>
        <a href="#"><span>⚙️</span> Ajustes</a>
        <a href="#"><span>⏻</span> Cerrar sesión</a>
      </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <!-- TOPBAR -->
      <header class="topbar" aria-label="Barra de estado">
        <div class="topbar-inner">
          <div class="left">
            <button id="btnMenu" class="hamburger" aria-label="Abrir menú lateral" aria-controls="sidebar" aria-expanded="false">☰</button>
            <div class="badge" id="today">—</div>
            <div class="badge" id="clock">—</div>
          </div>
          <div class="right">
            <div class="pill" title="Usuarios en línea">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <circle cx="12" cy="7" r="3" stroke="currentColor" stroke-width="1.5"/>
              </svg>
              <span id="online">8</span>
            </div>
            <div class="user">
              <div style="text-align:right">
                <div style="font-weight:600">Admin</div>
                <div style="font-size:.8rem; color:var(--muted)">ID: USR-0001</div>
              </div>
              <div class="avatar" aria-label="Icono de usuario">AV</div>
            </div>
          </div>
        </div>
      </header>

      <!-- CONTENT -->
      <section class="content">
        <div class="grid">
          <div class="card">
            <h4>Reservas de hoy</h4>
            <div class="metric">27</div>
            <div class="trend">+6 confirmadas</div>
          </div>
          <div class="card">
            <h4>Consultas</h4>
            <div class="metric">58</div>
            <div class="trend">12 sin responder</div>
          </div>
          <div class="card">
            <h4>Paquetes activos</h4>
            <div class="metric">34</div>
            <div class="trend">3 nuevos</div>
          </div>
          <div class="card">
            <h4>Salidas próximas</h4>
            <div class="metric">9</div>
            <div class="trend">próx. 07/11</div>
          </div>

          <div class="panel wide">
            <h3>Reservas recientes</h3>
            <table class="table" aria-label="Tabla de reservas recientes">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Cliente</th>
                  <th>Servicio</th>
                  <th>Personas</th>
                  <th>Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>06/11/2025 18:24</td>
                  <td>María Quispe</td>
                  <td>Paquete "Selva Exprés"</td>
                  <td>3</td>
                  <td><span class="status ok">✔ Confirmada</span></td>
                </tr>
                <tr>
                  <td>06/11/2025 17:50</td>
                  <td>Jorge Huamán</td>
                  <td>Destino: Cataratas Bayoz</td>
                  <td>2</td>
                  <td><span class="status warn">⧗ Pendiente pago</span></td>
                </tr>
                <tr>
                  <td>06/11/2025 16:10</td>
                  <td>Lucía Salazar</td>
                  <td>Circuito Café & Aventura</td>
                  <td>5</td>
                  <td><span class="status ok">✔ Confirmada</span></td>
                </tr>
                <tr>
                  <td>06/11/2025 15:34</td>
                  <td>Carlos R.</td>
                  <td>Paquete Familiar Chanchamayo</td>
                  <td>4</td>
                  <td><span class="status danger">⚠ Reprogramar</span></td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="panel tall">
            <h3>Atajos</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px">
              <button class="quick" onclick="alert('Nuevo destino')">📍 Nuevo destino</button>
              <button class="quick" onclick="alert('Nuevo circuito')">🗺️ Nuevo circuito</button>
              <button class="quick" onclick="alert('Nuevo paquete')">🎒 Nuevo paquete</button>
              <button class="quick" onclick="alert('Nueva actividad')">📅 Nueva actividad</button>
              <button class="quick" onclick="alert('Responder reservas')">✉ Responder reservas</button>
              <button class="quick" onclick="alert('Exportar calendario')">📤 Exportar calendario</button>
            </div>
          </div>

          <div class="panel wide">
            <h3>Calendario de actividades</h3>
            <div id="calendar" aria-label="Calendario" style="background:var(--card-2); border:1px solid var(--border); border-radius:12px; padding:12px"></div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Fecha y hora (zona Lima)
    const $clock = document.getElementById('clock');
    const $today = document.getElementById('today');
    function tick(){
      const now = new Date();
      const fDate = new Intl.DateTimeFormat('es-PE', { timeZone:'America/Lima', day:'2-digit', month:'2-digit', year:'numeric' }).format(now);
      const fTime = new Intl.DateTimeFormat('es-PE', { timeZone:'America/Lima', hour:'2-digit', minute:'2-digit', second:'2-digit' }).format(now);
      $today.textContent = fDate;
      $clock.textContent = fTime + ' (Lima)';
    }
    tick();
    setInterval(tick, 1000);

    // Menú móvil
    const btn = document.getElementById('btnMenu');
    const sb  = document.getElementById('sidebar');
    btn?.addEventListener('click', ()=>{
      const opened = sb.classList.toggle('open');
      document.body.classList.toggle('menu-open', opened);
      btn.setAttribute('aria-expanded', opened ? 'true' : 'false');
    });

    // Simular usuarios online
    const online = document.getElementById('online');
    setInterval(()=>{
      const n = 5 + Math.floor(Math.random()*8); // 5..12
      online.textContent = String(n);
    }, 6000);

    // Calendario simple del mes actual
    function renderCalendar(){
      const container = document.getElementById('calendar');
      const now = new Date();
      const y = now.getFullYear();
      const m = now.getMonth();
      const first = new Date(y, m, 1);
      const last  = new Date(y, m+1, 0);
      const weeks = [];
      let current = new Date(first);
      current.setDate(current.getDate() - (current.getDay() || 7) + 1); // empezar lunes
      while(current <= last || current.getDay() !== 1){
        const week = [];
        for(let i=0;i<7;i++){
          week.push(new Date(current));
          current.setDate(current.getDate()+1);
        }
        weeks.push(week);
      }
      const monthName = new Intl.DateTimeFormat('es-PE',{month:'long'}).format(now);
      let html = `<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px">
        <strong style="text-transform:capitalize">${monthName} ${y}</strong>
        <small style="color:var(--muted)">Lun a Dom</small>
      </div>`;
      html += '<div style="display:grid; grid-template-columns:repeat(7,1fr); gap:6px">';
      ['L','M','X','J','V','S','D'].forEach(d=>{
        html += `<div style="text-align:center; color:var(--muted); font-size:.8rem">${d}</div>`;
      });
      weeks.forEach(week=>{
        week.forEach(d=>{
          const inMonth = d.getMonth()===m;
          const isToday = d.toDateString()===now.toDateString();
          html += `<div style="padding:10px; border-radius:10px; border:1px solid var(--border); background:${inMonth?'#fff':'#f3f4f6'}; ${isToday?'outline:2px solid #94a3b8; outline-offset:2px':''}">
            <div style="font-size:.9rem; color:${inMonth?'#111827':'#9ca3af'}">${d.getDate()}</div>
            <div style="margin-top:6px; font-size:.75rem; color:var(--muted)">
              ${Math.random()>0.8?'<span class="status ok">Tour</span>':''}
            </div>
          </div>`;
        })
      })
      html += '</div>';
      container.innerHTML = html;
    }
    renderCalendar();
  </script>
</body>
</html>
