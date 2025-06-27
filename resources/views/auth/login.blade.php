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
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-4 text-white font-sans relative">

    <!-- Logo centralizada -->
    <div class="mb-6 sm:mb-8">
        <img src="{{ asset('theme/img/trustgate2.png') }}" alt="Trustgate" class="h-12 sm:h-16 mx-auto">
    </div>

    <!-- Login Card com efeito vidro -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-2xl shadow-md p-6 sm:p-10 w-full max-w-sm sm:max-w-md z-10">
        <h2 class="text-2xl sm:text-3xl font-bold mb-2">Olá! Faça seu login</h2>
        <p class="text-sm text-gray-400 mb-6 sm:mb-8">Por favor, informe suas credenciais para entrar.</p>

        @if ($errors->any())
            <div class="mb-4 text-red-500 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label class="block text-sm font-medium mb-1">E-mail</label>
            <input type="email" name="email"
                class="w-full px-4 py-3 rounded-lg bg-[#1e2a3f] text-white mb-4 sm:mb-5 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                required>

            <label class="block text-sm font-medium mb-1">Senha</label>
            <input type="password" name="password"
                class="w-full px-4 py-3 rounded-lg bg-[#1e2a3f] text-white mb-6 sm:mb-8 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                required>

            <button type="submit"
                class="w-full py-3 bg-yellow-400 text-black font-semibold rounded-lg hover:bg-yellow-300 transition-all text-sm tracking-wide">
                Entrar
            </button>
        </form>
    </div>

    <!-- Rodapé -->
    <footer class="mt-6 sm:mt-10 text-xs text-white/40 text-center w-full z-10 px-2 sm:px-0">
        © 2025 Trustgate. Todos os direitos reservados. |
        <a href="#" class="underline">Política de privacidade</a> |
        <a href="#" class="underline">Termos e condições</a>
    </footer>
</body>
</html>
