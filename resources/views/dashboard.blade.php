<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Trustgate</title>
  <style>
    /* === Color Palette from Pluggou === */
    :root {
      --bg-page: #0d141f;
      --bg-sidebar: #0b121a;
      --bg-card: rgba(15, 23, 32, 0.8);
      --border-card: rgba(255, 255, 255, 0.1);
      --text-primary: #e8edf3;
      --text-secondary: #9a9fad;
      --accent-green: #00d084;
      --accent-red: #ff4b4b;
      --accent-blue: #5393ff;
      --accent-yellow: #facc15;
      --accent-pink: #ff6b82;
      --shadow-lg: 0 8px 20px rgba(0, 0, 0, 0.5);
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { display: flex; min-height: 100vh; font-family: 'Inter', sans-serif; background: var(--bg-page); color: var(--text-primary); }
    /* Sidebar */
    aside { width: 280px; background: var(--bg-sidebar); padding: 40px 24px; display: flex; flex-direction: column; }
    .logo { font-size: 24px; font-weight: 700; color: var(--accent-green); margin-bottom: 48px; }
    nav a { display: flex; align-items: center; gap: 12px; padding: 14px 16px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; font-size: 16px; transition: background 0.2s, color 0.2s; }
    nav a.active { background: rgba(0, 208, 132, 0.2); color: var(--accent-green); font-weight: 600; }
    nav a:hover { background: rgba(255, 255, 255, 0.08); color: var(--text-primary); }
    /* Main container */
    main { flex: 1; padding: 40px; overflow-y: auto; }
    header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .title { font-size: 32px; font-weight: 700; }
    .subtitle { color: var(--text-secondary); margin-top: 4px; font-size: 14px; }
    .user-menu { font-size: 14px; color: var(--text-secondary); cursor: pointer; }
    /* Metric Cards */
    .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 24px; }
    .card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-lg); display: flex; flex-direction: column; }
    .card-icon { font-size: 28px; margin-bottom: 12px; }
    .card-value { font-size: 24px; font-weight: 700; margin-bottom: 6px; }
    .card-label { font-size: 14px; color: var(--text-secondary); }
    /* Chart Section */
    .section { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px; margin-top: 40px; }
    .chart-card { background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-lg); }
    .chart-title { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
    .chart-placeholder { height: 220px; background: rgba(255,255,255,0.02); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-size: 14px; }
    /* Transactions Table */
    .table-card { margin-top: 40px; background: var(--bg-card); border: 1px solid var(--border-card); border-radius: 12px; padding: 24px; box-shadow: var(--shadow-lg); }
    .table-card h3 { font-size: 16px; font-weight: 600; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 8px; text-align: left; font-size: 14px; }
    th { color: var(--text-secondary); font-weight: 500; }
    tr { border-bottom: 1px solid rgba(255,255,255,0.08); }
    .status { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .status.pendente { background: rgba(250,204,21,0.2); color: var(--accent-yellow); }
    .status.aprovada { background: rgba(0,208,132,0.2); color: var(--accent-green); }
    .status.recusada { background: rgba(255,75,75,0.2); color: var(--accent-red); }
    .status.estorno { background: rgba(255,107,130,0.2); color: var(--accent-pink); }
    /* Footer */
    footer { margin-top: 60px; text-align: center; font-size: 12px; color: var(--text-secondary); }
    .footer-brand { color: var(--accent-green); font-weight: 700; }
  </style>
</head>
<body>
  <aside>
    <div class="logo">Trustgate</div>
    <nav>
      <a href="#" class="active">🏠 Dashboard</a>
      <a href="#">💰 Transações</a>
      <a href="#">🔄 Transferências</a>
      <a href="#">↩️ Estornos</a>
      <a href="#">⚙️ Configurações</a>
    </nav>
  </aside>
  <main>
    <header>
      <div>
        <div class="title">Dashboard</div>
        <div class="subtitle">Veja as estatísticas mais recentes do seu negócio.</div>
      </div>
      <div class="user-menu">euller lopes ▾</div>
    </header>
    <div class="cards">
      <div class="card">
        <div class="card-icon">💰</div>
        <div class="card-value" style="color: var(--accent-green);">R$ 3.842,12</div>
        <div class="card-label">Saldo Disponível</div>
      </div>
      <div class="card">
        <div class="card-icon">🔒</div>
        <div class="card-value" style="color: var(--accent-red);">R$ 0,00</div>
        <div class="card-label">Bloqueios Cautelares</div>
      </div>
      <div class="card">
        <div class="card-icon">📄</div>
        <div class="card-value" style="color: var(--accent-blue);">141</div>
        <div class="card-label">Número de Transações</div>
      </div>
      <div class="card">
        <div class="card-icon">🛡️</div>
        <div class="card-value" style="color: var(--accent-yellow);">R$ 1.031,60</div>
        <div class="card-label">Reserva de Segurança</div>
      </div>
      <div class="card">
        <div class="card-icon">💸</div>
        <div class="card-value" style="color: var(--accent-pink);">R$ 111,39</div>
        <div class="card-label">Estornos</div>
      </div>
    </div>
    <div class="section">
      <div class="chart-card">
        <div class="chart-title">Transações PIX</div>
        <div class="chart-placeholder">[Gráfico de Linha]</div>
      </div>
      <div class="chart-card">
        <div class="chart-title">Análise de PIX por Status</div>
        <div class="chart-placeholder">[Gráfico de Pizza]</div>
      </div>
    </div>
    <div class="table-card">
      <h3>Últimas Transações</h3>
      <table>
        <thead>
          <tr><th>Transação</th><th>Cliente</th><th>Descrição</th><th>Data</th><th>Valor</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>tx_1751584045074_700</td>
            <td>PlugouTrust</td>
            <td>Depósito via Pix</td>
            <td>03/07/2025</td>
            <td>R$ 113,14</td>
            <td><span class="status pendente">PENDENTE</span></td>
          </tr>
          <tr>
            <td>tx_1751583890196_103</td>
            <td>PlugouTrust</td>
            <td>Depósito via Pix</td>
            <td>03/07/2025</td>
            <td>R$ 163,09</td>
            <td><span class="status pendente">PENDENTE</span></td>
          </tr>
        </tbody>
      </table>
    </div>
    <footer>
      © 2025 <span class="footer-brand">Trustgate</span>. Todos os direitos reservados.
    </footer>
  </main>
</body>
</html>
