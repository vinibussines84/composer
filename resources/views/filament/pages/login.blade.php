<x-filament::layouts.base :title="__('filament-panels::pages/auth/login.title')">
    <div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-gray-800 text-white rounded-2xl shadow-xl p-8 space-y-6">
            <div class="text-center">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="h-12 mx-auto mb-2">
                <h2 class="text-2xl font-bold">Bem-vindo</h2>
                <p class="text-sm text-gray-400">Entre para continuar</p>
            </div>

            <x-filament-panels::auth.login-form />

            <div class="text-center text-sm text-gray-500">
                © {{ date('Y') }} SeuSistema. Todos os direitos reservados.
            </div>
        </div>
    </div>
</x-filament::layouts.base>
