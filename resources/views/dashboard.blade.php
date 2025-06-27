<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Trustgate</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: sans-serif;
      background: radial-gradient(circle at -20% 30%, rgba(255, 213, 0, 0.1), transparent 60%) #000;
      color: #fff;
      display: flex;
      min-height: 100vh;
    }

    aside {
      width: 240px;
      background-color: #121212;
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
    }

    .logo {
      font-size: 24px;
      font-weight: bold;
      color: #facc15;
      margin-bottom: 40px;
    }

    nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px;
      color: #ccc;
      text-decoration: none;
      margin-bottom: 10px;
      border-radius: 8px;
      transition: background 0.2s, color 0.2s;
    }

    nav a.active {
      background-color: rgba(255, 255, 255, 0.05);
      color: #facc15;
      font-weight: bold;
    }

    nav a:hover {
      background-color: rgba(255, 255, 255, 0.08);
    }

    main {
      flex: 1;
      padding: 40px;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
    }

    .card {
      background-color: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .card h3 {
      font-size: 14px;
      color: #ccc;
      margin-bottom: 6px;
    }

    .card p {
      font-size: 24px;
      font-weight: bold;
    }

    .green { color: #4ade80; }
    .yellow { color: #facc15; }
    .blue { color: #60a5fa; }
    .pink { color: #f472b6; }
    .gray { color: #a1a1aa; }

    footer {
      margin-top: 60px;
      text-align: center;
      font-size: 12px;
      color: #888;
    }

    .footer-brand {
      color: #facc15;
      font-weight: bold;
    }

    .circle-chart {
      width: 120px;
      height: 120px;
      margin: 0 auto;
    }

    .circle-bg {
      fill: none;
      stroke: #333;
      stroke-width: 10;
    }

    .circle {
      fill: none;
      stroke-width: 10;
      stroke-linecap: round;
      transform: rotate(-90deg);
      transform-origin: center;
      transition: stroke-dashoffset 0.5s ease;
    }

    .label {
      font-size: 14px;
      fill: white;
      text-anchor: middle;
      dominant-baseline: middle;
    }

    .progress-text {
      text-align: center;
      margin-top: 10px;
      color: #ccc;
      font-size: 14px;
    }

    .progress-container {
      display: flex;
      justify-content: space-around;
      align-items: center;
      flex-wrap: wrap;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <aside>
    <div class="logo">Trustgate</div>
    <nav>
      <a href="/dashboard" class="active">🏠 Painel de Controle</a>
      <a href="/extrato">💵 Extrato</a>
      <a href="/saques">✋ Saques</a>
      <a href="#">⚙️ Configurações</a>
    </nav>
  </aside>

  <main>
    <div class="cards">
      <div class="card">
        <h3>💰 Saldo Disponível</h3>
        <p class="green">R$ {{ number_format($disponivel, 2, ',', '.') }}</p>
      </div>

      <div class="card">
        <h3>🔒 Valor Bloqueado</h3>
        <p class="yellow">R$ {{ number_format($bloqueado, 2, ',', '.') }}</p>
      </div>

      <div class="card">
        <h3>📥 Cash IN Hoje</h3>
        <p class="blue">R$ {{ number_format($cashIn, 2, ',', '.') }}</p>
      </div>

      <div class="card">
        <h3>📤 Cash OUT Hoje</h3>
        <p class="pink">R$ {{ number_format($cashOut, 2, ',', '.') }}</p>
      </div>

      <div class="card">
        <h3>💸 Total de Taxas</h3>
        <p class="gray">R$ {{ number_format($totalTaxas, 2, ',', '.') }}</p>
      </div>
    </div>

    @php
      $cashInMax = 30000;
      $cashOutMax = 30000;
      $percentIn = min(100, round(($cashIn / $cashInMax) * 100));
      $percentOut = min(100, round(($cashOut / $cashOutMax) * 100));
    @endphp

    <div class="card" style="margin-top: 30px;">
      <h3 style="font-size: 14px; color: #ccc; margin-bottom: 20px;">📊 Progresso Diário</h3>
      <div class="progress-container">
        <!-- Cash IN -->
        <div>
          <svg class="circle-chart">
            <circle class="circle-bg" cx="60" cy="60" r="45" />
            <circle
              class="circle"
              cx="60" cy="60" r="45"
              stroke="#60a5fa"
              stroke-dasharray="282.6"
              stroke-dashoffset="{{ 282.6 - (282.6 * $percentIn / 100) }}"
            />
            <text x="60" y="60" class="label">{{ $percentIn }}%</text>
          </svg>
          <div class="progress-text">Cash IN <br><strong>R$ {{ number_format($cashIn, 2, ',', '.') }}</strong></div>
        </div>

        <!-- Cash OUT -->
        <div>
          <svg class="circle-chart">
            <circle class="circle-bg" cx="60" cy="60" r="45" />
            <circle
              class="circle"
              cx="60" cy="60" r="45"
              stroke="#f472b6"
              stroke-dasharray="282.6"
              stroke-dashoffset="{{ 282.6 - (282.6 * $percentOut / 100) }}"
            />
            <text x="60" y="60" class="label">{{ $percentOut }}%</text>
          </svg>
          <div class="progress-text">Cash OUT <br><strong>R$ {{ number_format($cashOut, 2, ',', '.') }}</strong></div>
        </div>
      </div>
    </div>

    <footer>
      © 2025 <span class="footer-brand">Trustgate</span>. Todos os direitos reservados.
    </footer>
  </main>

</body>
</html>
