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

    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
      -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
      box-shadow: 0 0 0 1000px transparent inset !important;
      -webkit-text-fill-color: white !important;
      caret-color: white !important;
      transition: background-color 9999s ease-in-out 0s !important;
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

      <div>
        <label class="block text-sm font-medium mb-1">Senha</label>
        <div class="relative">
          <input type="password" name="password"
            class="w-full px-4 py-3 pl-11 rounded-lg bg-transparent border border-white/30 text-white focus:outline-none focus:border-yellow-400 transition-all duration-200"
            autocomplete="current-password" required>
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24">
            <path d="M12 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
            <path d="M12 3C7 3 2 7 2 12s5 9 10 9 10-4 10-9-5-9-10-9z"/>
          </svg>
        </div>
        <p class="text-xs text-gray-400 mt-1">Mínimo de 6 caracteres</p>
      </div>

      <button type="submit"
        class="w-full py-3 bg-yellow-400 text-black font-semibold rounded-lg hover:bg-yellow-300 hover:shadow-yellow-500/30 hover:shadow-lg transition-all text-sm tracking-wide">
        Entrar
      </button>
    </form>
  </div>

  <!-- Rodapé -->
  <footer class="mt-6 sm:mt-10 text-xs text-white/40 text-center w-full z-10 px-2 sm:px-0">
    © 2025 Trustgate. Todos os direitos reservados. |
    <a href="#" class="underline hover:text-white/70">Política de privacidade</a> |
    <a href="#" class="underline hover:text-white/70">Termos e condições</a>
  </footer>

  <script>
    // Fade-in animation
    document.querySelectorAll('.animate-fade-in').forEach(el => {
      el.style.opacity = 0;
      setTimeout(() => {
        el.style.transition = 'opacity 1s ease-out';
        el.style.opacity = 1;
      }, 100);
    });
  </script>
</body>
</html>
