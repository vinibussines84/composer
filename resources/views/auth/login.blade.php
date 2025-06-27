<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Trustgate</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: radial-gradient(circle at -20% 30%, rgba(255, 213, 0, 0.15), transparent 60%) #000000;
    }

    input:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
      box-shadow: 0 0 0 1000px transparent inset !important;
      -webkit-text-fill-color: white !important;
      caret-color: white !important;
      transition: background-color 9999s ease-in-out 0s !important;
    }

    .loading-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 50;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }

    .loading-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }

    .spinner {
      border: 4px solid rgba(255, 255, 255, 0.2);
      border-top: 4px solid #facc15;
      border-radius: 50%;
      width: 48px;
      height: 48px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-4 text-white font-sans relative">

  <!-- Logo -->
  <div class="mb-6 sm:mb-10 animate-fade-in">
    <img src="{{ asset('theme/img/trustgate2.png') }}" alt="Trustgate" class="h-12 sm:h-16 mx-auto">
  </div>

  <!-- Card -->
  <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-xl p-8 sm:p-12 w-full max-w-lg z-10 animate-fade-in transition-all duration-500">
    <h2 class="text-3xl sm:text-4xl font-bold mb-2">Olá! Faça seu login</h2>
    <p class="text-sm text-gray-400 mb-6 sm:mb-8">Informe suas credenciais para acessar sua conta.</p>

    @if ($errors->any())
      <div class="mb-4 text-red-500 text-sm">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
      @csrf

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium mb-1">E-mail</label>
        <div class="relative">
          <input type="email" name="email"
            class="w-full px-4 py-3 pl-11 rounded-lg bg-transparent border border-white/30 text-white focus:outline-none focus:border-yellow-400 transition-all duration-200"
            autocomplete="email" required>
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24">
            <path d="M4 4h16v16H4z" stroke="none"/>
            <path d="M4 4l8 8 8-8" />
          </svg>
        </div>
      </div>

      <!-- Senha -->
      <div>
        <label class="block text-sm font-medium mb-1">Senha</label>
        <div class="relative">
          <input type="password" name="password" id="password-input"
            class="w-full px-4 py-3 pl-11 pr-20 rounded-lg bg-transparent border border-white/30 text-white focus:outline-none focus:border-yellow-400 transition-all duration-200"
            autocomplete="current-password" required placeholder="••••••">

          <!-- Botão mostrar/ocultar -->
          <button type="button" id="toggle-password"
            class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-yellow-400 text-xs hover:underline focus:outline-none">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span id="toggle-text">Mostrar</span>
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-1">Mínimo de 6 caracteres</p>
      </div>

      <!-- Botão Entrar -->
      <button
        type="button"
        onclick="submitWithLoading()"
        class="w-full py-3 bg-yellow-400 text-black font-semibold rounded-lg hover:bg-yellow-300 hover:shadow-yellow-500/30 hover:shadow-lg transition-all text-sm tracking-wide">
        Entrar
      </button>
    </form>
  </div>

  <!-- Rodapé -->
  <footer class="mt-6 sm:mt-10 text-xs text-white/40 text-center w-full z-10 px-2 sm:px-0">
    © 2025 <span class="text-yellow-400 font-semibold">Trustgate</span>. Todos os direitos reservados. |
    <a href="#" class="underline hover:text-white/70">Política de privacidade</a> |
    <a href="#" class="underline hover:text-white/70">Termos e condições</a>
  </footer>

  <!-- Loading Overlay -->
  <div id="loading" class="loading-overlay">
    <div class="spinner"></div>
  </div>

  <!-- Scripts -->
  <script>
    // Fade-in animation
    document.querySelectorAll('.animate-fade-in').forEach(el => {
      el.style.opacity = 0;
      setTimeout(() => {
        el.style.transition = 'opacity 1s ease-out';
        el.style.opacity = 1;
      }, 100);
    });

    // Mostrar/Ocultar senha
    const passwordInput = document.getElementById('password-input');
    const toggleButton = document.getElementById('toggle-password');
    const eyeIcon = document.getElementById('eye-icon');
    const toggleText = document.getElementById('toggle-text');

    toggleButton.addEventListener('click', () => {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleText.textContent = isHidden ? 'Ocultar' : 'Mostrar';
      eyeIcon.innerHTML = isHidden
        ? `<path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.965 9.965 0 012.066-3.368M9.88 9.88a3 3 0 004.24 4.24m1.13-6.75a9.974 9.974 0 014.392 5.01 9.957 9.957 0 01-1.427 2.568M3 3l18 18" />`
        : `<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    });

    // Enviar com loading
    function submitWithLoading() {
      const overlay = document.getElementById('loading');
      overlay.classList.add('active');
      setTimeout(() => {
        document.querySelector('form').submit();
      }, 2000);
    }
  </script>
</body>
</html>
