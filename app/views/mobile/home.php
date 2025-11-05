<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title ?? 'Expediatravels'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            color-scheme: light dark;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(160deg, #0ea5e9 0%, #0369a1 45%, #0f172a 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }
        .app-shell {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border-radius: 28px;
            padding: 28px 22px 34px;
            margin: 24px 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.35);
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .brand {
            display: flex;
            flex-direction: column;
        }
        .brand span:first-child {
            font-size: 0.95rem;
            color: #0369a1;
            font-weight: 600;
        }
        .brand span:last-child {
            font-size: 1.45rem;
            color: #0f172a;
            font-weight: 700;
        }
        .notif {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            background: linear-gradient(145deg, rgba(14, 165, 233, 0.15), rgba(3, 105, 161, 0.15));
            display: grid;
            place-items: center;
            color: #0f172a;
            font-weight: 600;
            font-size: 1.1rem;
        }
        .search {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 18px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.18);
        }
        .search input {
            border: none;
            flex: 1;
            font-size: 1rem;
            background: transparent;
            color: #0f172a;
            outline: none;
        }
        .chip-list {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 4px;
        }
        .chip {
            padding: 10px 16px;
            border-radius: 20px;
            background: rgba(14, 165, 233, 0.12);
            color: #0369a1;
            font-size: 0.95rem;
            font-weight: 500;
            white-space: nowrap;
        }
        section h2 {
            font-size: 1.2rem;
            margin: 0 0 12px;
            color: #0f172a;
        }
        .destination-grid {
            display: grid;
            gap: 16px;
        }
        .destination-card {
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            min-height: 140px;
            background: #0f172a;
            color: #fff;
            box-shadow: 0 15px 30px rgba(15, 23, 42, 0.3);
        }
        .destination-card img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.75);
        }
        .destination-card .content {
            position: relative;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            height: 100%;
            gap: 6px;
        }
        .destination-card h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }
        .destination-card p {
            margin: 0;
            font-size: 0.95rem;
            opacity: 0.85;
        }
        .upcoming {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .tour-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-radius: 18px;
            background: rgba(14, 165, 233, 0.1);
            box-shadow: inset 0 0 0 1px rgba(14, 165, 233, 0.14);
        }
        .tour-card strong {
            display: block;
            font-size: 1.05rem;
            color: #0f172a;
        }
        .tour-card span {
            font-size: 0.9rem;
            color: rgba(15, 23, 42, 0.7);
        }
        .tour-tag {
            border-radius: 999px;
            padding: 6px 14px;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #0ea5e9;
        }
        .nav-bar {
            margin-top: 8px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            background: rgba(255, 255, 255, 0.75);
            padding: 12px 16px;
            border-radius: 24px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.15);
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: rgba(15, 23, 42, 0.65);
            text-decoration: none;
        }
        .nav-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.22), rgba(3, 105, 161, 0.22));
            display: grid;
            place-items: center;
            font-weight: 600;
            color: #0f172a;
        }
        .cta {
            display: block;
            margin-top: 6px;
            text-align: center;
            background: linear-gradient(135deg, #f97316, #fb923c);
            color: #fff;
            padding: 14px;
            border-radius: 18px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(249, 115, 22, 0.35);
        }
        .cta span {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            opacity: 0.85;
        }
        footer {
            text-align: center;
            font-size: 0.75rem;
            color: rgba(15, 23, 42, 0.55);
        }
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(160deg, #020617 0%, #1e293b 55%, #0f172a 100%);
            }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <header>
            <div class="brand">
                <span>Oxapampa · Selva Central</span>
                <span>Expediatravels</span>
            </div>
            <div class="notif">✈️</div>
        </header>

        <div class="search">
            <span>🔍</span>
            <input type="search" placeholder="Buscar experiencias" aria-label="Buscar experiencias" />
        </div>

        <div class="chip-list" role="tablist" aria-label="Filtros rápidos">
            <div class="chip">Escapadas 3D/2N</div>
            <div class="chip">Tours familiares</div>
            <div class="chip">Aventura</div>
            <div class="chip">Gastronomía</div>
        </div>

        <section aria-labelledby="destinos">
            <h2 id="destinos">Destinos destacados</h2>
            <div class="destination-grid">
                <?php foreach (($destinations ?? []) as $destination): ?>
                    <article class="destination-card" style="<?= !empty($destination['background']) ? 'background:' . htmlspecialchars($destination['background']) . ';' : '' ?>">
                        <?php if (!empty($destination['image'])): ?>
                            <img src="<?= htmlspecialchars($destination['image']); ?>" alt="<?= htmlspecialchars($destination['name']); ?>" loading="lazy" />
                        <?php endif; ?>
                        <div class="content">
                            <h3><?= htmlspecialchars($destination['name']); ?></h3>
                            <p><?= htmlspecialchars($destination['description']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section aria-labelledby="proximos">
            <h2 id="proximos">Próximos tours</h2>
            <div class="upcoming">
                <?php foreach (($upcomingTours ?? []) as $tour): ?>
                    <article class="tour-card">
                        <div>
                            <strong><?= htmlspecialchars($tour['title']); ?></strong>
                            <span><?= htmlspecialchars($tour['date']); ?></span>
                        </div>
                        <span class="tour-tag" style="<?= !empty($tour['color']) ? 'background:' . htmlspecialchars($tour['color']) . ';' : '' ?>">
                            <?= htmlspecialchars($tour['tag']); ?>
                        </span>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <a class="cta" href="/web/">
            Continuar en versión web
            <span>Accede al sitio completo para escritorio</span>
        </a>

        <nav class="nav-bar" aria-label="Menú principal">
            <a class="nav-item" href="#">
                <div class="nav-icon">🏠</div>
                Inicio
            </a>
            <a class="nav-item" href="#">
                <div class="nav-icon">🧭</div>
                Explorar
            </a>
            <a class="nav-item" href="#">
                <div class="nav-icon">🗓️</div>
                Reservas
            </a>
            <a class="nav-item" href="#">
                <div class="nav-icon">👤</div>
                Perfil
            </a>
        </nav>

        <footer>
            Expedia Travel · Inspirado en apps iOS & Android
        </footer>
    </div>
</body>
</html>
