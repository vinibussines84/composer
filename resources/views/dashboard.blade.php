@extends('layouts.app')

@section('content')
<div class="px-6 py-8 space-y-6">

    {{-- Cards Estatísticos --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        <x-dashboard.card 
            title="Saldo Disponível" 
            value="R$ {{ number_format($disponivel, 2, ',', '.') }}"
            color="green"
            icon="lucide-dollar-sign"
        />
        <x-dashboard.card 
            title="Bloqueado" 
            value="R$ {{ number_format($bloqueado, 2, ',', '.') }}"
            color="yellow"
            icon="lucide-shield-alert"
        />
        <x-dashboard.card 
            title="Taxas Arrecadadas" 
            value="R$ {{ number_format($totalTaxas, 2, ',', '.') }}"
            color="red"
            icon="lucide-percent"
        />
    </div>

    {{-- Gráfico e detalhes de movimentação --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Gráfico --}}
        <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-xl md:col-span-2 min-h-[300px]">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Entradas e Saídas de Hoje</h2>
                <input type="date" class="rounded-md border px-3 py-1 text-sm" value="{{ now()->toDateString() }}" />
            </div>

            {{-- Aqui você pode incluir um gráfico real com Chart.js --}}
            <div class="h-64 w-full flex items-center justify-center text-muted">
                <p class="text-sm text-gray-500 dark:text-gray-400">Gráfico em breve...</p>
            </div>
        </div>

        {{-- Card Cash In / Cash Out --}}
        <div class="space-y-4">
            <div class="bg-green-100 p-4 rounded-xl">
                <h3 class="text-lg font-semibold text-green-700 mb-1">Entradas (PIX)</h3>
                <p class="text-2xl font-bold text-green-800">R$ {{ number_format($cashIn, 2, ',', '.') }}</p>
                <p class="text-sm text-green-700">{{ $cashInCount }} transações hoje</p>
            </div>

            <div class="bg-red-100 p-4 rounded-xl">
                <h3 class="text-lg font-semibold text-red-700 mb-1">Saídas (Saque)</h3>
                <p class="text-2xl font-bold text-red-800">R$ {{ number_format($cashOut, 2, ',', '.') }}</p>
                <p class="text-sm text-red-700">{{ $cashOutCount }} saques hoje</p>
            </div>
        </div>
    </div>
</div>
@endsection
